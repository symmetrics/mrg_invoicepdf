* DOCUMENTATION

** INSTALLATION
Extract content of this archive to your Magento directory.
It might be necessary to clear/refresh the Magento cache.
Then set the settings in System/Configuration. If an old version is
still installed, the files in app/code/local/Symmetrics/InvoicePdf
should be deleted, before the new version is installed.

** USAGE
The module Symmetrics_InvoicePdf changes the standard PDF
template for invoices, in order to make them valid in Germany.

** FUNCTIONALITY
*** A:  Modifies the PDF template for invoices so that the invoices
		look comliant with law and as usual in Germany
*** B:  The following fields were added: (located under "Configuration
		=> Sales => PDF print-outs"):
       	- Customer number prefix (for this we have created a module in
		backend which allows setting of prefixes depending on
		requirements)
        - Due date of invoice
        - Comment 
        - Customer number prefix
        - Show customer IP in invoice
        - Show footer
        - Show shipping method
        - Show payment method
        - Show infotext
        - Infotext
        - Show infobox
        - Infobox title
        - Infobox
*** C:  When Symmetrics_Imprint module is installed, the data for footer are
		read from the configuration fields of this module and so the entire
		owner information is shown:
		- Full address
		- Contact data such as phone, fax, e-mail etc.
		- Full bank account 
		- Full tax information
*** D:  If Symmetrics_Imprint module is not installed it is checked if
		Symmetrics_Impressum module is installed. If yes, the data from
		configuration fields of this module are taken.
*** E:  An infobox and infotext can be shown on the invoice. 
		 Configuration is as usual in the system configuration.
*** F:  Shipping methods and payment methods are shown optionally.
*** G:  The module adds field "Logo position" in "Configuration => Sales 
		=> Sales => Invoice and Packing Slip Design". The logo that one can
		upload there is used by module and displayed on the set position
		in invoice.

** TECHNICAL
In order to display PDF the abstract class
Symmetrics_InvoicePdf_Model_Pdf_Abstract (file: app/code/community/
Symmetrics/InvoicePdf/Model/Pdf/Abstract.php) is used.
This class provides all necessary methods. For this an abstract method
'getPdf()' is provided which is then used by deriving classes for rendering. The
abstract class takes care also about the management, how texts should be rendered 
on page, and when necessary adds new pages by itself.  

The class by itself can set font '_setFont*', and create a new line with the help
of the font size and text paddings '_newLine(..)'.  In order to create a new page,
'newPage(...)' method is used. Also, if it is wanted, this method creates table 
headers for the product listing, for this it also renders the footer 
'insertAddressFooter(..)', sets a page number and adds a seam and lockmarks.

The method 'insertAddressFooter(..)' uses the internal '_insertAddressFooterItem(..)' 
method in order to display values and keys correctly.
Should the occasion arise, the data are read from Symmetrics_Imprint 
or from Symmetrics_Impressum  module (see FUNCTIONALITY C and D).

The logo is added through 'insertLogo(..)', this method takes into consideration
the postion set in backend and when necessary renders the logo also smaller.

The method '_insertOrderInfo(..)' is used in order to output the order
information, such as for example the orderID or shipping method. This
method takes into consideration all settings that were made in backend.
Internally it uses the method '_insertOrderInfoRow(..)' in order to
represent separate infromation with correct spacing. It ensures that 
the text does not collide into each other. With the method '_insertBillingAddress'
the billing address is added, besides it adds the sender's address through the
recepient. The method 'setSubject(..)' sets a title for page. The 'insertOrder(..)'
method is the central method for adding order info and the billing address.
The 'insertTableHeader(..)' method is used, as already mentioned, for 'newPage(..)'.
This method makes a table header.

With the method 'insertTableRow(..)' a table line is added.
For this Symmetrics_InvoicePdf_Model_Pdf_Items_Abstract object is passed.
With the help of this information this method can accurately calculate
how large the line is, and when necessary wraps it on a new page.
This abstract class is described further below.

With the '_drawItem(..)' method separate items are rendered, which are
registered in config.xml. The associated abstract class will be described
further below.

In order to show totals 'insertTotals()'  method is used. This method is similar to
'_drawItem(..)' and uses the same basic functionality. In order to render items
(products, totals, or everything possible), 'Symmetrics_InvoicePdf_Model_Pdf_Items_Abstract'
class is used. It has more setter and getter methods, which are used depending on a
situation. For the most important functions for the representation there are
'draw()', 'calculateHeight()' and 'addRow(..)' methods. 'draw()' is an abstract
method and is implemented by separate items. With 'calculateHeigth()' the size is
calculated, this method is then called in 'insertTableRow(..)'  of
Symmetrics_InvoicePdf_Model_Pdf_Abstract.
In order to add an item at first, 'addRow(..)' method is used. It accepts as parameter
only one instance of class 'Symmetrics_InvoicePdf_Model_Pdf_Items_Item'.
This class is derived from no other class and serves as a general item container.
It has almost the same functionality as 'Symmetrics_InvoicePdf_Model_Pdf_Items_Abstract'
only that it saves the value pairs, which are interpreted as a separate column. One
such column is wrapped up in container with 'addColumn(..)'. This class contains also
the method 'calculateHeigth()' which is called by the method of the same name in the
class 'Symmetrics_InvoicePdf_Model_Pdf_Items_Abstract'. Now the size is actually
calculated with the help of font and text.

In order to represent an ivoice from this class structure, 
'Symmetrics_InvoicePdf_Model_Pdf_Invoice' class is used. It contains only one 'getPdf()'
mehtod, which is overwritten and 4 other methods that serve for display of additional
information. Whereby one of it rewrites the  '_insertOrderInfo()' method, in order to
output the invoiceID. In other methods the same renderer principle is used as
for example for products or totals.
This class is used in the overwritten action controllers
Symmetrics_InvoicePdf_Adminhtml_Sales_InvoiceController (for invoice overview, 
see doc/screenshots/screenshot_rechnungsuebersicht.png) in order to offfer 
the invoices as download.


** PROBLEMS
Invoices are not automatically sent after the manual generation.

* TESTCASES
** BASIC
*** A:  Check if the invoice looks as a normal German invoice.
		You can find 2 examples in doc/examples directory.
*** B:  Check if different fields are shown in the invoice, when they are activated.
*** C:  Go in backend to "Configuration => Sales  => Sales => Invoice and Packing Slip Design
		=> Address" and enter an address. Check if it appears in footer when it is activated 
		(setting: "show footer"). Now install the Symmetrics_Impressum module and
		enter data in the corresponding fields of module in the system configuration
		"General => Impressum". Check if data appear correctly on the invoice, i.e. all
		filled in fields have been taken over correctly and no more the data from field
		"Configuration => Sales  => Sales => Invoice and Packing Slip Design
		=> Address"
*** D:  Uninstall the module Symmetrics_Impressum  and install Symmetrics_Imprint.
		Fill in the fields of module in the configuration "General => Imprint" and
		check if data from these fields now appear in footer. Also check whether
		when both modules are installed, the data from Symmetrics_Imprint are taken
		and not from Symmetrics_Impressum.
		Also pay attention that fields that are marked with tag <hide_in_invoice_pdf>
		in the system.xml of impressum or imprint moduls, are ignored and not taken
		over in footer.
*** E:  Fill in 5 fields for the info boxes and fields and check if the invoice
		is changed respectively.
*** F:  Activate / deactivate settings "show shipping method" and "show payment
		method" and check if shippint method or payment method is correctly shown
		or not shown on the invoice.
*** G: 	Check if Logo position field is available and one can choose between the
		options "left, center, right". Upload a logo and check if it appears on
		invoice. Also check if logo position is changed correctly when you 
		change the "Logo position" setting accordingly.