# RRZE Statistics

## Diagramme mit Observable Plot

Die Monatsdiagramme werden lokal mit Observable Plot gerendert. Alle Bibliotheken werden mit dem Plugin ausgeliefert; es werden keine Diagrammdaten an einen Exportdienst übertragen. Die Lizenztexte der Produktionsabhängigkeiten stehen in [THIRD-PARTY-LICENSES.txt](THIRD-PARTY-LICENSES.txt). Die Lizenzangaben des eigenen Plugins werden durch diese Migration nicht geändert.

Die Darstellung vergleicht das aktuelle Kalenderjahr der WordPress-Zeitzone mit den beiden Vorjahren. Linien, überlagerte Flächen, gruppierte Säulen und horizontale Balken bleiben verfügbar. Alle fünf Kennzahlen und beliebte Seiten/Dateien stehen als Tabs in einem gemeinsamen Dashboard-Widget bereit. Die Diagrammart wird direkt im Widget gewechselt; eine separate Konfigurationsansicht entfällt. Die letzte Ansicht und Diagrammart werden pro Benutzer und Website über eine authentifizierte WordPress-Anfrage gespeichert. Änderungen innerhalb von 250 ms werden gebündelt; unveränderte Auswahlen erzeugen keine Speicheranfrage. Bei längerer Wartezeit erscheint ein dezenter Indikator mit zugänglicher Statusmeldung; die bestätigte Erfolgsmeldung verschwindet nach 1,5 Sekunden. Bestehende Widget-Einstellungen dienen als Startwerte; die bisherigen Sammelansichten starten bei Besuchen. `visits` wird als **Besuche** bezeichnet und ist keine Zusicherung eindeutig gezählter Personen.

Die Tab-Leiste ist mit Pfeil links/rechts sowie Pos1/Ende bedienbar. Bereits besuchte Ansichten behalten Monatsauswahl, Jahresfilter und den geöffneten Tabellenzustand bis zum Neuladen. Die bisherige Widget-ID für Besuche bleibt erhalten, damit dessen Position und Sichtbarkeit bestehen bleiben.

Über die Jahresauswahl und den Monatsregler lassen sich einzelne Werte per Tastatur erkunden. Jahresreihen können über die Legende ein- und ausgeblendet werden. Die aufklappbare Datentabelle enthält dieselben Werte wie das Diagramm. Fehlende Werte werden als fehlend ausgegeben; echte Nullen bleiben erhalten.

CSV lässt sich kopieren oder herunterladen; SVG und PNG enthalten Titel, Zeitraum, Einheit, Legende und Quelle. Exporte berücksichtigen die sichtbaren Jahresreihen. CSV verwendet Kommas und unveränderte Zahlenwerte, bei fehlenden Werten leere Felder. Die Datei enthält einen UTF-8-BOM für Tabellenprogramme; kopierter Text nicht. SVG ist 1.200 Pixel breit, PNG 2.400 Pixel. PDF und XLSX sind nicht Bestandteil dieser Version.

### Entwicklung und Prüfung

Mit einer zu den Abhängigkeiten kompatiblen Node-Version (hier geprüft mit Node 26) ausführen:

```sh
npm ci
npm run build
npm test
php tests/php/chart-integration.php
npx playwright install chromium firefox webkit
npm run test:browser
```

Der Produktionsbuild aktualisiert die Lizenzhinweise und ersetzt die alten Assets in `dist/`. Die Browserprüfungen starten einen isolierten Testserver auf `127.0.0.1:8765`; sie ändern keine WordPress-Daten. JavaScript-Tests prüfen Datenlücken, Jahreswechsel, Gruppierung, CSV und Ressourcenfreigabe. Browsertests ergänzen Tastaturbedienung, Tabellen, Bildexporte, schmale Ansichten und automatische Accessibility-Prüfungen.

