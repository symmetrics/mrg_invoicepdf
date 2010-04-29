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
Die technische Information muss noch ergäntzt werden.
Dies folgt in kürze.

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