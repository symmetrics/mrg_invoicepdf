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
 * Subclass for drawing bundle products in invoice pdf
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
class Symmetrics_InvoicePdf_Model_Pdf_Items_Bundle_Invoice
    extends Mage_Bundle_Model_Sales_Order_Pdf_Items_Invoice
{
    /**
     * Draw an bundle product
     * 
     * @param int $position product position in table
     * 
     * @return Zend_Pdf_Page
     */
    public function draw($position = 1)
    {
        $order = $this->getOrder();
        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();

        $this->_setFontRegular();
        $items = $this->getChilds($item);
        
        $fontSize = 9;
        
        $_prevOptionId = '';
        $drawItems = array();

        foreach ($items as $_item) {
            $line = array();
            $attributes = $this->getSelectionAttributes($_item);
            if (is_array($attributes)) {
                $optionId = $attributes['option_id'];
            } else {
                $optionId = 0;
            }

            if (!isset($drawItems[$optionId])) {
                $drawItems[$optionId] = array(
                    'lines'  => array(),
                    'height' => 15
                );
            }
            
            if ($_item->getOrderItem()->getParentItem()) {
                if ($_prevOptionId != $attributes['option_id']) {
                    $optionLabel = Mage::helper('core/string')->str_split(
                        $attributes['option_label'],
                        70,
                        true,
                        true
                    );
                    $line[] = array(
                        'font' => 'bold',
                        'text' => $optionLabel,
                        'feed' => $pdf->margin['left'] + 110,
                        'font_size' => $fontSize
                    );

                    $drawItems[$optionId] = array(
                        'lines' => array($line),
                        'height' => 15
                    );

                    $line = array();
                    $_prevOptionId = $attributes['option_id'];
                }
            }

            // in case Product name is longer than 80 chars,
            // it is written in a few lines
            if ($_item->getOrderItem()->getParentItem()) {
                $feed = $pdf->margin['left'] + 130;
                $name = $this->getValueHtml($_item);
            } else {
                $feed = $pdf->margin['left'] + 130;
                $name = $_item->getName();
            }
            $line[] = array(
                'text'  => Mage::helper('core/string')->str_split(
                    $name,
                    35,
                    true,
                    true
                ),
                'feed'  => $pdf->margin['left'] + 110,
                'font_size' => $fontSize
            );

            if (!$_item->getOrderItem()->getParentItem()) {
                // draw SKUs
                $splitSku = Mage::helper('core/string')->str_split(
                    $item->getSku(),
                    10
                );
                $line[] = array(
                    'text'  => $splitSku,
                    'feed'  => $pdf->margin['left'] + 45,
                    'font_size' => $fontSize
                );
                
                // draw Position Number
                $line[]= array(
                    'text'  => $position,
                    'feed'  => $pdf->margin['left'] + 20,
                    'align' => 'right',
                    'font_size' => $fontSize
                );
            }

            // draw prices
            if ($this->canShowPriceInfo($_item)) {
                $price = $order->formatPriceTxt($_item->getPrice());
                $line[] = array(
                    'text'  => $price,
                    'feed'  => $pdf->margin['right'] - 160,
                    'align' => 'right',
                    'font_size' => $fontSize
                );
                $line[] = array(
                    'text'  => $_item->getQty()*1,
                    'feed'  => $pdf->margin['right'] - 110,
                    'align' => 'right',
                    'font_size' => $fontSize
                );

                $tax = $order->formatPriceTxt($_item->getTaxAmount());
                $line[] = array(
                    'text'  => $tax,
                    'feed'  => $pdf->margin['right'] - 65,
                    'align' => 'right',
                    'font_size' => $fontSize
                );

                $rowTotal = $order->formatPriceTxt($_item->getRowTotal());
                $line[] = array(
                    'text'  => $rowTotal,
                    'feed'  => $pdf->margin['right'] - 10,
                    'align' => 'right',
                    'font_size' => $fontSize
                );
            }
            $drawItems[$optionId]['lines'][] = $line;
        }

        // custom options
        $options = $item->getOrderItem()->getProductOptions();
        if ($options) {
            if (isset($options['options'])) {
                foreach ($options['options'] as $option) {
                    $lines = array();
                    $optionLabel = Mage::helper('core/string')->str_split(
                        strip_tags($option['label']),
                        70,
                        true,
                        true
                    );
                    $lines[][] = array(
                        'text'  => $optionLabel,
                        'font'  => 'bold',
                        'feed'  => $pdf->margin['left'] + 110,
                        'font_size' => $fontSize
                    );

                    if ($option['value']) {
                        $text = array();
                        if (isset($option['print_value'])) {
                            $_printValue = $option['print_value'];
                        } else {
                            $_printValue = strip_tags($option['value']);
                        }
                        
                        $values = explode(', ', $_printValue);
                        foreach ($values as $value) {
                            $splitValue = Mage::helper('core/string')
                                ->str_split(
                                    $value,
                                    50,
                                    true,
                                    true
                                );
                            foreach ($splitValue as $_value) {
                                $text[] = $_value;
                            }
                        }

                        $lines[][] = array(
                            'text'  => $text,
                            'feed'  => $pdf->margin['left'] + 110,
                            'font_size' => $fontSize
                        );
                    }
                    $drawItems[] = array(
                        'lines'  => $lines,
                        'height' => 15
                    );
                }
            }
        }
        $page = $pdf->drawLineBlocks(
            $page,
            $drawItems,
            array(
                'table_header' => false
            )
        );
        $this->setPage($page);
    }
}