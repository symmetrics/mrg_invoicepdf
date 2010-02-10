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
 * Main class, coordinate all the drawing on the invoice
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
class Symmetrics_InvoicePdf_Model_Pdf_Invoice
    extends Mage_Sales_Model_Order_Pdf_Abstract
{
    /**
     * multiple color objects
     * 
     * @var array
     */
    public $colors;
    
    /**
     * Encoding (utf-8)
     * 
     * @var string
     */
    public $encoding;
    
    /**
     * dots from left and right border
     * 
     * @var array
     */
    public $margin;
    
    /**
     * Symmetrics_Impressum data
     * 
     * @var array
     */
    public $impressum;
    
    /**
     * current page number
     * 
     * @var int
     */
    public $pagecounter;
    
    /**
     * invoice or memo
     * 
     * @var string
     */
    public $mode;
    
    /**
     * set some default values
     * 
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->encoding = 'UTF-8';
        $this->colors['black'] = new Zend_Pdf_Color_GrayScale(0);
        $this->colors['greyPos1'] = new Zend_Pdf_Color_GrayScale(0.9);
        $this->margin['left'] = 45;
        $this->margin['right'] = 540;
        $impressum = Mage::getConfig()->getNode('modules/Symmetrics_Impressum');
        
        if (is_object($impressum)) {
            if ($impressum->active == 'true') {
                $imprintModel = 'Symmetrics_Impressum_Block_Impressum';
                $this->impressum = Mage::getModel($imprintModel)->getImpressumData();
            }
        } else {
            $this->impressum = false;
        }
        $this->setMode('invoice');
    }
    
    /**
     * Calls all the helper methods
     * 
     * @param array $invoices all invoices for the order
     * 
     * @return Zend_Pdf
     */
    public function getPdf($invoices = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');
        $mode = $this->getMode();
        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
        $this->pagecounter = 1;

        foreach ($invoices as $invoice) {
            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
            $pdf->pages[] = $page;

            $order = $invoice->getOrder();

            /* add logo */
            $this->_insertLogo($page, $invoice->getStore());
            
            /* add billing address */
            $this->y = 692;
            $this->_insertBillingAddress($page, $order);

            /* add sender address */
            $this->y = 705;
            $this->_insertSenderAddress($page);
            
            /* add header */
            $this->y = 592;
            $this->_insertHeader($page, $order, $invoice);

            /* 
             * add footer if the impressum module is 
             * installed and "insert footer" switch 
             * in configuration is enabled 
             * */
            if ($this->impressum && Mage::getStoreConfig('sales_pdf/invoice/showfooter') == 1) {
                $this->y = 110;
                $this->_insertFooter($page, $invoice);
            }
            
            /* add page counter */
            $this->y = 110;
            $this->_insertPageCounter($page);
            
            /* add table header */
            $this->_setFontRegular($page, 9);
            $this->y = 562;
            $this->_insertTableHeader($page);
            $this->y -=20;
            $position = 0;
            foreach ($invoice->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                
                $this->_checkPageBreak($page);
                $position++;
                $page = $this->_drawItem($item, $page, $order, $position);
            }
            /* add totals */
            $page = $this->_insertTotals($page, $invoice);
            /* add note */
            if ($mode == 'invoice') {
                $this->_checkPageBreak($page);
                $page = $this->_insertNote($page);
            }
            if (Mage::getStoreConfig('sales_pdf/invoice/showpayment')) {
                $this->_checkPageBreak($page);
                $this->_insertPayment($page, $invoice);
            }
            if (Mage::getStoreConfig('sales_pdf/invoice/showcarrier')) {
                $this->_checkPageBreak($page);
                $this->_insertCarrier($page, $invoice);
            }
            if (Mage::getStoreConfig('sales_pdf/invoice/showinfotxt')) {
                $this->_checkPageBreak($page);
                $this->_insertInfoTxt($page);
            }
            if (Mage::getStoreConfig('sales_pdf/invoice/showinfobox')) {
                $this->_checkPageBreak($page);
                $this->_insertInfoBox($page);
            }
        }
        $this->_afterGetPdf();
        return $pdf;
    }
    
    /**
     * Check if the page is full and create a new one
     * 
     * @param Zend_Pdf_Page &$page  Page object
     * @param int           $border distance from footer
     * @param int           $bottom current position, if not set
     * 
     * @return void
     */
    protected function _checkPageBreak(&$page, $border = 170, $bottom = false)
    {
        if ($bottom === false) {
            $bottom = $this->y;
        }
        if ($bottom < $border) {
            $page = $this->newPage(array());
        }
        return $page;
    }

    /**
     * Insert date notes
     * 
     * @param Zend_Pdf_Page $page page object
     * 
     * @return Zend_Pdf_Page
     */
    protected function _insertNote($page)
    {
        $this->_setFontRegular($page, 10);

        $maturity = Mage::helper('invoicepdf')->__(
            'Invoice maturity: %s days',
            Mage::getStoreConfig('sales_pdf/invoice/maturity')
        );
        if (!empty($maturity)) {
            $page->drawText($maturity, $this->margin['left'], $this->y + 50, $this->encoding);
        }
        $this->_Ln(15);
        $notice = Mage::helper('invoicepdf')->__('Invoice date is equal to delivery date');
        $page->drawText($notice, $this->margin['left'], $this->y + 50, $this->encoding);
        
        $note = Mage::getStoreConfig('sales_pdf/invoice/note');

        if (!empty($note)) {
            $page->drawText($note, $this->margin['left'], $this->y + 30, $this->encoding);
        }
        return $page;
    }
    
    /**
     * insert page counter
     * 
     * @param Zend_Pdf_Page &$page page object
     * 
     * @return Zend_Pdf_Page
     */
    protected function _insertPageCounter(&$page)
    {
        $font = $this->_setFontRegular($page, 9);
        $xPosition = $this->margin['right'] - 23 - $this->widthForStringUsingFontSize($this->pagecounter, $font, 9);
        $counterText = Mage::helper('invoicepdf')->__('Page').' '.$this->pagecounter;
        $page->drawText(
            $counterText,
            $xPosition,
            $this->y,
            $this->encoding
        );
    }
    
    /**
     * Insert footer table
     * 
     * @param Zend_Pdf_Page            &$page   page object
     * @param Mage_Sales_Order_Invoice $invoice invoice object
     * 
     * @return void
     */
    protected function _insertFooter(&$page, $invoice = null) 
    {
        $page->setLineColor($this->colors['black']);
        $page->setLineWidth(0.5);
        $page->drawLine($this->margin['left'] - 20, $this->y - 5, $this->margin['right'] + 30, $this->y - 5);
        
        $this->_Ln(15);
        $this->_insertFooterAddress($page);

        $fields = array(
            'telephone' => Mage::helper('impressum')->__('Telephone:'),
            'fax' => Mage::helper('impressum')->__('Fax:'),
            'email' => Mage::helper('impressum')->__('E-Mail:'),
            'web' => Mage::helper('impressum')->__('Web:')
        );
        $this->_insertFooterBlock($page, $fields, 70, 40);
        
        $fields = array(
            'bankname' => Mage::helper('impressum')->__('Bank name:'),
            'bankaccount' => Mage::helper('impressum')->__('Account:'),
            'bankcodenumber' => Mage::helper('impressum')->__('Bank number:'),
            'bankaccountowner' => Mage::helper('impressum')->__('Account owner:')
        );
        $this->_insertFooterBlock($page, $fields, 215, 50);
        
        $fields = array(
            'taxnumber' => Mage::helper('impressum')->__('Tax number:'),
            'vatid' => Mage::helper('impressum')->__('VAT-ID:'),
            'hrb' => Mage::helper('impressum')->__('Register number:'),
            'ceo' => Mage::helper('impressum')->__('CEO:')
        );
        $this->_insertFooterBlock($page, $fields, 355, 60);
    }
    
    /**
     * Insert table header
     * 
     * @param Zend_Pdf_Page &$page page object
     * 
     * @return void
     */
    protected function _insertTableHeader(&$page)
    {
        $page->setFillColor($this->colors['greyPos1']);
        $page->setLineColor($this->colors['greyPos1']);
        $page->setLineWidth(1);
        $page->drawRectangle($this->margin['left'], $this->y, $this->margin['right'], $this->y - 15);

        $page->setFillColor($this->colors['black']);
        $font = $this->_setFontRegular($page, 9);
        
        $this->y -= 11;
        $page->drawText(
            Mage::helper('invoicepdf')->__('Pos'),
            $this->margin['left'] + 3,
            $this->y,
            $this->encoding
        );
        $page->drawText(
            Mage::helper('invoicepdf')->__('No.'),
            $this->margin['left'] + 45,
            $this->y,
            $this->encoding
        );
        $page->drawText(
            Mage::helper('invoicepdf')->__('Description'),
            $this->margin['left'] + 110,
            $this->y,
            $this->encoding
        );
        
        $singlePrice = Mage::helper('invoicepdf')->__('Price');
        $page->drawText(
            $singlePrice,
            $this->margin['right'] - 160 - $this->widthForStringUsingFontSize($singlePrice, $font, 9),
            $this->y,
            $this->encoding
        );
        
        $page->drawText(
            Mage::helper('invoicepdf')->__('Amount'),
            $this->margin['left'] + 360,
            $this->y,
            $this->encoding
        );
        
        $taxLabel = Mage::helper('invoicepdf')->__('Tax');
        $page->drawText(
            $taxLabel,
            $this->margin['right'] - 65 - $this->widthForStringUsingFontSize($taxLabel, $font, 9),
            $this->y,
            $this->encoding
        );
        
        $totalLabel = Mage::helper('invoicepdf')->__('Total');
        $page->drawText(
            $totalLabel, $this->margin['right'] - 10 - $this->widthForStringUsingFontSize($totalLabel, $font, 10),
            $this->y,
            $this->encoding
        );
    }
    
    /**
     * Insert header and subject
     * 
     * @param Zend_Pdf_Page                  &$page   page object
     * @param Mage_Sales_Model_Order         $order   order object
     * @param Mage_Sales_Model_Order_Invoice $invoice invoice object
     * 
     * @return void
     */
    protected function _insertHeader(&$page, $order, $invoice)
    {
        $page->setFillColor($this->colors['black']);
        $mode = $this->getMode();
        $this->_setFontBold($page, 15);
        // Subject
        if ($mode == 'invoice') {
            $invoiceMode = 'Invoice';
            $invoiceIdLabel = 'Invoice number:';
        } else {
            $invoiceMode = 'Creditmemo';
            $invoiceIdLabel = 'Creditmemo number:';
        }
        $invoiceMode = Mage::helper('invoicepdf')->__($invoiceMode);
        $invoiceIdLabel = Mage::helper('invoicepdf')->__($invoiceIdLabel);
        $page->drawText(
            $invoiceMode,
            $this->margin['left'],
            $this->y,
            $this->encoding
        );
        $this->_setFontRegular($page);
        $this->y += 64;
        $rightoffset = 180;
        // Invoice id label
        $page->drawText(
            $invoiceIdLabel,
            ($this->margin['right'] - $rightoffset),
            $this->y,
            $this->encoding
        );
        $this->_Ln();
        // Customer id label
        $page->drawText(
            Mage::helper('invoicepdf')->__('Customer number:'),
            ($this->margin['right'] - $rightoffset),
            $this->y,
            $this->encoding
        );
        $this->_Ln();
        $yPlus = 30;
        // If show ip, draw label
        if (Mage::getStoreConfig('sales_pdf/invoice/showcustomerip')) {
            $page->drawText(
                Mage::helper('invoicepdf')->__('Customer IP:'),
                ($this->margin['right'] - $rightoffset),
                $this->y,
                $this->encoding
            );
            $this->_Ln();
            $yPlus += 15;
        }
        // if show order id, draw label
        if (Mage::getStoreConfig('sales_pdf/invoice/put_order_id')) {
            $page->drawText(
                Mage::helper('invoicepdf')->__('Order ID:'),
                ($this->margin['right'] - $rightoffset),
                $this->y,
                $this->encoding
            );
            $this->_Ln();
            $yPlus += 15;
        }
        // date label
        $page->drawText(
            Mage::helper('invoicepdf')->__('Invoice date:'),
            ($this->margin['right'] - $rightoffset),
            $this->y,
            $this->encoding
        );
        
        $this->y += $yPlus;
        $rightoffset = 60;      
        
        // Invoice id
        $page->drawText(
            $invoice->getIncrementId(),
            ($this->margin['right'] - $rightoffset),
            $this->y,
            $this->encoding
        );
        $this->_Ln();
        $customerid = $order->getBillingAddress()->getCustomerId();
        if (!empty($customerid)) {
            $prefix = Mage::getStoreConfig('sales_pdf/invoice/customeridprefix');
            if (!empty($prefix)) {
                $customerid = $prefix . $customerid;   
            }
        } else {
            $customerid = '-';
        }
        $rightoffset = 10;
        // customer id
        $font = $this->_setFontRegular($page, 10);
        $page->drawText(
            $customerid,
            ($this->margin['right'] - $rightoffset - $this->widthForStringUsingFontSize($customerid, $font, 10)),
            $this->y,
            $this->encoding
        );
        $this->_Ln();
        // customer IP
        if (Mage::getStoreConfig('sales_pdf/invoice/showcustomerip')) {
            $customerIP = $order->getData('remote_ip');
            $font = $this->_setFontRegular($page, 10);
            $page->drawText(
                $customerIP,
                ($this->margin['right'] - $rightoffset - $this->widthForStringUsingFontSize($customerIP, $font, 10)),
                $this->y,
                $this->encoding
            );
            $this->_Ln();
        }
        // order ID
        if (Mage::getStoreConfig('sales_pdf/invoice/put_order_id')) {
            $rightoffset = 10;
            $font = $this->_setFontRegular($page, 10);
            $orderid = $order->getRealOrderId();
            $page->drawText(
                $orderid,
                ($this->margin['right'] - $rightoffset - $this->widthForStringUsingFontSize($orderid, $font, 10)),
                $this->y,
                $this->encoding
            );
            $this->_Ln();
        }
        // invoice date
        $invoiceDate = Mage::helper('core')->formatDate($order->getCreatedAtDate(), 'medium', false);
        $page->drawText(
            $invoiceDate,
            ($this->margin['right'] - $rightoffset - $this->widthForStringUsingFontSize($invoiceDate, $font, 10)),
            $this->y,
            $this->encoding
        );
    }
    
    /**
     * Split text to multiple array elements
     * 
     * @param string $text text to split
     * 
     * @return array
     */
    protected function _lineSplit($text)
    {
        $text = trim($text);
        $splitted = array();
        if (!empty($text)) {
            $text = str_replace("\r\n", "\n", $text);
            $text = str_replace("\r", "\n", $text);
            $splitted = explode("\n", $text);
        }
        return $splitted;
    }
    
    /**
     * Insert info text below invoice
     * 
     * @param Zend_Pdf_Page &$page page object
     * 
     * @return Zend_Pdf_Page
     */
    protected function _insertInfoTxt(&$page)
    {
        $fontSize = 10;
        $this->_setFontRegular($page, $fontSize);
        $infoTxt = Mage::getStoreConfig('sales_pdf/invoice/infotxt');
        
        $infoTxtLines = $this->_lineSplit($infoTxt);
        $calculatedHeight = (count($infoTxtLines) * ($fontSize * 1.44)) + 20;
        $calculatedEnd = $this->y - $calculatedHeight;
        $this->_checkPageBreak($page, 80, $calculatedEnd);
        $calculatedEnd = $this->y - $calculatedHeight;
        $this->_Ln();
        $this->_Ln();
        
        foreach ($infoTxtLines as $infoTxtLine) {
            $this->_setFontRegular($page, $fontSize);
            $page->drawText($infoTxtLine, $this->margin['left'], $this->y, $this->encoding);
            $this->_Ln();
            $this->_checkPageBreak($page);
        }
    }
    
    /**
     * Insert info box at the bottom
     * 
     * @param Zend_Pdf_Page &$page page object
     * 
     * @return Zend_Pdf_Page
     */
    protected function _insertInfoBox(&$page)
    {
        $fontSize = 10;
        $infoBox = Mage::getStoreConfig('sales_pdf/invoice/infobox');
        $infoBoxHl = Mage::getStoreConfig('sales_pdf/invoice/infoboxhl');
        $infoBoxLines = $this->_lineSplit($infoBox);
        $calculatedBoxHeight = (count($infoBoxLines) * ($fontSize * 1.44)) + 100;
        $calculatedEnd = $this->y - $calculatedBoxHeight;
        $this->_checkPageBreak($page, 80, $calculatedEnd);
        $calculatedEnd = $this->y - $calculatedBoxHeight;
        
        // Draw recangle (box)
        $xPos1 = $this->margin['left'];
        $xPos2 = $this->margin['right'];
        $yPos1 = $this->y - 10;
        $yPos2 = $this->y - $calculatedBoxHeight + 40;
        $textX = $this->margin['left'] + 20;
        $black = new Zend_Pdf_Color_GrayScale(0);
        $page = $page->setLineColor($black);
        $page = $page->setLineWidth(1);
        $page = $page->drawRectangle($xPos1, $yPos1, $xPos2, $yPos2, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
        
        $this->y -= 30;
        
        $this->_setFontBold($page, $fontSize + 2);
        $page->drawText($infoBoxHl, $textX, $this->y, $this->encoding);
        $this->y -= 24;
        

        $this->_setFontBold($page, $fontSize);
        foreach ($infoBoxLines as $infoBoxLine) {
            $page->drawText($infoBoxLine, $textX, $this->y, $this->encoding);
            $this->_Ln();
        }
    }
    
    /**
     * Insert payment information
     * 
     * @param Zend_Pdf_Page                  $page    page object
     * @param Mage_Sales_Model_Order_Invoice $invoice invoice object
     * 
     * @return Zend_Pdf_Page
     */
    protected function _insertPayment($page, $invoice)
    {
        $paymentMethod = $invoice
            ->getorder()
            ->getPayment()
            ->getMethodInstance()
            ->getTitle();
        $paymentMethod = Mage::helper('invoicepdf')->__('Payment method: %s', $paymentMethod);
        $page->drawText($paymentMethod, $this->margin['left'], $this->y, $this->encoding);
        $this->_Ln();
    }
    
    /**
     * Insert carrier information
     * 
     * @param Zend_Pdf_Page                  $page    page object
     * @param Mage_Sales_Model_Order_Invoice $invoice invoice object
     * 
     * @return Zend_Pdf_Page
     */
    protected function _insertCarrier($page, $invoice)
    {
        $order = $invoice->getorder();
        $carrier = $order->getShippingDescription();
        $carrier = Mage::helper('invoicepdf')->__('Shipping method: %s', $carrier);
        $page->drawText($carrier, $this->margin['left'], $this->y, $this->encoding);
        $this->_Ln();
    }

    /**
     * Insert billing address
     * 
     * @param Zend_Pdf_Page          $page  page object
     * @param Mage_Sales_Model_Order $order order object
     * 
     * @return void
     */
    protected function _insertBillingAddress($page, $order)
    {
        $this->_setFontRegular($page, 9);
        $billing = $this->_formatAddress($order->getBillingAddress()->format('pdf'));
        
        foreach ($billing as $line) {
            $page->drawText(trim(strip_tags($line)), $this->margin['left'], $this->y, $this->encoding);
            $this->_Ln(12);
        }
    }
    
    /**
     * Insert footer block 
     * 
     * @param Zend_Pdf_Page &$page       page object
     * @param array         $fields      Info fields
     * @param int           $colposition Column position
     * @param int           $valadjust   Value padding
     * 
     * @return void
     */
    protected function _insertFooterBlock(&$page, $fields, $colposition = 0, $valadjust = 30)
    {
        $this->_setFontRegular($page, 7);
        $y = $this->y;
        $valposition = $colposition + $valadjust;
        if (is_array($fields)) {
            foreach ($fields as $field => $label) {
                if (empty($this->impressum[$field])) {
                    continue;
                }
                $page->drawText($label, $this->margin['left'] + $colposition, $y, $this->encoding);
                $page->drawText($this->impressum[$field], $this->margin['left'] + $valposition, $y, $this->encoding);
                $y -= 12;
            }
        }
    }
    
    /**
     * Insert footer address
     * 
     * @param Zend_Pdf_Page &$page page object
     * @param int           $store Store ID
     * 
     * @return void
     */
    protected function _insertFooterAddress(&$page, $store = null)
    {       
        $this->_setFontRegular($page, 7);
        $y = $this->y;
        $address = explode(
            "\n",
            Mage::getStoreConfig('sales/identity/address', $store)
        );
        foreach ($address as $value) {
            if ($value!=='') {
                $page->drawText(
                    trim(strip_tags($value)),
                    $this->margin['left'] - 20,
                    $y,
                    $this->encoding
                );
                $y -= 12;
            }
        }
    }
    
    /**
     * Insert logo in header
     * 
     * @param Zend_Pdf_Page &$page page object
     * @param int           $store Store ID
     * 
     * @return void
     */
    protected function _insertLogo(&$page, $store = null) 
    {
        $maxwidth = 500;
        $maxheight = 50;
        
        $image = Mage::getStoreConfig('sales/identity/logo', $store);
        $imagePath = Mage::getStoreConfig('system/filesystem/media', $store);
        $imagePath .= '/sales/store/logo/';
        $imagePath .= $image;
        if ($image && file_exists($imagePath)) {
            $image = $imagePath;
            $size = getimagesize($image);
            $width = $size[0];
            $height = $size[1];
            if ($width > $height) {
                $ratio = $width / $height;
            } elseif ($height > $width) {
                $ratio = $height / $width;
            } else {
                $ratio = 1;
            }
            
            if ($height > $maxheight or $width > $maxwidth) {
                if ($height > $maxheight) {
                    $height = $maxheight;
                    $width = round($maxheight * $ratio);
                }
                if ($width > $maxwidth) {
                    $width = $maxheight;
                    $height = round($maxwidth * $ratio);
                }
            }

            if (is_file($image)) {
                $image = Zend_Pdf_Image::imageWithPath($image);
                $logoPosition = Mage::getStoreConfig('sales/identity/logoposition', $store);
                switch($logoPosition) {
                    case 'center':
                        $startLogoAt = $this->margin['left'];
                        $startLogoAt += ( ($this->margin['right'] - $this->margin['left']) / 2 );
                        $startLogoAt -= $width / 2;
                        break;
                    case 'right':
                        $startLogoAt = $this->margin['right'] - $width;
                        break;
                    default:
                        $startLogoAt = $this->margin['left'];
                }
                $position['xPos1'] = $startLogoAt;
                $position['yPos1'] = 762;
                $position['xPos2'] = $position['xPos1'] + $width;
                $position['yPos2'] = $position['yPos1'] + $height;
                $page->drawImage(
                    $image,
                    $position['xPos1'],
                    $position['yPos1'],
                    $position['xPos2'],
                    $position['yPos2']
                );
            }
        }
    }

    /**
     * Insert totals (sums)
     * 
     * @param Zend_Pdf_Page                  $page   page object
     * @param Mage_Sales_Model_Order_Invoice $source invoice object
     * 
     * @return Zend_Pdf_Page
     */
    protected function _insertTotals($page, $source)
    {    
        $this->y -=20;
        
        $order = $source->getOrder();
        $totals = $this->_getTotalsList($source);
        $lineBlock = array(
            'lines'  => array(),
            'height' => 17
        );
        foreach ($totals as $total) {
            $total->setOrder($order)
                ->setSource($source);

            if ($total->canDisplay()) {
                foreach ($total->getTotalsForDisplay() as $totalData) {
                    
                    // get font size
                    if ($totalData['font_size']) {
                        $fontSize = $totalData['font_size'];
                    }
                    else {
                        $fontSize = 10;
                    }
                    
                    // set font size for subtotal excl tax
                    if (array_key_exists('code', $totalData) && $totalData['code'] == 'subtotal_excl_tax') {
                        $fontSize = 7;
                    }
                    
                    // get font weight
                    if (array_key_exists('font_weight', $totalData) && $totalData['font_weight']) {
                        $fontWeight = $totalData['font_weight'];
                    }
                    else {
                        $fontWeight = 'regular';   
                    }
                    
                    $lineBlock['lines'][] = array(
                        array(
                            'text' => $totalData['label'],
                            'feed' => $this->margin['right'] - 100,
                            'align' => 'right',
                            'font_size' => $fontSize,
                            'font' => $fontWeight
                        ),
                        array(
                            'text' => $totalData['amount'],
                            'feed' => $this->margin['right'] - 10,
                            'align' => 'right',
                            'font_size' => $fontSize,
                            'font' => $fontWeight
                        ),
                    );
                }
            }
        }
        $page = $this->drawLineBlocks($page, array($lineBlock));
        return $page;
    }
    
    /**
    /**
     * Create line break
     * 
     * @param int $height distance to add
     * 
     * @return void
     */
    protected function _Ln($height = 15)
    {
        $this->y -= $height;
    }
    
    /**
     * set regular font size 
     * 
     * @param Zend_Pdf_Page $object page object
     * @param int           $size   font size
     * 
     * @return Zend_Pdf_Font
     */
    protected function _setFontRegular($object, $size = 10)
    {
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * set bold font size 
     * 
     * @param Zend_Pdf_Page $object page object
     * @param int           $size   font size
     * 
     * @return Zend_Pdf_Font
     */
    protected function _setFontBold($object, $size = 10)
    {
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * set italic font size 
     * 
     * @param Zend_Pdf_Page $object page object
     * @param int           $size   font size
     * 
     * @return Zend_Pdf_Font
     */
    protected function _setFontItalic($object, $size = 10)
    {
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
        $object->setFont($font, $size);
        return $font;
    }
    
    /**
     * Draw product
     * 
     * @param Varien_Object          $item     product object
     * @param Zend_Pdf_Page          $page    page object
     * @param Mage_Sales_Model_Order $order    order object
     * @param int                    $position product position on invoice
     * 
     * @return Zend_Pdf_Page
     */
    protected function _drawItem(Varien_Object $item, Zend_Pdf_Page $page, Mage_Sales_Model_Order $order, $position = 1)
    {
        $type = $item->getOrderItem()->getProductType();
        $renderer = $this->_getRenderer($type);
        $renderer->setOrder($order);
        $renderer->setItem($item);
        $renderer->setPdf($this);
        $renderer->setPage($page);
        $renderer->setRenderedModel($this);
        $renderer->draw($position);
        return $renderer->getPage();
    }
    
    /**
     * Mutator for mode
     * 
     * @param string $mode invoice|creditmemo
     * 
     * @return void
     */
    public function setMode($mode = 'invoice')
    {
        $this->mode = $mode;
    }
    
    /**
     * getter for mode
     * 
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }
    
    /**
     * Create new page object
     * 
     * @param array $settings settings array
     * 
     * @return Zend_Pdf_Page
     */
    public function newPage(array $settings = array())
    {
        $pdf = $this->_getPdf();
        $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
        $pdf->pages[] = $page;
        if ($this->impressum && Mage::getStoreConfig('sales_pdf/invoice/showfooter') == 1) {
            $this->y = 100;
            $this->_insertFooter($page);
        }
        $this->pagecounter++;
        $this->y = 110;
        $this->_insertPageCounter($page);
        $this->y = 800;
        $this->_setFontRegular($page, 9);
        return $page;
    }
    
    /**
     * Insert sender address
     * 
     * @param Zend_Pdf_Page $page page object
     * 
     * @return void
     */
    protected function _insertSenderAddress($page) 
    {
        if ($senderAddress = Mage::getStoreConfig('sales_pdf/invoice/senderaddress')) {
            $this->_setFontRegular($page, 7);
            $page->drawText($senderAddress, $this->margin['left'], $this->y, $this->encoding);
        }
        return;
    }
}
