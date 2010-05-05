* DOCUMENTATION

** INSTALLATION
Extrahieren Sie den Inhalt dieses Archivs in Ihr Magento Verzeichnis.
Ggf. ist das Leeren/Auffrischen des Magento-Caches notwendig.
Setzen Sie dann die Einstellungen in der System/Konfiguration.
Wenn noch eine alte Version installiert ist, sollten die Dateien unter
app/code/local/Symmetrics/InvoicePdf entfernt werden, bevor die neue Version
installiert wird.

** USAGE
Das Modul Symmetrics_InvoicePdf verändert die Standard
PDF-Vorlage für Rechnungen, um sie in Deutschland rechtsgültig
zu machen.

** FUNCTIONALITY
*** A: Modifiziert die PDF-Vorlage für Rechnungen so, dass
	die Rechnungen rechtlich konform und wie in Deutschland
	üblich aussehen. 
*** B: Zusätzliche Felder: (befinden sich unter
	"Configuration -> Sales -> PDF-Print-outs" )
**** a: Kundennummer-Präfix (Dafür haben wir im Backend ein Modul
	    geschaffen, welches das Setzen des Prefixes frei erlaubt,
	    je nach Anforderungen)
**** b: Fälligkeit der Rechnung
**** c: Notiz
**** d: mehr siehe D - G
*** C: Wenn das Modul Symmetrics_Imprint installiert ist
	werden im Footer alle Betreiberinformationen angezeigt:
		- Vollständige Anschrift
		- Kommunikationsdaten wie Telefon, Fax, E-Mail usw.
		- Vollständige Kontoverbindung
		- Vollständige Steuerinformationen
*** D: Es kann eine Infobox und ein Infotext auf der Rechnung
	angezeigt werden. Konfiguration ist wie üblich in der System
	Konfiguration.
*** E: Es werden Versandmethode und Zahlungsmethode optional angezeigt.
*** F: Das Modul benutzt ein Logo, welches im Backend unter
	Configuration -> Sales -> Sales -> Invoice and Packing
	Slip Design" geändert werden kann.

