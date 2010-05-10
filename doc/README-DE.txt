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
*** A:  Modifiziert die PDF-Vorlage für Rechnungen so, dass
	    die Rechnungen rechtlich konform und wie in Deutschland
	    üblich aussehen. 
*** B:  Folgende Felder wurden hinzugefügt: (befinden sich unter
	    "Konfiguration  =>  Verkäufe  =>  PDF Ausdrucke" ):
        - Kundennummer-Präfix (Dafür haben wir im Backend ein Modul
        - geschaffen, welches das Setzen des Prefixes frei erlaubt,
        - je nach Anforderungen)
        - Fälligkeit der Rechnung
        - Bemerkung
        - Kundennummer Prefix
        - Zeige Kunden-IP in Rechnung
        - Zeige Footer
        - Zeige Versandart
        - Zeige Zahlmethode
        - Zeige Infotext
        - Infotext
        - Zeige Infobox
        - Infobox Überschrift
        - Infobox
*** C:  Wenn das Modul Symmetrics_Imprint installiert ist, werden 
        die Daten für den Footer aus den Konfigurationsfeldern dieses 
        Moduls ausgelesen und damit alle Betreiberinformationen angezeigt:
		- Vollständige Anschrift
		- Kommunikationsdaten wie Telefon, Fax, E-Mail usw.
		- Vollständige Kontoverbindung
		- Vollständige Steuerinformationen
*** D:  Ist das Modul Symmetrics_Imprint nicht installiert, wird geprüft 
        ob das Modul Symmetrics_Impressum installiert ist. Wenn ja, werden die 
        Daten aus den Konfigurationsfeldern dieses Moduls geholt.
*** E:  Es kann eine Infobox und ein Infotext auf der Rechnung
	    angezeigt werden. Konfiguration ist wie üblich in der System
	    Konfiguration.
*** F:  Es werden Versandmethode und Zahlungsmethode optional angezeigt.
*** G:  Das Modul fügt unter "Konfiguration  =>  Verkäufe  =>  Verkäufe  =>  
        Rechnungs- und Lieferscheingestaltung" das Feld "Logoposition" 
        hinzu. Das Logo das man dort hochladen kann, wird vom Modul verwendet 
        und an der eingestellten Position in der Rechung dargestellt.

** TECHNICAL
Um die PDF darzustellen wird die Abstrakte Klasse 
Symmetrics_InvoicePdf_Model_Pdf_Abstract (Datei: app/code/community/
Symmetrics/InvoicePdf/Model/Pdf/Abstract.php) genutzt.
Diese Klasse stellt alle benötigten Methoden bereit. 
Dazu wird eine abstrakte Methode 'getPdf()' bereitgestellt, 
welche dann von den ableitenden Klassen zum Rendern genutzt wird. Die Abstrakte
Klasse kümmert sich auch um das Management, wie die Texte auf der Seite gerendert
werden sollen und fügt, wenn nötig, selbständig neue Seiten ein.

Die Klasse an sich kann eine Schriftart setzten '_setFont*', und eine neue Zeile 
anhand der Schriftgröße und des Textpaddings erstellen '_newLine(..)'. 
Um eine neue Seite zu erstellen, wird die Methode 'newPage(...)' benutzt. 
Diese Methode erstellt auch, wenn gewollt, einen Tabellen Header für die Produktauflistung, 
dazu rendert sie auch den Footer 'insertAddressFooter(..)', setzt eine Seitenzahl 
und fügt die Falz und Lockmarken ein.

Die Methode 'insertAddressFooter(..)' nutzt die interne '_insertAddressFooterItem(..)' 
- Methode um die values und keys richtig dazustellen.
Die Daten werden ggf. aus dem Modul Symmetrics_Imprint oder aus Symmetrics_Impressum 
ausgelesen (siehe FUNCTIONALITY C und D).

