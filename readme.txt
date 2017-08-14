=== Webling ===
Contributors: usystemsgmbh
Donate link: https://www.webling.eu
Tags: webling, vereinssoftware, vereinsverwaltung, verein, mitglieder, anmeldung, formular, anmeldeformular
Requires at least: 3.6
Tested up to: 4.8
Stable tag: 3.2.1
License: Aapache License
License URI: http://www.apache.org/licenses/LICENSE-2.0.html

Anmeldeformulare und Mitgliederdaten aus der Vereinssoftware webling.eu auf deiner Webseite anzeigen.

== Description ==

Zeige Mitgliederdaten aus Webling auf deiner Webseite an oder erstelle ein Anmeldeformular, welches dir automatisch Mitglieder in deinem Webling erstellt.

= Mitgliederlisten =

Zeige eine Mitgliederliste mit Daten aus der Vereinssoftware Webling auf deiner Webseite an. Es können entweder alle Mitglieder angezeigt werden, oder nach bestimmten Gruppen gefiltert.

= Anmeldeformuare =

Erstelle ein Anmeldeformular, über welches sich Mitglieder anmelden können. Es wird automatisch ein Mitglied mit den angegebene Daten in Webling erstellt. Die Formulare lassen sich so konfigurieren, dass nur gewünschte Felder angezeigt werden.

= Webling =

Webling ist eine praktische Vereinsverwaltungssoftware. Du benötigst mindestens ein <a href="https://www.webling.eu/angebote.php">Webling Plus</a> oder höher um dieses Plugin zu nutzen. Die benötigte API ist nur für diese Abos verfügbar ist. Das Plugin kann nicht ohne Webling benutzt werden.

= Support =

Bei Fragen zum Plugin wenden sie sich bitte an support@webling.ch

== Installation ==

Der Webling API-Key muss in den Einstellungen in der WordPress Administration hinterlegt werden. ("Webling" > "Einstellungen").

== Upgrade Notice ==

= 3.0 =
Das Shortcode Format hat sich geändert. Shortcodes wurden (wo möglich) automatisch konvertiert.
Das Format [webling_memberlist groups="123,567"] wird nicht mehr unterstützt (mit dem "groups" Attribut).

== Frequently Asked Questions ==

n/a

== Screenshots ==

1. Mitgliederliste auf einer Seite
2. Konfiguration einer Mitgliederliste
3. Mitgliederlisten
4. Anmeldeformular auf einer Seite
5. Konfiguration der Felder eines Anmeldeformulares
6. Einstellungen eines Anmeldeformulares
7. Plugin Einstellungen im Admin Bereich

== Changelog ==

= 3.2.1 =
* Bugfix: caching issue

= 3.2.0 =
* Added the possibility to use a custom HTML design for memberlists
* Bugfix: Dates before 1970 were not showing in memberlist
* Display version info in settings page

= 3.1.2 =
* Bugfix: Fixes for MySQL 5.5 compatibility

= 3.1.1 =
* Bugfix: Adding new forms did not work

= 3.1.0 =
* Added an option to send confirmation emails to visitors when submitting a form

= 3.0.3 =
* Fixing a problem with the order of form fields

= 3.0.2 =
* Small bugfixes

= 3.0.1 =
* Bugfix: Not all shortcodes were converted during upgrade

= 3.0 =
* Complete rewrite
* New: Forms - Add forms which automaticly add new members to your webling database
* Shortcode for forms added: [webling_form id="2"]
* Updated shortcode format: [webling_memberlist groups="123,567"] is no longer supported. New format is: [webling_memberlist id="1"], existing shortcode will be converted during upgrade.
* Webling data is beeing cached
* Totally new admin interface, fields and groups can be configured individually for each list
* Admin menu entry for Webling

= 2.0 =
* Official release on the WordPress plugin directory

= 1.1 =
* Groups Attribute added to shortcode, you can now use [webling_memberlist groups="123,567"] to filter by group ids
* Settings link on Plugin Page

= 1.0 =
* Initial Release
