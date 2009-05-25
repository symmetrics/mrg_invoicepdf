----------------------------------------------------
Installation
----------------------------------------------------

1. Ordner Symmetrics/InvoicePdf nach 
app/code/local oder app/code/community kopieren.

2. Datei app/etc/modules/Symmetrics_InvoicePdf.xml
nach app/etc/modules kopieren

3. Cache löschen

4. Backend aufrufen

5. Fertig!

----------------------------------------------------
Beschreibung
----------------------------------------------------

Das Modul Symmetrics_InvoicePdf verändert die Standard
PDF-Vorlage für Rechnungen. 

Features:

- Modifiziert die PDF-Vorlage für Rechnungen so, dass
die Rechnungen rechtlich konform und wie in Deutschland
üblich aussehen.

- Zeigt im Footer der Rechnung alle Betreiberinformationen
wenn das Modul Symmetrics_Impressum installiert ist.

----------------------------------------------------
Funktonalität und Besonderheiten
----------------------------------------------------

Symmetrics_InvoicePdf ersetzt die Standard-Vorlage 
für PDF-Rechnungen und fügt neue Informationen in
die Rechnung hinzu.

Neue Felder:

- Kundennummer-Präfix
- Fälligkeit der Rechnung
- Notiz

Diese Daten können im Backend unter 
"Configuration -> Sales -> PDF-Print-outs" 
geändert werden.

Das Modul benutzt ein Logo, welches im Backend unter
Configuration -> Sales -> Sales -> Invoice and Packing
Slip Design" geändert werden kann.
 
Wenn das Modul Symmetrics_Impressum installiert ist
werden im Footer alle Betreiberinformationen angezeigt:

- Vollständige Anschrift
- Kommunikationsdaten wie Telefon, Fax, E-Mail usw.
- Vollständige Kontoverbindung
- Vollständige Steuerinformationen