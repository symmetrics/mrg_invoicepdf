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
 *
 *
 * @category  Symmetrics
 * @package   Symmetrics_InvoicePdf
 * @author    Symmetrics GmbH <info@symmetrics.de>
 * @author    Torsten Walluhn <tw@symmetrics.de>
 * @copyright 2010 symmetrics gmbh
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.symmetrics.de/
 */
class Symmetrics_InvoicePdf_Model_Pdf_Items_Bundle_Invoice
    extends Symmetrics_InvoicePdf_Model_Pdf_Items_Bundle_Abstract
{
    public function draw()
    {
        $order  = $this->getOrder();
        $item   = $this->getItem();
        $pdf    = $this->getPdf();
        $page   = $this->getPage();
        $items = $this->getChilds($item);


        $_prevOptionId = '';
        $drawItems = array();

        foreach ($items as $_item) {
            $attributes = $this->getSelectionAttributes($_item);
            $tableRowItem = Mage::getModel('invoicepdf/pdf_items_item');
            
            // draw SKUs
            if (!$_item->getOrderItem()->getParentItem()) {
                $sku = $this->getSku($_item);
                $tableRowItem->addColumn("sku", $sku, 45 , 'left', 50);
            }


            /* in case Product name is longer than 80 chars - it is written in a few lines */
            if ($_item->getOrderItem()->getParentItem()) {
                $feed = 30;
                $maxWidth = 335;
                $name = $this->getValueHtml($_item);
            } else {
                $feed = 110;
                $maxWidth = 260;
                $name = $_item->getName();
            }
            $tableRowItem->addColumn("name", $name, $feed, 'left', $maxWidth);

            // draw prices
            if ($this->canShowPriceInfo($_item)) {
                $price = $order->formatPriceTxt($_item->getPrice());
                $tableRowItem->addColumn("price", $price, 160, 'right');

                $qty = $_item->getQty()*1;
                $tableRowItem->addColumn("qty", $qty, 110, 'right');

                $tax= $order->formatPriceTxt($_item->getTaxAmount());
                $tableRowItem->addColumn("tax", $tax, 60, 'right');

                $rowTotal = $order->formatPriceTxt($_item->getRowTotal());
                $tableRowItem->addColumn("rowTotal", $rowTotal, 10, 'right');
            }

            // draw Option Labels
            if ($_item->getOrderItem()->getParentItem()) {
                if ($_prevOptionId != $attributes['option_id']) {
                    $tableRowOptionItem = Mage::getModel('invoicepdf/pdf_items_item');
                    /* @var $tableRowOptionItem Symmetrics_InvoicePdf_Model_Pdf_Items_Item */

                    $labelFont = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                    $tableRowOptionItem->addColumn('option_label', $attributes['option_label'], 20, 'left', 0, $labelFont, 7);

                    $this->addRow($tableRowOptionItem);

                    $_prevOptionId = $attributes['option_id'];
                }
            }
            $this->addRow($tableRowItem);
        }
        
        $this->setTriggerPosNumber(true);

        $page = $pdf->insertTableRow($page, $this);
        $this->setPage($page);
        $this->clearRows();
    }
}