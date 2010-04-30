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
 * @copyright 2010 Symmetrics Gmbh
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.symmetrics.de/
 */

/**
 * Overridden sales order invoice view block from backend
 *
 * @category  Symmetrics
 * @package   Symmetrics_InvoicePdf
 * @author    Symmetrics GmbH <info@symmetrics.de>
 * @author    Torsten Walluhn <tw@symmetrics.de>
 * @copyright 2010 symmetrics gmbh
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.symmetrics.de/
 */
class Symmetrics_InvoicePdf_Block_Sales_Invoice_Grid
    extends Mage_Adminhtml_Block_Sales_Invoice_Grid
{
    /**
     * overridden prepareMassaction to remove Magento Print PDF Invoice with
     * InvoicePdf Massaction
     * 
     * @return Symmetrics_InvoicePdf_Block_Sales_Invoice_Grid
     */
    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();
        $this->getMassactionBlock()->removeItem('pdfinvoices_order');

        $this->getMassactionBlock()->addItem(
            'symmetrics_pdfinvoices_order',
            array(
                'label'=> Mage::helper('sales')->__('PDF Invoices'),
                'url'  => $this->getUrl('symmetrics/invoicePdf/pdfinvoices'),
            )
        );

        return $this;
    }
}