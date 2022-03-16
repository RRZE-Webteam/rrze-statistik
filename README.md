# RRZE Statistics
RRZE statistics is a WordPress plugin that creates a row of dashboard widgets displaying the latest statistics off statistiken.rrze.fau.de.

This plugin only works within our university cms service, because the site must be listed on statistiken.rrze.fau.de

Data is updated once every hour for hits / visitors / kbytes / hosts / files and once every week for files and sites with the highest hit count.

This plugin provides no additional settings. Activate the plugin for usage. It might take up to a week until data can be displayed within the dashboard.
On deactivation, the plugin also deletes all stored datasets inside the database.

_________
A hint for developers. If WP_Debug = true, RRZE statistics will fetch a selected dataset from statistiken.rrze.fau.de to enable usage on test systems.

### Acknowledgments
Special thanks @rvdforst for spending a few hours, teaching me quite a few lessons and tricks about webpack, l18n, wp_cronjobs, and PHP oop fundamentals.
And other special thanks to my colleagues @rrze-webteam for their great advice and input during the development of the first version.

## RRZE Statistik (Dt. Fassung)
RRZE Statistik ist ein WordPress Plugin, welches eine Reihe von Dashboard-Widgets generiert. Die Daten für die Widgets werden durch die Webseite statistiken.rrze.fau.de bereitgestellt.

Das Plugin funktioniert nur für Kunden des universitären CMS der Friedrich-Alexander Universität Erlangen-Nürnberg, die auch auf statistiken.rrze.fau.de gelistet werden.

Vorhandene Daten werden stündlich für Hits / Besucher / kBytes / Hosts und Dateien abgerufen. Die Datensätze für die Seiten und Mediendateien mit den meisten Hits werden nur einmal pro Woche abgerufen.

Dieses Plugin bietet keine Konfigurationsmöglichkeiten. Bei Aktivierung kann es bis zu 7 Tage dauern, bevor alle Datensätze korrekt auf dem Dashboard dargestellt werden. Sobald das Plugin deaktiviert wird, werden auch die zugehörigen Datensätze aus der WP Mediathek gelöscht.

_______
Ein Hinweis für Entwickler. Wenn WP_DEBUG = true, wird ein Beispieldatensatz aus statistiken.rrze.fau.de geladen. Dies ermöglicht die Nutzung in Testumgebungen.

### Danksagung
Am Schluss möchte ich noch @rvdforst dafür danken, dass er die ein oder andere Stunde investiert hat, um mir Tipps zu webpack, l18n, wp_cronjobs und PHP oop zu geben.

Aber auch meinen anderen Kollegen @rrze-webteam, die immer wieder Feedback, Ideen und Tipps mit eingebracht haben.