Das Logo wird mittels 'insertLogo(..)' eingefügt, diese Methode berücksichtigt
die im Backend eingestellte Position und rendert ggf. das Logo auch kleiner.

Die Methode '_insertOrderInfo(..)' wird verwendet um die Order Informationen, wie z.b.
die OrderId oder die Versandmethode auszugeben. Diese Methode berücksichtige alle
Einstellungen die im Backend gemacht worden sind. 
Intern verwendet sie die Methode '_insertOrderInfoRow(..)' um die einzelnen 
Informationen mit korrektem Abstand darzustellen. 
Sie gewährleistet, das der Text nicht ineinander kollidiert.
Mit der Methode '_insertBillingAddress' wird die Zahlungsadresse eingefügt, 
zusätzlich fügt sie die Absenderadresse über dem Empfänger hinzu.
Die Methode 'setSubject(..)' legt einen Titel für die Seite fest.
Die 'insertOrder(..)' Methode ist die zentrale Methode um die Order Info und
die Rechnungsadresse einzufügen.
Die 'insertTableHeader(..)' - Methode wird, wie schon erwähnt, 
bei 'newPage(..)' verwendet. Diese Methode zeichnet den Tabellenkopf.

Mit der Methode 'insertTableRow(..)' wird eine Tabellenzeile eingefügt. 
Dazu wird ein Symmetrics_InvoicePdf_Model_Pdf_Items_Abstract Objekt übergeben.
Mittels dieser Informationen kann diese Methode genau errechnen, wie hoch
die Zeile wird und bricht sie ggf. auf eine neue Seite um.
Diese Abstrakte Klasse wird weiter unten beschrieben.

Mit der '_drawItem(..)' - Methode werden die einzelnen Items gerendert, 
welche in der config.xml registriert sind. Die zugehörige abstrakte 
Klasse wird weiter unten beschrieben.

Um die Totals darzustellen wird die Methode 'insertTotals()' verwendet. Diese
Methode ähnelt '_drawItem(..)' und verwendet die gleiche Grundfunktionalität.
Um Items zu rendern (Produkte, Totals, bzw. alles mögliche), wird die Klasse
'Symmetrics_InvoicePdf_Model_Pdf_Items_Abstract' verwendet.
Diese hat mehrere setter und getter Methoden, die je nach Situation verwendet
werden. Die wichtigsten Funktionen für die Darstellung sind die 'draw()',
'calculateHeight()' und 'addRow(..)' Methoden.
'draw()' ist eine Abstrakte Methode und wird von den einzelnen Items 
implementiert. Mit 'calculateHeigth()' wird die Größe berechnet, diese
Methode wird dann in 'insertTableRow(..)' von Symmetrics_InvoicePdf_Model_Pdf_Abstract
aufgerufen. 
Um aber erstmal ein Item abzulegen, wird die 'addRow(..)' Methode 
verwendet. Diese akzeptiert als Parameter nur eine Instanz der Klasse
'Symmetrics_InvoicePdf_Model_Pdf_Items_Item'.
Diese Klasse leitet sich von keiner anderen Klasse ab und dient als genereller
Item Container. Sie hat fast die gleiche Funktionalität wie 
'Symmetrics_InvoicePdf_Model_Pdf_Items_Abstract' nur das sie Wertepaare
speichert, welche als einzelne Spalte interpretiert werden.
Eine solche Spalte wird mit 'addColumn(..)' in den Container gepackt. 
Diese Klasse beinhaltet auch die Methode 'calculateHeigth()' welche von der
gleichnamigen Methode in der Klasse 'Symmetrics_InvoicePdf_Model_Pdf_Items_Abstract'
aufgerufen wird. Nur wird hier die Höhe tatsächlich anhand der Schrift 
und des Textes berechnet.

