<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category  Symmetrics
 * @package   Symmetrics_InvoicePdf
 * @author    Symmetrics GmbH <info@symmetrics.de>
 * @author    Eugen Gitin <eg@symmetrics.de>
 * @author    Eric Reiche <er@symmetrics.de>
 * @copyright 2009 Symmetrics GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.symmetrics.de/
 */
/**
 * Observer model
 * 
 * @category  Symmetrics
 * @package   Symmetrics_InvoicePdf
 * @author    Symmetrics GmbH <info@symmetrics.de>
 * @author    Eugen Gitin <eg@symmetrics.de>
 * @author    Eric Reiche <er@symmetrics.de>
 * @copyright 2009 Symmetrics GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.symmetrics.de/
 */
class Symmetrics_InvoicePdf_Model_Observer extends Varien_Object
{
    /**
     * Create invoice after order was completed
     * 
     * @param Varien_Observer $observer observer object
     * 
     * @return Symmetrics_InvoicePdf_Model_Observer
     */
    public function createInvoice($observer)
    {
        if (Mage::getStoreConfig('sales_pdf/invoice/autoinvoice')) {
            $order = $observer->getOrder();
            $email = (bool) Mage::getStoreConfig('sales_pdf/invoice/autoinvoicemail');
            if (!$order->getId()) {
                 Mage::log('order does not exists');
            }
            if (!$order->canInvoice()) {
                 Mage::log(Mage::helper('sales')->__('Can not create invoice for order.'));
            }
            $invoice = $order->prepareInvoice();
            $invoice->register();
            if ($email) {
                $invoice->setEmailSent(true);
            }
            $invoice->getOrder()->setIsInProcess(true);
            try {
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder())
                    ->save();
                if ($email) {
                    $invoice->sendEmail();
                }
            } catch (Mage_Core_Exception $e) {
                Mage::log($e->getMessage());
            }
        }

        return $this;
    }

    /**
     * Change Magento Print button in invoice admin view with Symmetrics_InvoicePdf Print Button
     *
     * @param Varien_Observer $observer observer object
     *
     * @return Symmetrics_InvoicePdf_Model_Observer
     */
    public function adminhtmlInvoiceView($observer)
    {
        $currentInvoice = Mage::registry('current_invoice');

        $layout = Mage::getSingleton('core/layout');
        /* @var $layout Mage_Core_Model_Layout */

        $route = "symmetrics/invoicepdf/print";
        $params = array('invoice_id' => $currentInvoice->getId());

        // sales_invoice_view
        $invoiceBlock = $layout->getBlock('sales_invoice_view');
        /* @var $invoiceBlock Mage_Adminhtml_Block_Sales_Order_Invoice_View */

        $invoiceBlock->removeButton('print');

        $invoiceBlock->addButton(
            'invoicepdf',
            array(
                'label'     => Mage::helper('sales')->__('Print'),
                'class'     => 'save',
                'onclick'   => 'setLocation(\'' . Mage::getUrl($route, $params) . '\')'
            )
        );

        return $this;
    }
}
