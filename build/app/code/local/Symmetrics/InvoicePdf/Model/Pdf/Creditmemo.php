<?php
class Symmetrics_InvoicePdf_Model_Pdf_Creditmemo extends Symmetrics_InvoicePdf_Model_Pdf_Invoice
{
	function __construct()
	{
		parent::__construct();
		
		$this->setMode('creditmemo');
	}
}