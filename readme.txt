=== PS Blog-Vorlagen ===
Contributors: DerN3rd (PSOURCE)
Tags: multisite, blogvorlagen, classicpress, wordpress, psource
Requires at least: 5.0
Tested up to: 7.1.4
Stable tag: 1.0.0
Requires PHP: 8.1
Requires ClassicPress: 2.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Ermöglicht dem Webseiten-Administrator das Erstellen neuer Blogs basierend auf Vorlagen, um den Blog-Erstellungsprozess zu beschleunigen.

== Description ==

Mit Neue Blog-Vorlagen kannst Du einen Katalog mit Webseiten-Vorlagen erstellen, um die Produktivität erheblich zu verbessern.
Verwende das Design und den Inhalt vorhandener Webseiten in Deinem Multisite-Netzwerk, um unglaublich schnell neue Webseiten zu erstellen.

= Ein besserer Start =

Stelle neue Webseiten so ein, dass Beiträge, Seiten, Benutzer, Dateien, Kategorien, Menüs und sogar Plugins geerbt werden. 
Dies beschleunigt die Erstellung von Webseiten für Netzwerkadministratoren und neue Mitglieder. 
Gib neuen Benutzern beim Start einen Schub, indem Du sie eine Vorlage auswählen lässt, die ihren Anforderungen entspricht.

= Neue Blog-Vorlage kann verwendet werden als: =

Die Standardvorlage für alle neuen Webseiten, die in Deinem Netzwerk erstellt wurden.
Eine Vorlagenauswahl, wenn sich Personen für eine neue Webseite in Deinem Netzwerk anmelden.
Eine Vorlagenauswahl, wenn ein Superadministrator eine neue Webseite unter Sites> Add New im Netzwerkadministrator-Dashboard erstellt.
So funktioniert die neue Blog-Vorlage: Du erstellst zunächst eine neue Webseite und passt sie mit allen Funktionen an, die automatisch zu neuen Webseiten hinzugefügt werden sollen, die mit dieser Vorlage erstellt wurden. 
Du konfigurierst beispielsweise alle Einstellungen, fügst alle Beiträge, Seiten hinzu, richtest das Theme ein, lädtst den benutzerdefinierten Bildheader hoch, aktivierst Plugins, fügst Benutzer hinzu, fügst Widgets hinzu, fügst Links zur Blogroll hinzu und so weiter.
Bitte beachte:

Die Webseite, die Du für die Verwendung als Vorlage angepasst hast, dient ausschließlich als Vorlage und nicht als funktionierende Webseite.
Änderungen an einem Vorlagenblog wirken sich auf Webseiten aus, die nach den neuen Änderungen erstellt wurden. Sie wirken sich nicht auf bereits erstellte Webseiten aus.
Du kannst Deine Hauptseite nicht als Vorlagenblog verwenden! Du erhältst eine kleine Erinnerung, wenn Du es versuchst.

[POWERED BY PSOURCE](https://psource.eimen.net)

[Projektseite](https://cp-psource.github.io/blogvorlagen/)
[GitHub](https://github.com/piestingtal-source/blogtemplates)


== Entwicklung und Tests ==

Der Kopier-Integrationstest läuft ausschließlich per WP-CLI in einer lokalen Multisite-Installation mit WP_ENVIRONMENT_TYPE=local:

`wp --skip-plugins --skip-themes --user=<Netzwerkadministrator> eval-file wp-content/plugins/blogvorlagen/tests/copy-integration.php`

Er erstellt zwei eigene Test-Websites und eine Testvorlage und entfernt diese anschließend auch bei Testfehlern. Bestehende Websites werden nicht als Kopierquelle oder Kopierziel verwendet. Geprüft werden unter anderem Beiträge, ausgewählte Seiten, serialisierte Einstellungen, Menüs, Kommentar-Metadaten, Mediendateien und Fehler beim Dateikopieren.

Laufzeittests wurden mit ClassicPress 2.7.4 und PHP 8.5.12 durchgeführt. Die Angabe „Tested up to“ ersetzt keinen separaten Laufzeittest auf WordPress. Datenbanktransaktionen machen die Dateikopie und zusätzliche Tabellen mit DDL-Operationen nicht atomar.

== ChangeLog ==

= 1.0.0 =

* Reset release version and update platform requirements.
* Serialisierte Einstellungen, Options-Caches, Kommentar-Metadaten und Medien-URLs beim Kopieren korrigiert.
* Dateikopierfehler werden gemeldet; der Blog-Kontext wird bei Ausnahmen wiederhergestellt.
* Seitensuche mit vorbereiteten SQL-Abfragen, Eingabeprüfung und sichtbaren Fehlermeldungen; verspätete Antworten werden verworfen.