** TECHNICAL
Um die PDF darzustellen wird eine Abstrakte Klasse genutzt, 
Symmetrics_InvoicePdf_Model_Pdf_Abstract. Diese Klasse stellt alle benötigten
Methoden bereit. Dazu wird eine abstrakte Methode 'getPdf()' bereitgestellt, 
welche dann von den ableitenden Klassen zum Rendern genutzt wird. Die Abstrakte
Klasse kümmert sich auch um das Management, wie die Texte auf der Seite gerendert
werden soll und fügt, wenn nötig, selbständig neue Seiten ein, falls dies nötig
ist. Die Klasse an sich kann eine Schrift setzten '_setFont*', eine neue Zeile 
anhand der Schriftgräße und des Textpaddings erstellen '_newLine(..)'. Um eine
neue Seite zu erstellen, wird die Methode 'newPage(...)' benutzt. Diese Methode
erstellt auch wenn gewollt einen Tabellen Header für die Produktauflistung, dazu
rendert sie auch den Footer 'insertAddressFooter(..)', setzt eine Seitenzahl und
fügt die Falz und Lockmarken ein. 
Die 'insertAddressFooter(..)' nutzt die interne '_insertAddressFooterItem(..)' 
Methode um die Values und Keys richtig dazustellen. Dazu liest sie auch ggf. die 
Daten aus dem Imprint Modul aus.
Das Logo wird mittels 'insertLogo(..)' eingefügt, diese Methode berücksichtigt
die im Backend eingestellte Position und render ggf. das Logo auch klein.
'_insertOrderInfo(..)' wird Verwendet um die Order Informationen, wie z.b.
OrderId oder Shipping Method auszugeben. Diese Methode berücksichtige alle
Einstellungen die im Backend gemacht wurden sind. Intern verwendet sie die 
Methode '_insertOrderInfoRow(..)' um die einzelnen Informationen mit korrektem
Abstand darzustellen. Sie gewährleistet, das der Text nicht in einander kollidiert.
Mit '_insertBillingAddress' wird die Zahlungsadresse eingefügt, dazu fügt sie
auch die Absenderadresse über den Empfänger hinzu.
Mit 'setSubject(..)' wird ein Titel für die Seite festgelegt. 
Die 'insertOrder(..)' Methode ist die Zentrale Methode um die Order info und
die Regungsadresse einzufügen.
'insertTableHeader(..)' wird wie schon erwähnt bei 'newPage(..)' verwendet.
Diese Methode zeichnet den Tabellen Kopf.
Mit 'insertTableRow(..)' wird eine Tabellen Zeile eingefügt. Dazu wird ein 
Symmetrics_InvoicePdf_Model_Pdf_Items_Abstract Objekt übergeben.
Mittels dieser Informationen kann diese Methode genau errechnen, wie hoch
die Zeile wird und bricht sie ggf. auf eine neue Seite um.
Diese Abstrakte Klasse wird weiter unten besprochen.
Mit '_drawItem(..)' werden die einzelnen Items gerendert, welche in der 
config.xml registriert sind. Die zugehörige abstrakte Klasse wird weiter 
unten besprochen.
Um die Totals darzustellen wird 'insertTotals()' verwendet. Diese
Methode ähnlet '_drawItem(..)' und verwendet die gleiche Grundfunktionalität.
Um Items zu rendert (Produkte, Totals, bzw. alles mögliche), wird die Klasse
'Symmetrics_InvoicePdf_Model_Pdf_Items_Abstract' verwendet.
Diese hat mehrere setter und getter Methode, die je nach Situation verwendet
werden. Die wichtigsten Funktionen für die Darstellung sind die 'draw()',
'calculateHeight()' und 'addRow(..)' Methoden.
'draw()' ist eine Abstrakte Methode und wird von den einzelnen Items 
implementiert. Mit 'calculateHeigth()' wird die größe berechnet, diese
Methode wird dann in 'insertTableRow(..)' von Symmetrics_InvoicePdf_Model_Pdf_Abstract
aufgerufen. Um aber erstmal ein Item abzulegen, wird die 'addRow(..)' Methode 
verwendet. Diese Akzeptiert als Parameter nur eine Instanz der Klasse
'Symmetrics_InvoicePdf_Model_Pdf_Items_Item'.
Diese Klasse leitet sich von keiner anderen Klasse ab und dient als genereller
Item Container. Sie hat fast die gleiche Funktionalität wie 
'Symmetrics_InvoicePdf_Model_Pdf_Items_Abstract' nur das sie Wertepaare
speichert, welche als einzelne Spalte interpretiert werden.
eine solche Spalte wird mit 'addColumn(..)' in den Container gepackt. 
Diese klasse hat auch die Methode 'calculateHeigth()' welche von der
gleichnamigen Methode in der Klasse 'Symmetrics_InvoicePdf_Model_Pdf_Items_Abstract'
aufgerufen wird. Nur ist hier der fall, dass die Höhe tatsächlich anhand
der Schrift und des Textes berechnet wird.
Um nun am ende aus diesem Klassenkonstrukt eine Rechnung dazustellen, wird die 
Klasse 'Symmetrics_InvoicePdf_Model_Pdf_Invoice' verwendet. Diese hat nur 
eine 'getPdf()' Methode, welche überschrieben ist und 4 weitere Methode, 
die zur Anzeige für zusätzliche Informationen dienen. Wobei eine die
Methode '_insertOrderInfo()' überschiebt, um die InvoiceId auszugeben.
In den restlichen Methode wird das gleiche Renderer Prinzip, wie z.b. bei
den Produkten oder Totals verwendet.
Diese Klasse wird in den überschriebenen Action-Controller
Symmetrics_InvoicePdf_Adminhtml_Sales_InvoiceController (Rechnungsübersicht -
screenshot_rechnungsuebersicht.png) und
Symmetrics_InvoicePdf_Adminhtml_Sales_Order_InvoiceController (Rechnunsdetails -
screenshot_rechnungsdetails.png) verwendet um die Rechnung(en) als Download
anzubieten.

** PROBLEMS
Rechnungen werden nach manueller Generierung nicht automatisch verschickt

* TESTCASES
** BASIC
*** A: Prüfen Sie, ob die Rechnung wie eine normale deutsche Rechnung aussieht
		Sie finden 2 Beispiele im [examples] Ordner
*** B: Prüfen Sie, ob die Felder auf der Rechnung angezeigt werden, wenn diese 
        eineschaltet sind.
*** C: Gehen Sie im Backend unter Verkäufe->Verkäufe und stellen Sie eine 
        Adresse ein. Prüfen Sie, ob diese im Footer erscheint, wenn dieser 
        aktiviert ist. Nun installieren Sie das Impressum Modul und tragen Sie 
        die Daten in der System Konfiguration ein. Prüfen Sie, ob die Daten so auf
        der Rechnung auftauchen.
*** D: Füllen Sie die 5 Felder für die Infoboxen und Felder aus und prüfen Sie,
		ob sich die Rechnung entsprechend verändert.
*** E: Ändern Sie die Optionen und prüfen Sie, ob die Zahlungsmethode bzw. Versandmethode
		auf der Rechnung angezeigt werden.
*** F: Laden Sie ein Logo hoch und prüfen Sie, ob es auf der Rechnung erscheint.