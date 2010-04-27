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
 * invoice rendering class for default products
 *
 * @category  Symmetrics
 * @package   Symmetrics_InvoicePdf
 * @author    symmetrics gmbh <info@symmetrics.de>
 * @author    Torsten Walluhn <tw@symmetrics.de>
 * @copyright 2010 symmetrics gmbh
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.symmetrics.de/
 */
class Symmetrics_InvoicePdf_Model_Pdf_Items_Invoice_Default
    extends Symmetrics_InvoicePdf_Model_Pdf_Items_Abstract
{
    /**
     * method to draw the invoice
     *
     * @return void
     */
    public function draw()
    {
        $order  = $this->getOrder();
        $item   = $this->getItem();
        $pdf    = $this->getPdf();
        $page   = $this->getPage();


        $tableRowItem = Mage::getModel('invoicepdf/pdf_items_item');
        /* @var $tableRowItem Symmetrics_InvoicePdf_Model_Pdf_Items_Item */
       
        $sku = $this->getSku($item);
        $tableRowItem->addColumn("sku", $sku, 45, 'left', 50);
        
        $name = $item->getName();
        $tableRowItem->addColumn("name", $name, 110, 'left', 260);

        $price = $order->formatPriceTxt($item->getPrice());
        $tableRowItem->addColumn("price", $price, 160, 'right');

        $qty = $item->getQty()*1;
        $tableRowItem->addColumn("qty", $qty, 110, 'right');

        $tax= $order->formatPriceTxt($item->getTaxAmount());
        $tableRowItem->addColumn("tax", $tax, 60, 'right');

        $rowTotal = $order->formatPriceTxt($item->getRowTotal());
        $tableRowItem->addColumn("rowTotal", $rowTotal, 10, 'right');

        $this->addRow($tableRowItem);

        $options = $this->getItemOptions();

        if ($options) {
            foreach ($options as $option) {
                $tableRowOptionItem = Mage::getModel('invoicepdf/pdf_items_item');
                /* @var $tableRowOptionItem Symmetrics_InvoicePdf_Model_Pdf_Items_Item */
                // draw options label
                $labelFont = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                $tableRowOptionItem->addColumn('option_label', $option['label'], 110, 'left', 0, $labelFont, 7);

                $this->addRow($tableRowOptionItem);

                if ($option['value']) {
                    $tableRowOptionItem = Mage::getModel('invoicepdf/pdf_items_item');
                    $_printValue = isset($option['print_value'])
                        ? $option['print_value'] : strip_tags($option['value']);
                    $values = explode(', ', $_printValue);

                    $valueFont = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                    $tableRowOptionItem->addColumn('option_value', $_printValue, 115, 'left', 0, $valueFont, 6);
                    $this->addRow($tableRowOptionItem);
                }
            }
        }

        $this->setTriggerPosNumber(true);

        $page = $pdf->insertTableRow($page, $this);
        $this->setPage($page);
        $this->clearRows();
    }
}