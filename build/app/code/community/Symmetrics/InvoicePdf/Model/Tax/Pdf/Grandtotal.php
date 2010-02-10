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
 * @copyright 2009 Symmetrics GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.symmetrics.de/
 */
/**
 * Modified grandtotal and tax for Germany 
 * 
 * @category  Symmetrics
 * @package   Symmetrics_InvoicePdf
 * @author    Symmetrics GmbH <info@symmetrics.de>
 * @author    Eugen Gitin <eg@symmetrics.de>
 * @copyright 2009 Symmetrics GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.symmetrics.de/
 */

 class Symmetrics_InvoicePdf_Model_Tax_Pdf_Grandtotal extends Mage_Tax_Model_Sales_Pdf_Grandtotal
 {
     /**
     * Return modified array for rendering
     * 
     * @return array
     */
    public function getTotalsForDisplay()
    {
        $totals = array();
        $order = $this->getOrder();
        $store = $order->getStore();
        $config = Mage::getSingleton('tax/config');
        $totalsConfig = Mage::getConfig()->getNode('global/pdf/totals')->asArray();

        // if not configured to display full taxes > show normal totals
        if (!$config->displaySalesTaxWithGrandTotal($store)) {
            return parent::getTotalsForDisplay();
        }
        
        // insert all applied taxes
        $taxInfo = $order->getFullTaxInfo();
        if (is_array($taxInfo) && $config->displaySalesTaxWithGrandTotal($store)) {
            foreach ($taxInfo as $appliedTax) {
                if ($appliedTax['hidden'] == 1) {
                    continue;
                }
                $taxAmount = $this->getOrder()->formatPriceTxt($appliedTax['amount']);
                $taxRate = $appliedTax['rates'][0]['percent'];
                $totals[] = array (
                    'code' => 'tax',
                    'amount' => $taxAmount,
                    'label' => sprintf(Mage::helper('invoicepdf')->__('Incl. Tax (%s%%)'), $taxRate) . ':',
                    'font_size' => $totalsConfig['tax']['font_size'],
                    'font_weight' => $totalsConfig['tax']['font_weight']
                );
            }
        }
        
        // insert grand total excl. tax
        $amount = $this->getOrder()->formatPriceTxt($this->getAmount());
        $amountExclTax = $this->getAmount()-$this->getSource()->getTaxAmount();
        $amountExclTax = $this->getOrder()->formatPriceTxt($amountExclTax);
        $totals[] = array (
            'code' => 'subtotal_excl_tax',
            'amount' => $this->getAmountPrefix() . $amountExclTax,
            'label' => Mage::helper('tax')->__('Grand Total (Excl. Tax)') . ':',
            'font_size' => $totalsConfig['subtotal']['font_size'],
            'font_weight' => $totalsConfig['subtotal']['font_weight']
        );

        // insert grand total incl. tax
        $amount = $this->getOrder()->formatPriceTxt($this->getAmount());
        $totals[] = array (
            'code' => 'subtotal_incl_tax',
            'amount' => $this->getAmountPrefix() . $amount,
            'label' => Mage::helper('invoicepdf')->__('Grand Total (Incl. Tax)') . ':',
            'font_size' => $totalsConfig['grand_total']['font_size'],
            'font_weight' => $totalsConfig['grand_total']['font_weight']
        );
        
        return $totals;
    }
 }
 