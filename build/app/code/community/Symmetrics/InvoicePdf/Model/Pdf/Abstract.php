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
 
 abstract class Symmetrics_InvoicePdf_Model_Pdf_Abstract extends Varien_Object
 {
    /**
     * Zend PDF object
     *
     * @var Zend_Pdf
     */
    protected $_pdf;
    
    protected $_height;
    
    protected $_width;
    
    
    const PDF_INVOICE_PUT_ORDER_ID = 'put_order_id';
    const PDF_SHIPMENT_PUT_ORDER_ID = 'sales_pdf/shipment/put_order_id';
    const PDF_CREDITMEMO_PUT_ORDER_ID = 'sales_pdf/creditmemo/put_order_id';

    const PAGE_POSITION_LEFT = 40;
    const PAGE_POSITION_RIGHT = 555;
    const PAGE_POSITION_TOP = 800;
    
    abstract public function getPdf();
    
    /**
     * Cunstructor to initialize the PDF object
     *
     */
    protected function _construct()
    {
        $this->_setPdf(new Zend_Pdf());
        $this->_height = 0;
        $this->_width = 0;
    }
    
    /**
     * Returns the total width in points of the string using the specified font and
     * size.
     *
     * This is not the most efficient way to perform this calculation. I'm
     * concentrating optimization efforts on the upcoming layout manager class.
     * Similar calculations exist inside the layout manager class, but widths are
     * generally calculated only after determining line fragments.
     *
     * @param string $string
     * @param Zend_Pdf_Resource_Font $font
     * @param float $fontSize Font size in points
     * @return float
     */
    public function widthForStringUsingFontSize($string, $font, $fontSize)
    {
        $drawingString = '"libiconv"' == ICONV_IMPL ? iconv('UTF-8', 'UTF-16BE//IGNORE', $string) : @iconv('UTF-8', 'UTF-16BE', $string);

        $characters = array();
        for ($i = 0; $i < strlen($drawingString); $i++) {
            $characters[] = (ord($drawingString[$i++]) << 8) | ord($drawingString[$i]);
        }
        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $font->widthsForGlyphs($glyphs);
        $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
        return $stringWidth;

    }

    /**
     * Returns the total height in points of the font using the specified font and
     * size.
     *
     * @param string $string
     * @param Zend_Pdf_Resource_Font $font
     * @param float $fontSize Font size in points
     * @return float
     */
    public function heightForFontUsingFontSize($font, $fontSize)
    {
        $height = $font->getLineHeight();
        $stringHeight = ($height / $font->getUnitsPerEm()) * $fontSize;
        
        return $stringHeight;
    }

    /**
     * Before getPdf processing
     *
     * @return void
     */
    protected function _beforeGetPdf() {
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);
    }

    /**
     * After getPdf processing
     *
     * @return void
     */
    protected function _afterGetPdf() {
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(true);
    }
    
    /**
     * Set PDF object
     *
     * @param Zend_Pdf $pdf
     *
     * @return Mage_Sales_Model_Order_Pdf_Abstract
     */
    protected function _setPdf(Zend_Pdf $pdf)
    {
        $this->_pdf = $pdf;
        return $this;
    }
    
    /**
     * Retrieve PDF object
     *
     * @throws Mage_Core_Exception
     *
     * @return Zend_Pdf
     */
    protected function _getPdf()
    {
        if (!$this->_pdf instanceof Zend_Pdf) {
            Mage::throwException(Mage::helper('sales')->__('Please define PDF object before using'));
        }

        return $this->_pdf;
    }
    

    protected function _setFontRegular($object, $size = 10)
    {
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $object->setFont($font, $size);
        return $font;
    }

    protected function _setFontBold($object, $size = 10)
    {
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $object->setFont($font, $size);
        return $font;
    }

    protected function _setFontItalic($object, $size = 10)
    {
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
        $object->setFont($font, $size);
        return $font;
    }
    
    /**
     * Create new page and assign to PDF object
     *
     * @param array $settings
     
     * @return Zend_Pdf_Page
     */
    public function newPage(Varien_Object $settings)
    {
        $pageSize = ($settings->hasPageSize()) ? $settings->getPageSize() : Zend_Pdf_Page::SIZE_A4;
        $page = $this->_getPdf()->newPage($pageSize);
        $this->insertAddressFooter($page, $settings->getStore());
        $pdf = $this->_getPdf();

        $this->_height = self::PAGE_POSITION_TOP;
        /* @var $pdf Zend_Pdf */
        $pdf->pages[] = $page;
        if (count($pdf->pages) > 1) {
            $this->insertTableHeader($page);
        }
        
        return $page;
    }
    
    /**
     * Insert the store logo to the Pdf
     *
     * @param &$page Zend_Pdf_Page Page to insert logo
     * @param $store integer       store Id to get logo
     *
     * @return Zend_Pdf_Page
     */
    protected function insertLogo(&$page, $store = null)
    {
        $image = Mage::getStoreConfig('sales/identity/logo', $store);
        if ($image) {
            $image = Mage::getStoreConfig('system/filesystem/media', $store) . '/sales/store/logo/' . $image;
            if (is_file($image)) {
                $image = Zend_Pdf_Image::imageWithPath($image);
                $page->drawImage($image, 25, 800, 125, 825);
            }
        }
        //return $page;
    }

    /**
     * Insert the store address to the Pdf
     *
     * @param &$page Zend_Pdf_Page Page to insert address
     * @param $store integer       store Id to get address
     *
     * @return Zend_Pdf_Page
     */
    protected function insertAddressFooter(&$page, $store = null)
    {
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 5);

        $footerLinePos = 47;
        
        $heightCount = 0;
        $lineSpacing = 6;
        $width = 20;
        
        $page->setLineWidth(0.4);
        $page->drawLine($width, $footerLinePos, $page->getWidth() - $width, $footerLinePos);
        
        $page->setLineWidth(0);
        // TODO: Chech if imprint is installed
        foreach (explode("\n", Mage::getStoreConfig('sales/identity/address', $store)) as $value){
            if ($value!=='') {
                $height = 40 - $lineSpacing * $heightCount;
                $page->drawText(trim(strip_tags($value)), $width, $height, 'UTF-8');

                $heightCount++;                
                if ($heightCount == 4) {
                    $width += 100;
                    $heightCount = 0;
                }
            }
        }
        
        $this->_setFontRegular($page);
        
        return $page;
    }

    /**
     * Format address
     *
     * @param string $address
     * @return array
     */
    protected function _formatAddress($address)
    {
        $return = array();
        foreach (explode('|', $address) as $str) {
            foreach (Mage::helper('core/string')->str_split($str, 65, true, true) as $part) {
                if (empty($part)) {
                    continue;
                }
                $return[] = $part;
            }
        }
        return $return;
    }

    /**
     * Insert a Order information row
     *
     * @param Zend_Pdf_Page $page  given Page to insert row
     * @param string        $key   key to write
     * @param string        $value value to write
     *
     * @return void
     */
    protected function _insertOrderInfoRow(&$page, $key, $value)
    {
        $font = $this->_setFontRegular($page, 8);
        
        $page->drawText(
            $key, 
            self::PAGE_POSITION_RIGHT - 170,
            $this->_height, 
            'UTF-8'
        );
        
        if (is_array($value)) {
            foreach ($value as $valueRow) {
                $valueRow  = trim($valueRow);
                $page->drawText(
                    $valueRow, 
                    self::PAGE_POSITION_RIGHT - 10 - $this->widthForStringUsingFontSize($valueRow, $font, 8),
                    $this->_height, 
                    'UTF-8'
                );
                $this->_height += 14;
            }
        } else { 
            $page->drawText(
                $value, 
                self::PAGE_POSITION_RIGHT - 10 - $this->widthForStringUsingFontSize($value, $font, 8),
                $this->_height, 
                'UTF-8'
            );
            $this->_height += 14;
        }
        
    }

    /**
     * Inserts the Order Information to given page
     *
     * @param Zend_Pdf_Page          $page       given page to insert order info
     * @param Mage_Sales_Model_Order $order      order to get info from
     * @param boolean                $putOrderId print order id
     */
    protected function _insertOrderInfo(&$page, $order, $putOrderId)
    {
        $this->_height = 600;

        
        $this->_insertOrderInfoRow(
            $page,
            Mage::helper('sales')->__('Order Date: '),
            Mage::helper('core')->formatDate(
                $order->getCreatedAtStoreDate(),
                'medium',
                false
            )
        );

        if ($putOrderId) {
            $this->_insertOrderInfoRow(
                $page,
                Mage::helper('sales')->__('Order # '),
                $order->getRealOrderId()
            );
        }
        
        /* Payment */
        $paymentInfo = Mage::helper('payment')->getInfoBlock($order->getPayment())
            ->setIsSecureMode(true)
            ->toPdf();

        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key => $value){
            if (strip_tags(trim($value)) == ''){
                unset($payment[$key]);
            }
        }
        reset($payment);
        
        $this->_insertOrderInfoRow(
            $page,
            Mage::helper('sales')->__('Payment Method:'),
            $payment
        );
        
    }
    
    protected function _insertBillingAddress(&$page, $billingAddress)
    {
        $billingAddress = $this->_formatAddress($billingAddress->format('pdf'));
        $this->_height = 725;
        $this->_width = 40;
        $font = $this->_setFontRegular($page, 10);
        
        foreach ($billingAddress as $addressItem) {
            $page->drawText(
                $addressItem,
                $this->_width,
                $this->_height,
                'UTF-8'
            );
            
            $this->_height -= 14;
        }
    }
    
    protected function setSubject(&$page, $title)
    {
        $this->_setFontBold($page, 16);
        $black = new Zend_Pdf_Color_GrayScale(0);
        $page->setFillColor($black);

        $page->drawText(
            $title,
            self::PAGE_POSITION_LEFT,
            600,
            'UTF-8'
        );
        $this->_setFontRegular($page);
    }
    
    protected function insertOrder(&$page, $order, $putOrderId = true)
    {
        /* @var $order Mage_Sales_Model_Order */

        $this->_insertOrderInfo($page, $order, $putOrderId);

        /* Billing Address */
        $this->_insertBillingAddress($page, $order->getBillingAddress());

        
        $this->_height = 590;
        $this->_width = 40;
        $this->insertTableHeader($page);
    }
    
    protected function insertTableHeader(&$page)
    {
        
        $fontSize = 9;
        $font = $this->_setFontRegular($page, $fontSize);
        $fontHeight = $this->heightForFontUsingFontSize($font, $fontSize);

        $columHeight = $fontHeight + 5;
        $greyScale9 = new Zend_Pdf_Color_GrayScale(0.9);
        $fillType = Zend_Pdf_Page::SHAPE_DRAW_FILL;
        
        $page->setFillColor($greyScale9);
        $page->drawRectangle($this->_width ,$this->_height, self::PAGE_POSITION_RIGHT, $this->_height - $columHeight, $fillType);

        $this->_height -= $fontHeight;
        $black = new Zend_Pdf_Color_GrayScale(0);
        $page->setFillColor($black);

        $page->drawText(
            Mage::helper('invoicepdf')->__('Pos'),
            $this->_width + 3,
            $this->_height,
            'UTF-8'
        );
        $page->drawText(
            Mage::helper('invoicepdf')->__('No.'),
            $this->_width + 45,
            $this->_height,
            'UTF-8'
        );
        $page->drawText(
            Mage::helper('invoicepdf')->__('Description'),
            $this->_width + 110,
            $this->_height,
            'UTF-8'
        );
         
        $singlePrice = Mage::helper('invoicepdf')->__('Price');
        $page->drawText(
            $singlePrice,
            self::PAGE_POSITION_RIGHT - 160 - $this->widthForStringUsingFontSize($singlePrice, $font, $fontSize),
            $this->_height,
            'UTF-8'
        );

        $amountLabel = Mage::helper('invoicepdf')->__('Amount');
        $page->drawText(
            $amountLabel,
            self::PAGE_POSITION_RIGHT - 110 - $this->widthForStringUsingFontSize($amountLabel, $font, $fontSize),
            $this->_height,
            'UTF-8'
        );
        
        $taxLabel = Mage::helper('invoicepdf')->__('Tax');
        $page->drawText(
            $taxLabel,
            self::PAGE_POSITION_RIGHT - 60 - $this->widthForStringUsingFontSize($taxLabel, $font, $fontSize),
            $this->_height,
            'UTF-8'
        ); 

        $totalLabel = Mage::helper('invoicepdf')->__('Total');
        $page->drawText(
            $totalLabel,
            self::PAGE_POSITION_RIGHT - 10 - $this->widthForStringUsingFontSize($totalLabel, $font, $fontSize),
            $this->_height,
            'UTF-8'
        );
    }
}