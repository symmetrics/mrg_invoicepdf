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
 * Class for drawing default/simple products in invocie pdf
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
class Symmetrics_InvoicePdf_Model_Pdf_Items_Default
    extends Mage_Sales_Model_Order_Pdf_Items_Invoice_Default
{
    /**
     * Draw default product
     * 
     * @param int $position position on invoice
     * 
     * @return Zend_Pdf_Page
     */
    public function draw($position = 1)
    {
        $order = $this->getOrder();
        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();
        $lines = array();
        
        $fontSize = 9;

        // draw Position Number
        $lines[0]= array();
        $lines[0][] = array(
            'text'  => $position,
            'feed'  => $pdf->margin['left'] + 20,
            'align' => 'right',
            'font_size' => $fontSize
        );
        
        // draw SKU
        $splitSku = Mage::helper('core/string')->str_split(
            $this->getSku($item),
            10
        );
        $lines[0][] = array(
            'text'  => $splitSku,
            'feed'  => $pdf->margin['left'] + 45,
            'font_size' => $fontSize
        );
        
        // draw Product name
        $splitName = Mage::helper('core/string')->str_split(
            $item->getName(),
            40,
            true,
            true
        );
        $lines[0][]= array(
            'text' => $splitName,
            'feed' => $pdf->margin['left'] + 110,
            'font_size' => $fontSize
        );

        // draw QTY
        $lines[0][] = array(
            'text'  => $item->getQty() * 1,
            'feed'  => $pdf->margin['right'] - 110,
            'align' => 'right',
            'font_size' => $fontSize
        );

        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $splitLabel = Mage::helper('core/string')->str_split(
                    strip_tags($option['label']),
                    40,
                    false,
                    true
                );
                $lines[][] = array(
                    'text' => $splitLabel,
                    'font' => 'bold',
                    'feed' => $pdf->margin['left'] + 110
                );

                // draw options value
                if ($option['value']) {
                    if (isset($option['print_value'])) {
                        $_printValue = $option['print_value'];
                    } else {
                        $_printValue = strip_tags($option['value']);
                    }
                    $values = explode(', ', $_printValue);
                    foreach ($values as $value) {
                        $splitValue = Mage::helper('core/string')->str_split(
                            $value,
                            60,
                            true,
                            true
                        );
                        $lines[][] = array(
                            'text' => $splitValue,
                            'feed' => $pdf->margin['left'] + 120
                        );
                    }
                }
            }
        }
        
        if (Mage::getStoreConfig('tax/sales_display/price') == 2 || Mage::getStoreConfig('tax/sales_display/price') == 3) {
            $itemPrice = $item->getPriceInclTax(); 
        }
        else {
            $itemPrice = $item->getPrice();
        }

        // draw Price        
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($itemPrice),
            'feed'  => $pdf->margin['right'] - 160,
            'align' => 'right',
            'font_size' => $fontSize
        );

        // draw Tax
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getTaxAmount()),
            'feed'  => $pdf->margin['right'] - 65,
            'align' => 'right',
            'font_size' => $fontSize
        );
        
        if (Mage::getStoreConfig('tax/sales_display/subtotal') == 2 || Mage::getStoreConfig('tax/sales_display/subtotal') == 3) {
            $itemSubtotal = $item->getRowTotal() + $item->getTaxAmount(); 
        }
        else {
            $itemSubtotal = $item->getRowTotal();
        }

        // draw Subtotal
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($itemSubtotal),
            'feed'  => $pdf->margin['right'] - 10,
            'align' => 'right',
            'font_size' => $fontSize
        );

        $lineBlock = array(
            'lines'  => $lines,
            'height' => 15
        );

        $page = $pdf->drawLineBlocks(
            $page,
            array($lineBlock),
            array('table_header' => true)
        );
        $this->setPage($page);
    }
}