Vor einer produktiven Freigabe zusätzlich im tatsächlichen WordPress-Dashboard mit VoiceOver/Safari prüfen: sinnvolle Namen und Ansagen der Jahresauswahl und des Monatsreglers, sichtbarer Fokus, Tabellennavigation und verständliche Exportmeldungen. Automatische Prüfungen ersetzen diese manuelle Screenreader-Abnahme nicht.

### Vorgemerkter Folgeausbau: Kalender-Heatmap

Geplant ist eine zusätzliche Besuche-Ansicht nach Art einer GitHub-Aktivitätsübersicht: Wochen als Spalten, Wochentage als Zeilen, Monatsbeschriftungen und fünf Intensitätsstufen für die vergangenen zwölf Monate. Voraussetzung sind echte Tagesdaten mit geklärter Zeitzone und Kennzahl. Die derzeit verwendete `webalizer.hist` liefert Monatswerte; daraus werden keine Tageswerte abgeleitet. Nullwerte, fehlende Daten und zukünftige Tage müssen unterscheidbar bleiben. Zugängliche Tagesauswahl, Tabelle und Exporte gehören zum späteren Ausbau.

## Hintergrund

RRZE statistics is a WordPress plugin that creates one tabbed dashboard widget displaying the latest statistics off statistiken.rrze.fau.de.

This plugin only works within our university cms service, because the site must be listed on statistiken.rrze.fau.de

On dashboard access, expired caches are refreshed from the upstream statistics service. Monthly statistics are cached for six hours; popular pages and files for twelve hours.

Activate the plugin and use the tabs and chart-type selector directly in the dashboard widget. Statistics appear once the upstream service supplies data for the site.
Deactivation resets legacy site-wide widget settings and removes legacy options; personal view preferences remain available after reactivation. Cached transients retain their existing expiration times.

_________
A hint for developers. If WP_Debug = true, RRZE statistics will fetch a selected dataset from statistiken.rrze.fau.de to enable usage on test systems.

### Acknowledgments
Special thanks @rvdforst for spending a few hours, teaching me quite a few lessons and tricks about webpack, l18n, wp_cronjobs, and PHP oop fundamentals.
And other special thanks to my colleagues @rrze-webteam for their great advice and input during the development of the first version.

## RRZE Statistik (Dt. Fassung)
RRZE Statistik ist ein WordPress Plugin, welches ein Dashboard-Widget mit mehreren Datenansichten generiert. Die Daten für die Widgets werden durch die Webseite statistiken.rrze.fau.de bereitgestellt.

Das Plugin funktioniert nur für Kunden des universitären CMS der Friedrich-Alexander Universität Erlangen-Nürnberg, die auch auf statistiken.rrze.fau.de gelistet werden.

Beim Dashboard-Aufruf werden abgelaufene Caches aus der Statistikquelle erneuert. Monatsstatistiken werden sechs Stunden zwischengespeichert, die Listen beliebter Seiten und Dateien zwölf Stunden.

Kennzahlen und beliebte Seiten lassen sich über die Tabs auswählen; die Darstellungsart wird direkt im Widget geändert. Statistiken erscheinen, sobald die Datenquelle Werte für den Webauftritt bereitstellt. Beim Deaktivieren werden die bisherigen websiteweiten Widget-Einstellungen zurückgesetzt; persönliche Ansichtspräferenzen bleiben für eine erneute Aktivierung erhalten.

_______
Ein Hinweis für Entwickler. Wenn WP_DEBUG = true, wird ein Beispieldatensatz aus statistiken.rrze.fau.de geladen. Dies ermöglicht die Nutzung in Testumgebungen.

### Danksagung
Am Schluss möchte ich noch @rvdforst dafür danken, dass er die ein oder andere Stunde investiert hat, um mir Tipps zu webpack, l18n, wp_cronjobs und PHP oop zu geben.

Aber auch meinen anderen Kollegen @rrze-webteam, die immer wieder Feedback, Ideen und Tipps mit eingebracht haben.
