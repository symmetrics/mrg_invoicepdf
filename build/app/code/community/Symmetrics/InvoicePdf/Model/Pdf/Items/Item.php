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
class Symmetrics_InvoicePdf_Model_Pdf_Items_Item
{
    /**
     * Variable to collect Items
     *
     * @var Varien_Object
     */
    protected $_columns;

    /**
     * type of Item
     *
     * @var integer
     */
    protected $_type;

    const ITEM_TYPE_PRODUCT = 0;
    const ITEM_TYPE_OPTIONS = 1;

    /**
     * constructor to initialize the colums container
     *
     * @return void
     */
    public function __construct()
    {
        $this->_columns = new Varien_Object();
        $this->_type = self::ITEM_TYPE_PRODUCT;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function setTyle($type)
    {
        $this->_type = $type;
    }

    /**
     * add a Coumn
     *
     * @param string        $key
     * @param string        $value
     * @param integer       $paddingLeft
     * @param Zend_Pdf_Font $font
     * @param integer       $fontSize
     *
     * @return Symmetrics_InvoicePdf_Model_Pdf_Items_Item
     */
    public function addColumn($key, $value, $padding = 0, $align = 'left', $font = null, $fontSize = 8)
    {
        $column = new Varien_Object();

        if ($font == null) {
            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        }

        $column->setValue($value);
        $column->setPadding($padding);
        $column->setFont($font);
        $column->setFontSize($fontSize);
        $column->setAlign($align);

        $this->_columns->setData($key, $column);
        return $this;
    }

    /**
     * count the columns
     *
     * @return integer
     */
    public function columnCount()
    {
        return count($this->_columns->getData());
    }

    /**
     * get a column for a specified key
     *
     * @param sring $key
     *
     * @return Varien_Object
     */
    public function getColumn($key)
    {
        return $this->_columns->getData($key);
    }

    /**
     * unset a column for specified key
     *
     * @param string $key
     */
    public function unsetColumn($key)
    {
        $this->_columns->unsetData($key);
    }

    /**
     * check if a column exists
     *
     * @param stirng $key
     *
     * @return boolean
     */
    public function hasColumn($key)
    {
        return $this->_columns->hasData($key);
    }

    public function getAllColumns()
    {
        return $this->_columns->getData();
    }

    public function calculateHeight()
    {
        $columnHeight = 0;
        $columns = $this->getAllColumns();
        
        foreach ($columns as $column) {
            $font = $column->getFont();
            $fontSize = $column->getFontSize();

            $height = $this->_heightForFontUsingFontSize($font, $fontSize);
            if ($columnHeight < $height) {
                $columnHeight = $height;
            }
        }

        return $columnHeight;
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
    protected function _heightForFontUsingFontSize($font, $fontSize)
    {
        $height = $font->getLineHeight();
        $stringHeight = ($height / $font->getUnitsPerEm()) * $fontSize;

        return $stringHeight;
    }
}