----------------------------------------------------
Installation
----------------------------------------------------

1. Ordner Symmetrics/InvoicePdf nach 
app/code/local oder app/code/community kopieren.

2. Datei app/etc/modules/Symmetrics_InvoicePdf.xml
nach app/etc/modules kopieren

3. Cache loeschen

4. Backend aufrufen

5. Fertig!

----------------------------------------------------
Beschreibung
----------------------------------------------------

Das Modul Symmetrics_InvoicePdf veraendert die Standard
PDF-Vorlage fuer Rechnungen. 

Features:

- Modifiziert die PDF-Vorlage fuer Rechnungen so, dass
die Rechnungen rechtlich konform und wie in Deutschland
ueblich aussehen.

- Zeigt im Footer der Rechnung alle Betreiberinformationen
wenn das Modul Symmetrics_Impressum installiert ist.

----------------------------------------------------
Funktonalitaet und Besonderheiten
----------------------------------------------------

Symmetrics_InvoicePdf ersetzt die Standard-Vorlage 
fuer PDF-Rechnungen und fuegt neue Informationen in
die Rechnung hinzu.

Neue Felder:

- Kundennummer-Praefix (Dafuer haben wir im Backend ein Modul geschaffen, welches das Setzen des Prefixes frei erlaubt, je nach Anforderungen)
- Faelligkeit der Rechnung
- Notiz

Diese Daten koennen im Backend unter 
"Configuration -> Sales -> PDF-Print-outs" 
geaendert werden.

Das Modul benutzt ein Logo, welches im Backend unter
Configuration -> Sales -> Sales -> Invoice and Packing
Slip Design" geaendert werden kann.
 
Wenn das Modul Symmetrics_Impressum installiert ist
werden im Footer alle Betreiberinformationen angezeigt:

- Vollstaendige Anschrift
- Kommunikationsdaten wie Telefon, Fax, E-Mail usw.
- Vollstaendige Kontoverbindung
- Vollstaendige Steuerinformationen