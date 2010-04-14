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
 * @author    symmetrics gmbh <info@symmetrics.de>
 * @author    Torsten Walluhn <tw@symmetrics.de>
 * @copyright 2010 symmetrics gmbh
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.symmetrics.de/
 */

/**
 * Abstract Pdf Rendering class
 *
 * @category  Symmetrics
 * @package   Symmetrics_InvoicePdf
 * @author    symmetrics gmbh <info@symmetrics.de>
 * @author    Torsten Walluhn <tw@symmetrics.de>
 * @copyright 2010 symmetrics gmbh
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.symmetrics.de/
 */
 
class Symmetrics_InvoicePdf_Model_Pdf_Invoice extends Symmetrics_InvoicePdf_Model_Pdf_Abstract 
{
    protected $_invoice;
    
    public function getPdf($invoices = array())
    {
        $pdf = $this->_pdf;
        
        foreach ($invoices as $invoice) {
            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->emulate($invoice->getStoreId());
            }
            
            $this->_invoice = $invoice;
            
            $settings = new Varien_Object();
            $order = $invoice->getOrder();
            
            $settings->setStore($invoice->getStore());
            
            $page = $this->newPage($settings);
            
            /* Add image */
            $this->insertLogo($page, $invoice->getStore());

            /* Add address */
//            $this->insertAddressFooter($page, $invoice->getStore());

            /* Add head */
            $this->insertOrder(
                $page, 
                $order, 
                Mage::helper('invoicepdf')->getSalesPdfInvoiceConfigFlag(
                    self::PDF_INVOICE_PUT_ORDER_ID, 
                    $order->getStoreId()
                )
            );

            $this->setSubject($page, Mage::helper('sales')->__('Invoice'));

            $page = $this->newPage($settings);
            $page = $this->newPage($settings);
            $page = $this->newPage($settings);
            $page = $this->newPage($settings);
            
        }
        
        
//        var_dump($pdfPage->getWidth());
//        var_dump($pdfPage->getHeight());
        return $this->_pdf;
    }
    
    protected function _insertOrderInfo(&$page, $order, $putOrderId)
    {
        parent::_insertOrderInfo(&$page, $order, $putOrderId);
        $this->_insertOrderInfoRow(
            $page,
            Mage::helper('sales')->__('Invoice # '),
            $this->_invoice->getIncrementId()
        );
    }
}