Um nun aus diesem Klassenkonstrukt eine Rechnung dazustellen, wird die 
Klasse 'Symmetrics_InvoicePdf_Model_Pdf_Invoice' verwendet. Diese beinhaltet nur 
eine 'getPdf()' Methode, welche überschrieben ist und 4 weitere Methoden, 
die zur Anzeige von zusätzlichen Informationen dienen. Wobei eine davon die
Methode '_insertOrderInfo()' überschreibt, um die InvoiceId auszugeben.
In den restlichen Methoden wird das gleiche Renderer - Prinzip, wie z.b. bei
den Produkten oder Totals verwendet.
Diese Klasse wird in den überschriebenen Action-Controllern
Symmetrics_InvoicePdf_Adminhtml_Sales_InvoiceController (Für Rechnungsübersicht, 
siehe doc/screenshots/screenshot_rechnungsuebersicht.png) und
Symmetrics_InvoicePdf_Adminhtml_Sales_Order_InvoiceController (Für Rechnungsdetails,
siehe doc/screenshots/screenshot_rechnungsdetails.png) verwendet um die Rechnung(en) als Download
anzubieten.

** PROBLEMS
Rechnungen werden nach manueller Generierung nicht automatisch verschickt.

* TESTCASES
** BASIC
*** A:  Prüfen Sie, ob die Rechnung wie eine normale deutsche Rechnung aussieht.
		Sie finden 2 Beispiele im doc/examples Ordner.
*** B:  Prüfen Sie, ob die verschiedenen Felder auf der Rechnung angezeigt werden, wenn diese 
        aktiviert sind.
*** C:  Gehen Sie im Backend unter 
        "Verkäufe => Verkäufe => Rechnungs- und Lieferscheingestaltung => Adresse" 
        und tragen Sie eine Adresse ein. Prüfen Sie, ob diese im Footer erscheint, 
        wenn dieser aktiviert ist (Einstellung: "Zeige Footer"). 
        Nun installieren Sie das Symmetrics_Impressum Modul und tragen Sie die Daten 
        in den entsprechenden Feldern des Moduls in der Systemkonfiguration 
        ein "Allgemein => Impressum". Prüfen Sie, ob die 
        Daten korrekt auf der Rechnung erscheinen, also alle ausgefüllten Felder 
        übernommen werden und nicht mehr die Daten aus dem Feld
        "Verkäufe => Verkäufe => Rechnungs- und Lieferscheingestaltung => Adresse".
*** D:  Deinstallieren Sie das Modul Symmetrics_Impressum und installieren Sie 
        Symmetrics_Imprint. Füllen Sie die Felder des Moduls in der Konfiguration 
        "Allgemein => Imprint" aus und prüfen Sie ob nun die Daten aus diesen Feldern
        im Footer erscheinen. Prüfen Sie auch ob, wenn beide 
        Module installiert sind, die Daten aus Symmetrics_Imprint genommen werden und nicht 
        aus Symmetrics_Impressum.
        Beachten Sie auch, das Felder die mit dem Tag <hide_in_invoice_pdf>
        in der system.xml des Impressum - oder Imprint - Moduls gekennzeichnet sind, 
        ignoriert und nicht in den Footer übernommen werden.
*** E:  Füllen Sie die 5 Felder für die Infoboxen und Felder aus und prüfen Sie,
	    ob sich die Rechnung entsprechend verändert.
*** F:  Aktivieren / deaktiveren Sie die Einstellungen "Zeige Versandart" und 
        "Zeige Zahlmethode" und prüfen Sie, ob die Versandart bzw. Zahlmethode auf 
        der Rechnung korrekt angezeigt bzw. nicht angezeigt werden.
*** G:  Prüfen Sie, ob das Feld Logoposition vorhanden ist und man zwischen den 
        Optionen "Links, mittig, Rechts" wählen kann. Laden Sie ein Logo hoch und 
        prüfen Sie, ob es auf der Rechnung erscheint. Prüfen Sie auch ob sich die 
        Position des Logos korrekt verändert wenn sie die Einstellung "Logoposition" 
        entsprechend verändern.