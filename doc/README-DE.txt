* DOCUMENTATION

** INSTALLATION
Extrahieren Sie den Inhalt dieses Archivs in Ihr Magento Verzeichnis. Die Verzeichnisstruktur ist bereits auf die des Magentoverzeichnisses angepasst.
Auch die benötigte Konfigurationsdatei um das Modul zu aktivieren ist bereits in diesem Archiv enthalten.
Ggf. ist das Leeren/Auffrischen des Magento-Caches notwendig.
Setzen Sie dann die Einstellungen in der System/Konfiguration.

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
*** C: Wenn das Modul Symmetrics_Impressum installiert ist
	werden im Footer alle Betreiberinformationen angezeigt:
		- Vollständige Anschrift
		- Kommunikationsdaten wie Telefon, Fax, E-Mail usw.
		- Vollständige Kontoverbindung
		- Vollständige Steuerinformationen
*** D: Neu: Rechnungen können per Option in der Sytemkonfiguration
	automatisch erzeugt und verschickt werden, wenn eine Bestellung
	eingeht.
*** E: Neu: Es kann eine Infobox und ein Infotext auf der Rechnung
	angezeigt werden. Konfiguration wie üblich in der System
	Konfiguration.
*** F: Neu: Es werden Versandmethode und Zahlungsmethode optional angezeigt.
*** G: Das Modul benutzt ein Logo, welches im Backend unter
	Configuration -> Sales -> Sales -> Invoice and Packing
	Slip Design" geändert werden kann.
*** H: Wenn attach invoice installiert ist, werden die automatisch generierten
        Rechnungen auch an die Benachrichtigungs-Emails angehangen.

** TECHNINCAL

** PROBLEMS
Rechnungen werden nach manueller Generierung nicht automatisch verschickt

* TESTCASES
** BASIC
*** A: Prüfen Sie, ob die Rechnung wie eine normale deutsche Rechnung aussieht
		Sie finden 2 Beispiele im [examples] Ordner
*** B: Prüfen Sie, ob die Felder auf der Rechnung angezeigt werden
*** C: Installieren Sie das Impressum Modul und tragen Sie die Daten in der
		System Konfiguration ein. Prüfen Sie, ob die Daten so auf der Rechnung
		auftauchen.
*** D: Geben Sie 4 Bestellungen auf und ändern Sie nach jeder Bestellung die beiden
		Felder "Automatisch erzeugen" und "Automatisch verschicken", so dass alle 
		Kombinationen getestet werden.
*** E: Füllen Sie die 5 Felder für die Infoboxen und Felder aus und prüfen Sie,
		ob sich die Rechnung entsprechend verändert.
*** F: Ändern Sie die Optionen und prüfen Sie, ob die Zahlungsmethode bzw. Versandmethode
		auf der Rechnung angezeigt werden.
*** G: Laden Sie ein Logo hoch und prüfen Sie, ob es auf der Rechnung erscheint.

** CATCHABLE

** STRESS