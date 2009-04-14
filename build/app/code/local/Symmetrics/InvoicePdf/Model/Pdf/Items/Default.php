<?php
/**
 * Symmetrics_InvoicePdf_Model_Pdf_Items_Default
 *
 * @category Symmetrics
 * @package Symmetrics_InvoicePdf
 * @author symmetrics gmbh <info@symmetrics.de>, Eugen Gitin <eg@symmetrics.de>
 * @copyright symmetrics gmbh
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Symmetrics_InvoicePdf_Model_Pdf_Items_Default extends Mage_Sales_Model_Order_Pdf_Items_Invoice_Default
{	
    public function draw($position = 1)
    {
        $order = $this->getOrder();
        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();
        $shift = array(0, 10, 0);

        $this->_setFontRegular();

        /* position number */
		$font = $this->_setFontBold();
        $page->drawText($position, $pdf->margin['left'] + 20 - $pdf->widthForStringUsingFontSize($position, $font, 9), $pdf->y, $pdf->encoding);

        /* article name, split after 35 chars */
		foreach(Mage::helper('core/string')->str_split($item->getName(), 35, true, true) as $key => $part) {
			$page->drawText($part, $pdf->margin['left'] + 110, $pdf->y - $shift[0], $pdf->encoding);
			$shift[0] += 10;
        }

        $options = $this->getItemOptions();

        if (isset($options)) {
            foreach ($options as $option) {
                $this->_setFontItalic();
                foreach(Mage::helper('core/string')->str_split(strip_tags($option['label']), 40, false, true) as $_option) {
                    $page->drawText($_option, 35, $pdf->y - $shift[0], $pdf->encoding);
                    $shift[0] += 10;
                }

                $this->_setFontRegular();
                if ($option['value']) {
                    $values = explode(', ', strip_tags($option['value']));
                    foreach ($values as $value) {
                        foreach (Mage::helper('core/string')->str_split($value, 60, true, true) as $_value) {
                            $page->drawText($_value, 40, $pdf->y - $shift[0], $pdf->encoding);
                            $shift[0] += 10;
                        }
                    }
                }
            }
        }

        foreach ($this->_parseDescription() as $description) {
            $page->drawText(strip_tags($description), 65, $pdf->y - $shift[1], $pdf->encoding);
            $shift[1] += 10;
        }

        /* sku */       
        foreach (Mage::helper('core/string')->str_split($this->getSku($item), 10) as $key => $part) {
			if ($key > 0) {
				$shift[2] += 10;
            }
			$page->drawText($part, $pdf->margin['left'] + 45, $pdf->y-$shift[2], $pdf->encoding);
        }

        /* single price */
        $price = $order->formatPriceTxt($item->getPrice());
        $page->drawText($price, $pdf->margin['right'] - 160 - $pdf->widthForStringUsingFontSize($price, $font, 9), $pdf->y, $pdf->encoding);
        
        /* quantity */
        $quantity = $item->getQty() * 1;
        $page->drawText($quantity, $pdf->margin['right'] - 110 - $pdf->widthForStringUsingFontSize($quantity, $font, 9), $pdf->y, $pdf->encoding);

        /* row total */
        $row_total = $order->formatPriceTxt($item->getRowTotal());
        $page->drawText($row_total, $pdf->margin['right'] - 10 - $pdf->widthForStringUsingFontSize($row_total, $font, 9), $pdf->y, $pdf->encoding);
        
        /* tax amount */
        $tax = $order->formatPriceTxt($item->getTaxAmount());
        $page->drawText($tax, $pdf->margin['right'] - 65 - $pdf->widthForStringUsingFontSize($tax, $font, 9), $pdf->y, 'UTF-8');
        
        $pdf->y -= max($shift) + 10;
    }
    
    protected function _setFontRegular($size = 10)
    {
    	$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $this->setFont($font, $size);
        return $font;
    }

    protected function _setFontBold($size = 10)
    {
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $this->setFont($font, $size);
        return $font;
    }

    protected function _setFontItalic($size = 10)
    {
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
        $this->setFont($font, $size);
        return $font;
    }
}
