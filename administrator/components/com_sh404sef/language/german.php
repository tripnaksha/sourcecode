<?php
//
// Copyright (C) 2004 W.H.Welch
// All rights reserved.
//
// This source file is part of the 404SEF Component, a Mambo 4.5.1
// custom Component By W.H.Welch - http://sef404.sourceforge.net/
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// Please note that the GPL states that any headers in files and
// Copyright notices as well as credits in headers, source files
// and output (screens, prints, etc.) can not be removed.
// You can extend them with your own credits, though...
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//
// The "GNU General Public License" (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html.
//
// German Translation by M. Stenzel - mastergizmo@arcor.de and Matrikular - coicvc@web.de
//
// {shSourceVersionTag: Version x - 2007-09-20}

/**
 * 2008.02.23 mic [ http://www.joomx.com ]
 * UTF-8 
 */
 
// Dont allow direct linking
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

define('_COM_SEF_404PAGE',							'404 Seite');
define('_COM_SEF_ADD',								'Hinzufügen');
define('_COM_SEF_ADDFILE',							'Standard Indexdatei');
define('_COM_SEF_ASC',								' (aufsteigend) ');
define('_COM_SEF_BACK',								'Zurück zur sh404SEF Konfigurationsübersicht');
define('_COM_SEF_BADURL',							'Die alte Nicht-SEF Url muss mit index.php beginnen');
define('_COM_SEF_CHK_PERMS',						'Bitte die Dateiberechtigungen überprüfen und sicherstellen, dass auf die Datei zugegriffen werden kann.');
define('_COM_SEF_CONFIG',							'Konfiguration');
define('_COM_SEF_CONFIG_DESC',						'Alle sh404SEF-Einstellungen konfigurieren');
define('_COM_SEF_CONFIG_UPDATED',					'Änderungen erfolgreich gespeichert');
define('_COM_SEF_CONFIRM_ERASE_CACHE',				'Soll der URL-Cache geleert werden? Das ist nach Konfigurationsänderungen dringend empfohlen. Um den Cache wieder aufzubauen, die Webseite nochmals aufrufen, oder besser eine Sitemap verwenden');
define('_COM_SEF_COPYRIGHT',						'Copyright');
define('_COM_SEF_DATEADD',							'Datum hinzugefügt');
define('_COM_SEF_DEBUG_DATA_DUMP',					'DEBUG DATA DUMP COMPLETE: Laden der Seite abgebrochen');
define('_COM_SEF_DEF_404_MSG',						'<h1>404: Nicht gefunden</h1><h4>Die angeforderte Seite konnte leider nicht gefunden werden.</h4>');
define('_COM_SEF_DEF_404_PAGE',						'Standard 404 Seite');
define('_COM_SEF_DESC',								' (absteigend) ');
define('_COM_SEF_DISABLED',							'Hinweis: Die SEF Unterstützung in diesem CMS ist momentan deaktiviert. Um SEF zu benutzen, muss in der <a href="' . $GLOBALS['shConfigLiveSite'] .'/administrator/index.php?option=com_config">Systemsteuerung</a> unter dem TAB SEO die SEF-Urls aktiviert werden' );
define('_COM_SEF_EDIT',								'Bearbeiten');
define('_COM_SEF_EMPTYURL',							'Es muss eine URL für die Umleitung angeben werden');
define('_COM_SEF_ENABLED',							'Aktiviert');
define('_COM_SEF_ERROR_IMPORT',						'Fehler während des Imports:');
define('_COM_SEF_EXPORT',							'Exportieren');
define('_COM_SEF_EXPORT_FAILED',					'EXPORT FEHLGESCHLAGEN!!!');
define('_COM_SEF_FATAL_ERROR_HEADERS',				'SCHWERER FEHLER: Header wurde bereits gesendet');
define('_COM_SEF_FRIENDTRIM_CHAR',					'Zeichen am Anfang oder Ende entfernen');
define('_COM_SEF_HELP',								'sh404SEF<br/>Hilfe');
define('_COM_SEF_HELPDESC',							'Hilfe für sh404SEF benötigt?');
define('_COM_SEF_HELPVIA',							'<strong>In den folgenden Foren ist Hilfe zu finden:</strong>');
define('_COM_SEF_HIDE_CAT',							'Kategorie verbergen');
define('_COM_SEF_HITS',								'Zugriffe');
define('_COM_SEF_IMPORT',							'Importieren');
define('_COM_SEF_IMPORT_EXPORT',					'URL Import / Export');
define('_COM_SEF_IMPORT_OK',						'Individuelle URLs wurden erfolgreich importiert');
define('_COM_SEF_INFO',								'sh404SEF<br/>Dokumentation');
define('_COM_SEF_INFODESC',							's404SEF Projekt Zusammenfassung und Dokumentation');
define('_COM_SEF_INSTALLED_VERS',					'Versionnummer');
define('_COM_SEF_INVALID_SQL',						'FALSCHE DATEN IN SQL-Datei:');
define('_COM_SEF_INVALID_URL',						'FALSCHE URL: dieser Link benötigt eine valide Itemid, aber es wurde keine gefunden.<br/>Lösung: Einen Menüeintrag für diesen Artikel erstellen, er braucht jedoch nicht veröffentlicht werden, es genügt dass der Eintrag existiert.');
define('_COM_SEF_LICENSE',							'Lizenz');
define('_COM_SEF_LOWER',							'Nur Kleinbuchstaben');
define('_COM_SEF_MAMBERS',							'Mambers Forum');
define('_COM_SEF_NEWURL',							'Neue Url');
define('_COM_SEF_NO_UNLINK',						'Kann die Datei aus dem Medienverzeichnis nicht entfernen');
define('_COM_SEF_NOACCESS',							'Kein Zugriff möglich');
define('_COM_SEF_NOCACHE',							'Kein Cache möglich');
define('_COM_SEF_NOLEADSLASH',						'Hier sollte kein vorangehender "SLASH" an der neuen SEF URL sein');
define('_COM_SEF_NOREAD',							'SCHWERER FEHLER: Datei kann nicht gelesen werden' );
define('_COM_SEF_NORECORDS',						'Keine Einträge gefunden.');
define('_COM_SEF_OFFICIAL',							'Offizielles Projektforum');
define('_COM_SEF_OK',								' OK ');
define('_COM_SEF_OLDURL',							'Alte SEF URL');
define('_COM_SEF_PAGEREP_CHAR',						'Trennzeichen');
define('_COM_SEF_PAGETEXT',							'Seitentext');
define('_COM_SEF_PROCEED',							' Vorgang Starten ');
define('_COM_SEF_PURGE404',							'404 Logs<br />löschen');
define('_COM_SEF_PURGE404DESC',						'Löscht vorhandene 404 Logdateien');
define('_COM_SEF_PURGECUSTOM',						'Eigene Umleitungen<br/>löschen');
define('_COM_SEF_PURGECUSTOMDESC',					'Löscht vorhandene, eigene Umleitungen');
define('_COM_SEF_PURGEURL',							'SEF Urls<br/>löschen');
define('_COM_SEF_PURGEURLDESC',						'Löscht alle vorhanden SEF Urls');
define('_COM_SEF_REALURL',							'Wirkliche Url');
define('_COM_SEF_RECORD',							' Eintrag');
define('_COM_SEF_RECORDS',							' Einträge');
define('_COM_SEF_REPLACE_CHAR',						'Zu ersetzendes Zeichen');
define('_COM_SEF_SAVEAS',							'als Eigene Umleitung speichern');
define('_COM_SEF_SEFURL',							'SEF Url');
define('_COM_SEF_SELECT_DELETE',					'Es wurde nicht zum Löschen ausgewählt');
define('_COM_SEF_SELECT_FILE',						'Es muss vorher eine Datei ausgewählt werden');
define('_COM_SEF_SH_ACTIVATE_IJOOMLA_MAG',			'iJoomla Magazin im Inhalt aktivieren');
define('_COM_SEF_SH_ADV_INSERT_ISO',				'ISO Code wählen');
define('_COM_SEF_SH_ADV_MANAGE_URL',				'URL Verarbeitung');
define('_COM_SEF_SH_ADV_TRANSLATE_URL', 			'Übersetze URL');
define('_COM_SEF_SH_ALWAYS_INSERT_ITEMID',			'Itemid an SEF URL anhängen');
define('_COM_SEF_SH_ALWAYS_INSERT_ITEMID_PREFIX',	'Menü ID');
define('_COM_SEF_SH_ALWAYS_INSERT_MENU_TITLE',		'Menü Überschrift immer einfügen');
define('_COM_SEF_SH_CACHE_TITLE',					'Cache Verwaltung');
define('_COM_SEF_SH_CAT_TABLE_SUFFIX',				'Tabellenvorzeichen');
define('_COM_SEF_SH_CB_INSERT_NAME',				'Community Builder Namen einfügen');
define('_COM_SEF_SH_CB_INSERT_USER_ID', 			'Benutzer ID voranstellen');
define('_COM_SEF_SH_CB_INSERT_USER_NAME',			'Benutzernamen einfügen');
define('_COM_SEF_SH_CB_NAME',						'Standardname der CB Komponente');
define('_COM_SEF_SH_CB_TITLE',						'Community Builder Konfiguration');
define('_COM_SEF_SH_CB_USE_USER_PSEUDO',			'Pseudonym angeben');
define('_COM_SEF_SH_CONF_TAB_ADVANCED',				'Erweitert');
define('_COM_SEF_SH_CONF_TAB_BY_COMPONENT',			'Komponenten');
define('_COM_SEF_SH_CONF_TAB_MAIN',					'Allgemein');
define('_COM_SEF_SH_CONF_TAB_PLUGINS',				'Plugins');
define('_COM_SEF_SH_DEFAULT_MENU_ITEM_NAME',		'Standard Menüüberschrift');
define('_COM_SEF_SH_DO_NOT_INSERT_LANGUAGE_CODE',	'Keinen Code einfügen');
define('_COM_SEF_SH_DO_NOT_OVERRIDE_SEF_EXT',		'sef_ext nicht überschreiben');
define('_COM_SEF_SH_DO_NOT_TRANSLATE_URL',			'Nicht übersetzen');
define('_COM_SEF_SH_ENCODE_URL',					'URL chiffrieren');
define('_COM_SEF_SH_FB_INSERT_CATEGORY_ID',			'Kategorie ID angeben');
define('_COM_SEF_SH_FB_INSERT_CATEGORY_NAME',		'Kategorienname einfügen');
define('_COM_SEF_SH_FB_INSERT_MESSAGE_ID',			'Nachrichten ID einfügen');
define('_COM_SEF_SH_FB_INSERT_MESSAGE_SUBJECT',		'Nachrichtenbetreff einfügen');
define('_COM_SEF_SH_FB_INSERT_NAME',				'Fireboardname einfügen');
define('_COM_SEF_SH_FB_NAME',						'Standard Fireboardname');
define('_COM_SEF_SH_FB_TITLE',						'Fireboard Konfiguration ');
define('_COM_SEF_SH_FILTER',						'Filter');
define('_COM_SEF_SH_FORCE_NON_SEF_HTTPS',			'Kein SEF wenn HTTPS');
define('_COM_SEF_SH_GUESS_HOMEPAGE_ITEMID',			'Homepage ID verwenden');
define('_COM_SEF_SH_IJOOMLA_MAG_NAME',				'Standard iJoomla Magazinname');
define('_COM_SEF_SH_IJOOMLA_MAG_TITLE',				'iJoomla Magazin Konfiguration');
define('_COM_SEF_SH_INSERT_GLOBAL_ITEMID_IF_NONE',	'Einfügen der Menü-Itemid');
define('_COM_SEF_SH_INSERT_IJOOMLA_MAG_ARTICLE_ID', 'Artikel ID in URL einfügen');
define('_COM_SEF_SH_INSERT_IJOOMLA_MAG_ISSUE_ID',	'Ausgabe ID in URL einfügen');
define('_COM_SEF_SH_INSERT_IJOOMLA_MAG_MAGAZINE_ID', 'Magazin ID in URL einfügen');
define('_COM_SEF_SH_INSERT_IJOOMLA_MAG_NAME',		'Magazin Name in URL einfügen');
define('_COM_SEF_SH_INSERT_LANGUAGE_CODE',			'Sprachencode in URL einfügen');
define('_COM_SEF_SH_INSERT_NUMERICAL_ID',			'Numerische ID in URL einfügen');
define('_COM_SEF_SH_INSERT_NUMERICAL_ID_ALL_CAT',	'Alle Kategorien');
define('_COM_SEF_SH_INSERT_NUMERICAL_ID_CAT_LIST',	'Gilt für welche Kagetorie');
define('_COM_SEF_SH_INSERT_NUMERICAL_ID_TITLE',		'Einmalige ID');
define('_COM_SEF_SH_INSERT_PRODUCT_ID',				'Produkt ID verwenden');
define('_COM_SEF_SH_INSERT_TITLE_IF_NO_ITEMID',		'Menütitel bei fehlender Itemid');
define('_COM_SEF_SH_ITEMID_TITLE',					'Itemid Verwaltung');
define('_COM_SEF_SH_LETTERMAN_DEFAULT_ITEMID',		'Standard Itemid für Letterman');
define('_COM_SEF_SH_LETTERMAN_TITLE',				'Letterman Konfiguration ');
define('_COM_SEF_SH_LIVE_SECURE_SITE',				'SSL gesicherte URL');
define('_COM_SEF_SH_LOG_404_ERRORS',				'404 Fehlermeldungen aufzeichnen');
define('_COM_SEF_SH_MAX_URL_IN_CACHE',				'Cache Größe');
define('_COM_SEF_SH_OVERRIDE_SEF_EXT',				'sef_ext Datei überschreiben');
define('_COM_SEF_SH_REDIR_404',						'404');
define('_COM_SEF_SH_REDIR_CUSTOM',					'Individuell');
define('_COM_SEF_SH_REDIR_SEF',						'SEF');
define('_COM_SEF_SH_REDIR_TOTAL',					'Total');
define('_COM_SEF_SH_REDIRECT_JOOMLA_SEF_TO_SEF',	'301 Redirect von CMS SEF nach sh404SEF');
define('_COM_SEF_SH_REDIRECT_NON_SEF_TO_SEF',		'301 Redirect von Nicht-SEF zu SEF');
define('_COM_SEF_SH_REPLACEMENTS',					'Liste der zu ersetzenden Zeichen');
define('_COM_SEF_SH_SHOP_NAME',						'Standard Shopname');
define('_COM_SEF_SH_TRANSLATE_URL',					'URL Übersetzen');
define('_COM_SEF_SH_TRANSLATION_TITLE',				'Übersetzungsverwaltung');
define('_COM_SEF_SH_USE_URL_CACHE',					'URL Cache aktivieren');
define('_COM_SEF_SH_VM_ADDITIONAL_TEXT',			'Zusätzlicher Text');
define('_COM_SEF_SH_VM_DO_NOT_SHOW_CATEGORIES',		'Keine');
define('_COM_SEF_SH_VM_INSERT_CATEGORIES',			'Kategorien einfügen');
define('_COM_SEF_SH_VM_INSERT_CATEGORY_ID',			'Kategorie ID in URL einfügen');
define('_COM_SEF_SH_VM_INSERT_FLYPAGE',				'Flypagenamen einfügen');
define('_COM_SEF_SH_VM_INSERT_MANUFACTURER_ID',		'Hersteller ID einfügen');
define('_COM_SEF_SH_VM_INSERT_MANUFACTURER_NAME',	'Hersteller Namen einfügen');
define('_COM_SEF_SH_VM_INSERT_SHOP_NAME',			'Shop Namen in URL einfügen');
define('_COM_SEF_SH_VM_SHOW_ALL_CATEGORIES',		'Unterkategorien');
define('_COM_SEF_SH_VM_SHOW_LAST_CATEGORY',			'Nur letzte Kategorie anzeigen');
define('_COM_SEF_SH_VM_TITLE',						'Virtuemart Konfiguration');
define('_COM_SEF_SH_VM_USE_PRODUCT_SKU',			'Art. Nr. als Namen verwenden');
define('_COM_SEF_SHOW_CAT',							'Kategorie anzeigen');
define('_COM_SEF_SHOW_SECT',						'Bereich anzeigen');
define('_COM_SEF_SHOW0',							'Zeige SEF Urls');
define('_COM_SEF_SHOW1',							'Zeige 404 Logs');
define('_COM_SEF_SHOW2',							'Zeige Eigene Umleitungen');
define('_COM_SEF_SKIP',								'Überspringen');
define('_COM_SEF_SORTBY',							'Sortieren nach:');
define('_COM_SEF_STRANGE',							'Etwas seltsames ist passiert. Das sollte nicht vorkommen<br />');
define('_COM_SEF_STRIP_CHAR',						'Auszublendende Zeichen');
define('_COM_SEF_SUCCESSPURGE',						'Einträge erfolgreich gelöscht');
define('_COM_SEF_SUFFIX',							'Dateiendung');
define('_COM_SEF_SUPPORT',							'Support<br/>Homepage');
define('_COM_SEF_SUPPORT_404SEF',					'sh404 unterstützen');
define('_COM_SEF_SUPPORTDESC',						'Zur 404SEF Homepage (neues Fenster) verbinden');
define('_COM_SEF_TITLE_ADV',						'Erweiterte Konfiguration');
define('_COM_SEF_TITLE_BASIC',						'Standard Konfiguration');
define('_COM_SEF_TITLE_CONFIG',						'404 SEF Konfiguration');
define('_COM_SEF_TITLE_MANAGER',					'SEF URL Verwaltung');
define('_COM_SEF_TITLE_PURGE',						'404 SEF Databank löschen');
define('_COM_SEF_TITLE_SUPPORT',					'sh404SEF Hilfe');
define('_COM_SEF_TT_404PAGE',						'Statische Inhaltsseite welche beim Fehler: <strong>404 Seite nicht gefunden</strong> angezeigt wird<br />Der Status, veröffentlicht oder nicht, wird nicht berücksichtigt');
define('_COM_SEF_TT_ADDFILE',						'Dateiname der an eine leere URL angehängt wird wenn keine Datei existiert.<br />Nützlich wenn Bots die Seiten nach einer bestimmten Datei durchsuchen und beim Nichtfinden eine 404 Fehlermeldung zurückgeben würden.');
define('_COM_SEF_TT_ADV',							'<strong>Standard Bearbeitung</strong><br />Die Seite wird normal abgearbeitet.<br/>Falls eine erweiterte Extension vorhanden ist, wird diese benutzt.<br /><strong>Keine Zwischenspeicherung</strong><br/>Es erfolgt keine Zwischenspeicherung in der Datenbank. Das Standard CMS SEF System wird benutzt.<br/><strong>Überspringen</strong><br/>Keine SEF Urls für diese Komponente<br/>');
define('_COM_SEF_TT_ADV4',							'Erweiterte Optionen für ');
define('_COM_SEF_TT_ENABLED',						'Ist diese Optoin auf Nein gesetzt, wird die Standard CMS SEF Funktion benutzt.');
define('_COM_SEF_TT_FRIENDTRIM_CHAR',				'Zeichen welche am Anfang oder Ende einer URL entfernt werden sollen, sind hier durch ein | getrennt anzugeben.');
define('_COM_SEF_TT_LOWER',							'Konvertiert alle Zeichen in der URL zu Kleinbuchstaben.');
define('_COM_SEF_TT_NEWURL',						'Diese URL muss mit index.php beginnen');
define('_COM_SEF_TT_OLDURL',						'Nur Relative Umleitung vom CMS Rootverzeichnis <i>ohne</i> vorangehenden SLASH');
define('_COM_SEF_TT_PAGEREP_CHAR',					'Trennzeichen Vorgabe welche die Seitenzahlen vom Rest der URL trennt.');
define('_COM_SEF_TT_PAGETEXT',						'Text welcher bei mehrseitigen Dokumenten an die URL angehängt wird.<br />Die Seitennummer wird duch %s dargestellt.');
define('_COM_SEF_TT_REPLACE_CHAR',					'Vorgabe um unbekannte Zeichen und Symbole in der URL zu ersetzen.');
define('_COM_SEF_TT_SH_ACTIVATE_IJOOMLA_MAG',		'Wenn <strong>Ja</strong> wird der ed Parameter, insofern dieser der com_content Komponente übergeben wird, als iJoomla Magazin Edition ID interpretiert.');
define('_COM_SEF_TT_SH_ADV_INSERT_ISO',				'Für jede installierte Komponente und wenn JoomFish aktiviert ist, soll der ISO-Code in die SEF-Url eingefügt werden. Zum Beispiel: www.meineseite.com/<strong>de</strong>/links.html. de steht für Deutsch - dieser Code wird nicht in der Standard-URL angezeigt.');
define('_COM_SEF_TT_SH_ADV_MANAGE_URL',				'Für jede installierte Komponente:<br /><b>verwende Standard</b><br/>verarbeite normal, ist eine SEF-Advanced-Extension vorhanden, verwende diese<br/><b>Kein Cache</b><br/>Keine Speicherung in der Datenbank und verwende bisherigen SEF-Url-Aufbau<br/><b>Nein</b><br/>Keine SEFE-URLs für diese Komponente<br/>');
define('_COM_SEF_TT_SH_ADV_OVERRIDE_SEF',			'Einige Komponenten haben eigene sef_ext Dateien zur Verwendung durch sh404Sef oder OpenSef. Ist dieser Parameter auf AN (überschreiben der sef_ext), wird diese Erweiterung nicht verwendet, stattdessen das sh404SEF eigene Plugin. Andernfalls wird die Komponenten-SEF-Erweiterung verwendet.');
define('_COM_SEF_TT_SH_ADV_TRANSLATE_URL',			'Soll für jede installierte Komponnete die URL übersetzt werden? (Keine Auswirkung wenn nur eine Sprache verwendet wird)');
define('_COM_SEF_TT_SH_ALWAYS_INSERT_ITEMID',		'Diese Option aktivieren, wenn die Nicht SEF Itemid (oder die aktuelle ID des Menüpunktes wenn keine Itemid in dem nicht SEF URL gesetzt wurde) dem SEF URL vorangestellt werden soll. Dieses sollte anstelle des -Immer Menütitel einfügen- Paramters verwendet werden falls mehrere gleichnamige Menüpunkte existieren!');
define('_COM_SEF_TT_SH_ALWAYS_INSERT_MENU_TITLE',	'Wenn aktiv, wird der Titel des Menüpunktes welcher zu der Itemid in der Nicht SEF URL gehört (oder der aktuelle Menüpunkt Titel wenn keine Itemid gesetzt ist), in die SEF URL eingefügt.');
define('_COM_SEF_TT_SH_CB_INSERT_NAME',				'Wenn <strong>Ja</strong> wird jedem SEF Link der Community Builder Komponente dessen Community Builder Menü-Element Titel vorangestellt.');
define('_COM_SEF_TT_SH_CB_INSERT_USER_ID',			'Sollten Benutzer mit gleichem Namen existieren, kann hiermit eingestellt werden, dass dem Namen die dazugehörige ID vorangestellt wird.');
define('_COM_SEF_TT_SH_CB_INSERT_USER_NAME',		'Diese Option kann bei großer Benutzeranzahl zu hoher Last der Datenbank führen.<br />Sie bewirkt, dass der Name in den SEF URL aufgenommen wird. Ist diese Option deaktiviert, wird das reguläre ID Format benutzt.<br /><strong>Beispiel:</strong><br />..../send-user-email.html?user=245');
define('_COM_SEF_TT_SH_CB_NAME',					'Wurde die vorherige Option aktiviert, kann hier der Text angegeben werden, welcher den Standardnamen in der SEF URL überschreibt. Eine spätere Änderung und Übersetzung sind nicht möglich.');
define('_COM_SEF_TT_SH_CB_USE_USER_PSEUDO',			'Wenn auf <strong>Ja</strong> gesetzt, wird der Benutzerpseudoname in die SEF-URL inkludiert, ansonsten der wirkliche Benutzername');
define('_COM_SEF_TT_SH_DEFAULT_MENU_ITEM_NAME',		'Wurde die vorherige Option auf <strong>Ja</strong> gesetzt, kann  hier den Text der in den SEF URL eingefügt wird, überschreiben.<br />Hinweis: Dieser Text kann nicht geändert werden und wird nicht übersetzt.');
define('_COM_SEF_TT_SH_ENCODE_URL',					'Wenn aktiviert, die URL wird verschlüsselt um Kompatibel mit Sprachen welche Sonderzeichen haben, zu sein. Die URL kann z.B. dann so aussehen: mysite.com/%34%56%E8%67%12.....');
define('_COM_SEF_TT_SH_FB_INSERT_CATEGORY_ID',		'Wenn <strong>Ja</strong>, wird die Kategorie-ID zum Namen hinzugefügt (nützlich wenn es 2 Kategorien mit gleichem Namen gibt)');
define('_COM_SEF_TT_SH_FB_INSERT_CATEGORY_NAME',	'Wenn aktiviert, wird der Kategorienamen zu allen SEF-Links hinzugefügt');
define('_COM_SEF_TT_SH_FB_INSERT_MESSAGE_ID',		'Wenn auf <strong>Ja</strong>, wird jede Nachrichten-ID zum Betreff hinzugefügt (Nützlich wenn es 2 Nachrichten mit selben Betreff gibt)');
define('_COM_SEF_TT_SH_FB_INSERT_MESSAGE_SUBJECT',	'Wenn auf <strong>Ja</strong>, wird jeder Nachrichtenbetreff in eine SEF-URL konvertiert');
define('_COM_SEF_TT_SH_FB_INSERT_NAME',				'Wenn aktiviert, wird der Fireboardtitle zu allen Fireboardurls vorangestellt');
define('_COM_SEF_TT_SH_FB_NAME',					'Wenn aktiviert, wird der Fireboardname allen Fireboardurls vorangestellt');
define('_COM_SEF_TT_SH_FORCE_NON_SEF_HTTPS',		'Wenn aktiviert, werden im SSL-Modus (https) alle SEF-URLs wieder normal dargestellt (nützlich auf gesharten SSL-Servern bei Problemen)');
define('_COM_SEF_TT_SH_GUESS_HOMEPAGE_ITEMID',		'Wenn aktiviert, werden auf der Startseite ItemIDs der Inhalte durch einen sh404-Vorschlag ersetzt. Nützlich dann, wenn Inhalte auf verschiedenen Seiten angesehen werden können');
define('_COM_SEF_TT_SH_IJOOMLA_MAG_NAME', 			'Wurde der vorherige Parameter aktiviert, enthät die SEF-URL den hier vergebenen Namen. Es ist nicht möglich diesen Eintrag nachträglich zu ändern, es erfolgt keine Übersetzung.');
define('_COM_SEF_TT_SH_INSERT_GLOBAL_ITEMID_IF_NONE',	'Wenn in einer URL keine Itemid vorhanden ist bevor sie in eine SEF-Url umgewandelt wird, erhält diese die aktuelle Menü Itemid.<br />Dies stellt sicher, dass der Link, sollte er geklickt werden, auf der Seite bleibt.');
define('_COM_SEF_TT_SH_INSERT_IJOOMLA_MAG_ARTICLE_ID', 'Aktivieren dieser Option führt dazu, dass die Artikel-ID dem Artikel-Titel in der URL vorangestellt wird.<br /><strong>Beispiel:</strong> beispiel.de/Joomla-magazine/<strong>56</strong>-Good-article-title.html');
define('_COM_SEF_TT_SH_INSERT_IJOOMLA_MAG_ISSUE_ID', 'Wenn <strong>Ja</strong> wird die Interne Ausgabe-ID dem Ausgabenamen in der URL vorangestellt.');
define('_COM_SEF_TT_SH_INSERT_IJOOMLA_MAG_MAGAZINE_ID', 'Wenn <strong>Ja</strong> wird die Interne Magazin ID dem Magazinnamen in der URL vorangestellt<br /><strong>Beispiel:</strong><br />beispiel.de/<strong>4</strong>-Joomla-magazine/Good-article-title.html');
define('_COM_SEF_TT_SH_INSERT_IJOOMLA_MAG_NAME', 'Bei <strong>Ja</strong> wird immer der Name des Magazins, basierend auf dem Menütiteleintrag der SEF-URL vorangestellt.');
define('_COM_SEF_TT_SH_INSERT_LANGUAGE_CODE',		'Wenn <strong>Ja</strong>, wird der ISO-Code der Seitensprache in die SEF-URL eingefügt, ausgenommen die Sprache ist die Standardseitensprache');
define('_COM_SEF_TT_SH_INSERT_NUMERICAL_ID',		'Die Option aktivieren um so eine bessere Schnittstelle zu Diensten wie z.B. wie Google News bereitzustellen. In diese Fall wird eine numerische ID an die URL angehängt.<br />Ein Beispiel wäre:<br />2007041100000<br />wobei: <strong>20070411</strong> das Erstellungsdatum und: <strong>00000</strong><br />eine interne, eindeutige ID des Inhaltselements darstellt.<br />Da dieser Wert später nicht mehr geändert werden kann, sollte das Erstellungsdatum erst dann gesetzt werden, wenn der Beitrag bereit für die Veröffentlichung ist.');
define('_COM_SEF_TT_SH_INSERT_NUMERICAL_ID_CAT_LIST', 'Ausgehend von den hier angewählten Kategorien wird die numerische ID in die SEF-URL des jeweiligen Inhaltelements eingefügt.<br />Durch halten der Strg-Taste ist eine Mehrfachauswahl möglich.');
define('_COM_SEF_TT_SH_INSERT_PRODUCT_ID',			'Wenn <strong>Ja</strong> wird die CMS interne Produkt ID (Nicht SKU), vor dem Namen des Shops eingefügt.<br /><strong>Beispiel:</strong><br />beispiel.de/3-my-very-nice-product.html.<br />Dies ist nützlich wenn gleichnamige Artikel vertrieben und die Kategorienamen nicht anzeigt werden sollen');
define('_COM_SEF_TT_SH_INSERT_TITLE_IF_NO_ITEMID',	'Wenn in einer URL keine Itemid gesetzt wurde bevor sie in eine SEF Url umgewandelt wird, und diese Option aktiviert ist, wird der Titel des Menüeintrags in die SEF-Url eingebunden.<br />Wurde die Option:<br />Einfügen der Menü-Itemid<br />aktiviert, sollte auch diese Funktion auf <strong>Ja</strong> gesetzt werden.<br />Damit wird verhindert, dass Beispielsweise -2, -3,- ... an die URL angehängt werden wenn diese von verschiedenen Seiten angezeigt wird.');
define('_COM_SEF_TT_SH_LETTERMAN_DEFAULT_ITEMID',	'Seiten-ItemID in Letterman links (unsubscribe, confirmation messages, ...) hinzufügen' );
define('_COM_SEF_TT_SH_LIVE_SECURE_SITE',			'Werden keine SSL gesicherte Seiten benutzt, dann hier die volle Basis-URL der eigenen Webseite eintragen.<br />Wird keiner hier eingetragen, so wird: http<srong>s</strong>://beispielseite.de benutzt.<br />Die Angabe muss ohne abschließende Slashes erfolgen.<br /><strong>Beispiel:</strong><br />https://www.beispielseite.de oder https://beispielseite.de/WasAuchImma');
define('_COM_SEF_TT_SH_LOG_404_ERRORS',				'Durch das Aktivieren dieser Option werden 404 Fehler in der Datenbank gespeichert. Dies kann später dabei helfen eventuelle Fehler in den Links zu finden. Die Funktion verbraucht zusätzlichen Speicher. Sollten die Links also fehlerfrei sein, kann diese Option deaktiviert werden.');
define('_COM_SEF_TT_SH_MAX_URL_IN_CACHE',			'Wurde der URL-Cache (Zwischenspeicher) aktiviert, kann an dieser Stelle ein Maximalwert festgelegt werden. Überschreitet die Anzahl der URLs diesen Wert wird zwar fortgesetzt, allerdings werden diese nicht zwischengespeichert, was die Ladezeit der Seiten erhöht.<br />Jede gespeicherte URL benötigt ca. 200 bytes - 100 davon für die SEF-URL und 100 für Nicht-SEF-URLs.<br />Beispiel: 5000 URLs verbrauchen ca. 1 Mb Speicher.');
define('_COM_SEF_TT_SH_REDIRECT_JOOMLA_SEF_TO_SEF', 'Wenn auf <strong>Ja</strong> gesetzt, werden die CMS-Standard-SEF-URLs anstatt mit einem 301-Redirect mit dem sh404SEF-Redirect ersetzt. Wenn dieser nicht vorhanden ist, wird er automatisch erzeugt');
define('_COM_SEF_TT_SH_REDIRECT_NON_SEF_TO_SEF',	'Wenn aktiv, werden Nicht-SEF-URLs die bereits in der Datenbank gespeichert sind, zur SEF-URL weitergleitet.');
define('_COM_SEF_TT_SH_REPLACEMENTS',				'Anhand dieser Ausschluss-Tabelle lassen sich unerlaubte Zeichen oder Nicht-Lateinische Zeichensätze durch hier definierte Zeichenfolgen ersetzten.<br />Das einzuhaltende Format lautet:<br />AlterWERT TRENNZEICHEN NeuerWERT.<br />In der Praxis werden altes und neues Zeichen durch ein | getrennt und jede weitere Ausschluss-Regel durch ein Komma definiert.<br />Es können auf diese Weise viele verschiedene Regeln erstellt werden. Ebenso das Ersetzen von Mehrfach-Zeichen wie im folgenden Beispiel ist möglich: ö|oe');
define('_COM_SEF_TT_SH_SHOP_NAME',					'Es kann ein alternativer Shopnamen angeben werden welcher dann den in der Konfiguration hinterlegten Text überschreibt. Dieser Text kann weder nachträglich geändert noch übersetzt werden.');
define('_COM_SEF_TT_SH_TRANSLATE_URL',				'Wird eine mehrsprachige Webseite eingesetzt und ist diese Option aktiviert, werden SEF URL Elemente anhand der eingestellten Sprache der Besucher und den Joom!Fish-Vorgaben übersetzt.<br />Ist diese Option deaktiviert oder wird nur eine Sprache verwendet, wird die im CMS eingetragene Standardsprache verwendet');
define('_COM_SEF_TT_SH_USE_URL_CACHE',				'Bei Aktivierung dieser Option werden SEF-URLs in einen Zwischenspeicher gelegt der die Ladezeiten der Seite erheblich verkürzt. Dieser Vorgang verbraucht allerdings mehr Speicher!');
define('_COM_SEF_TT_SH_VM_ADDITIONAL_TEXT',			'Wenn <strong>Ja</strong> wird der URL der Kategorie zusätzlicher Text angehängt.<br /><strong>Beispiel:</strong><br />.../category-A/View-all-products.html statt ..../category-A/');
define('_COM_SEF_TT_SH_VM_INSERT_CATEGORIES',		'Bei <strong>Keine</strong> werden keine Kategorienamen der URL hinzugefügt.<br /><strong>Beispiel:</strong><br />beispiel.de/joomla-cms.html<br />Wird die Option <strong>Nur die Letzte anzeigen</strong> gewählt, enthält die URL den Kategorienamen des jeweiligen Produktes.<br /><strong>Beispiel:</strong><br />beispiel.de/joomla/joomla-cms.html<br /><strong>Unterkategorien</strong> bedeutet, dass der Name der Kategorie des Artikels inkl. aller Unterkategorien dem Link hinzugefügt wird.<br /><strong>Beispiel:</strong><br />beispiel.de/software/cms/joomla/joomla-cms.html');
define('_COM_SEF_TT_SH_VM_INSERT_CATEGORY_ID',		'Hier kann entschieden werden, ob zu jeder URL einer Kategorie dessen ID vorangestellt wird.<br /><strong>Beispiel:</strong><br />beispiel.de/1-software/4-cms/1-joomla/joomla-cms.html');
define('_COM_SEF_TT_SH_VM_INSERT_FLYPAGE',			'Wenn aktiviert, wird jedem Flypagenamen bei Produktdetails der Name vorangestellt. Kann deaktiviert sein wenn nur eine Flypage verwendet wird');
define('_COM_SEF_TT_SH_VM_INSERT_MANUFACTURER_ID',	'Wenn <strong>Ja</strong> wird dem Herstellernamen die dazugehörige ID vorangestellt.<br /><strong>Beispiel:</strong><br />beispiel.de/6-manufacturer-name/3-my-very-nice-product.html');
define('_COM_SEF_TT_SH_VM_INSERT_MANUFACTURER_NAME', 'Wenn <strong>Ja</strong> wird der Herstellername, sofern er existiert, der SEF URL hinzugefügt.<br /><strong>Beispiel:</strong><br />beispiel.de/manufacturer-name/product-name.html');
define('_COM_SEF_TT_SH_VM_INSERT_SHOP_NAME',		'Wenn <strong>Ja</strong> wird der Shopname basierend auf dem Titel des Menüeintrags der SEF URL vorangestellt.');
define('_COM_SEF_TT_SH_VM_USE_PRODUCT_SKU',			'Wenn <strong>Ja</strong> wird die vergebene Artikelnr. anstatt des Namens verwendet.');
define('_COM_SEF_TT_SHOW_CAT',						'Bei <strong>Ja</strong> werden die Kategorienamen in die URL aufgenommen');
define('_COM_SEF_TT_SHOW_SECT',						'Bei <strong>Ja</strong> werden die Bereichsnamen in die URL aufgenommen');
define('_COM_SEF_TT_STRIP_CHAR',					'Zeichen und Symbole die der URL entnommen werden sollen. Durch | getrennt anzugeben.');
define('_COM_SEF_TT_SUFFIX',						'Erweiterung für Dateien. Zum Deaktivieren dieses Feld leer lassen. Ein häufiger Eintrag wäre z.B. .html');
define('_COM_SEF_TT_USE_ALIAS',						'Soll anstatt des Original- der Titel-Alias verwendet werden');
define('_COM_SEF_UNWRITEABLE',						' <strong style="color:red">Nicht beschreibbar</strong>');
define('_COM_SEF_UPLOAD_OK',						'Datei erfolgreich hochgeladen');
define('_COM_SEF_URL',								'Url');
define('_COM_SEF_URLEXIST',							'Diese URL existiert bereits in der Datenbank!');
define('_COM_SEF_USE_ALIAS',						'Benutze Titelalias');
define('_COM_SEF_USE_DEFAULT',						'Standard Routine');
define('_COM_SEF_USING_DEFAULT',					' <strong style="color:red">Benutze Standardwerte</strong>');
define('_COM_SEF_VIEW404',							'404 Logs<br />ansehen / bearbeiten');
define('_COM_SEF_VIEW404DESC',						'Ansehen/Bearbeiten der 404 Logs');
define('_COM_SEF_VIEWCUSTOM',						'Eigene Umleitungen<br />(Redirects)<br />ansehen / bearbeiten');
define('_COM_SEF_VIEWCUSTOMDESC',					'Ansehen/Bearbeiten eigene Umleitungen (Redirects)');
define('_COM_SEF_VIEWMODE',							'Ansichtsmodus:');
define('_COM_SEF_VIEWURL',							'SEF-URLs<br />ansehen / bearbeiten');
define('_COM_SEF_VIEWURLDESC',						'Ansehen/Bearbeiten der SEF-URLs');
define('_COM_SEF_WARNDELETE',						'Achtung!!!<br/>Forfahren löscht: ');
define('_COM_SEF_WRITE_ERROR',						'FEHLER: Konfiguration nicht beschreibbar');
define('_COM_SEF_WRITE_FAILED',						'FEHLER: Datei kann nicht ins Mediaverzeichnis hochgeladen werden');
define('_COM_SEF_WRITEABLE',						' <strong style="color:green">beschreibbar</strong>');
define('_FULL_TITLE',								'Haupttitel');
define('_PREVIEW_CLOSE',							'Fenster Schließen');
define('_TITLE_ALIAS',								'Titel Alias');

// V 1.2.4.s
define('_COM_SEF_SH_DOCMAN_TITLE',					'Docman Konfiguration');
define('_COM_SEF_SH_DOCMAN_INSERT_NAME',			'Docmanname einfügen');
define('_COM_SEF_TT_SH_DOCMAN_INSERT_NAME',			'Wenn auf <strong>Ja</strong> wird der Elementtitel der Docmanhauptseite der DocMan-SEEF-URL vorangestellt');
define('_COM_SEF_SH_DOCMAN_NAME',					'Vorgabe Docmanname');
define('_COM_SEF_TT_SH_DOCMAN_NAME',				'Wenn voriger Parameter auf Ja, hier den Text für die SEF-URL angeben. HINWEIS: dieser Text wird nicht übersetzt');
define('_COM_SEF_SH_DOCMAN_INSERT_DOC_ID',			'Dokument-ID einfügen');
define('_COM_SEF_TT_SH_DOCMAN_INSERT_DOC_ID',		'Wenn <strong>Ja</strong>, wir die Dokument-ID dem Dokumentnamen vorangestellt (nützlich wenn der gleiche Namen mehrmals verwendet wird');
define('_COM_SEF_SH_DOCMAN_INSERT_DOC_NAME',		'Füge Dokumentname ein');
define('_COM_SEF_TT_SH_DOCMAN_INSERT_DOC_NAME',		'Wenn <strong>Ja</strong>, wird allen SEF-URLs der Dokumentname bei Aktionen  vorangestellt');
// myblog
define('_COM_SEF_SH_MYBLOG_TITLE',					'MyBlog Konfiguration');
define('_COM_SEF_SH_MYBLOG_INSERT_NAME',			'MyBlog Name einfügen');
define('_COM_SEF_TT_SH_MYBLOG_INSERT_NAME',			'Wenn <strong>Ja</strong>, wird der Elementtitel der MyBlog-Hauptseite allen MyBlog-SEF-URLs vorangestellt');
define('_COM_SEF_SH_MYBLOG_NAME',					'Vorgabe Myblog Name');
define('_COM_SEF_TT_SH_MYBLOG_NAME',				'Wenn voriger Parameter auf Ja, hier den Text für die SEF-URLs angeben. Hinweis: dieser Text wird nicht übersetzt');
define('_COM_SEF_SH_MYBLOG_INSERT_POST_ID',			'Post ID einfügen');
define('_COM_SEF_TT_SH_MYBLOG_INSERT_POST_ID',		'Wenn <strong>Ja</strong>, wird die interne Post-ID dem Titel vorangestellt (sinnvoll bei identischen Titeln)');
define('_COM_SEF_SH_MYBLOG_INSERT_TAG_ID',			'Tag ID einfügen');
define('_COM_SEF_TT_SH_MYBLOG_INSERT_TAG_ID',		'Wenn <strong>Ja</strong>, wird die intere Tag-ID dem Tag-Namen vorangestellt');
define('_COM_SEF_SH_MYBLOG_INSERT_BLOGGER_ID',		'Blogger ID einfügen');
define('_COM_SEF_TT_SH_MYBLOG_INSERT_BLOGGER_ID',	'Wenn <strong>Ja</strong>, wir die interne Blogger-ID dem Bloggernamen vorangestellt');
define('_COM_SEF_SH_RW_MODE_NORMAL',				'Mit .htaccess (mod_rewrite)');
define('_COM_SEF_SH_RW_MODE_INDEXPHP',				'Ohne .htaccess (index.php)');
define('_COM_SEF_SH_RW_MODE_INDEXPHP2',				'Ohne .htaccess (index.php?)');
define('_COM_SEF_SH_SELECT_REWRITE_MODE',			'Rewrite Modus');
define('_COM_SEF_TT_SH_SELECT_REWRITE_MODE',		'Einen Rewritemodus für sh404SEF angeben.<br /><strong>mit .htaccess (mod_rewrite)</strong><br />Standard: es muss eine richtig konfiguroierte .htacces Datei geben<br /><strong>ohne .htaccess (index.php)</strong><br /><strong>EXPERIMENTAL: </strong>Es muss keine .htaccess Datei vorhanden sein. Dieser Modus verwendet die PathInfo Funktion des Apache Servers. URLs haben ein /index.php/ bit zu Beginn. Funktioniert NICHT mit MS IIS Servern!<br /><strong>ohne .htaccess (index.php?)</strong><br /><strong>EXPERIMENTAL :</strong>Es muss keine .htaccess Datei vorhanden sein. Dieser Modues ist identisch wie voriger, ausgenommen dass /index.php?/ anstatt /index.php/ verwendet wird. Funktioniert NICHT mit MS IIS Servern!<br />');
define('_COM_SEF_SH_RECORD_DUPLICATES',				'Doppelte URL aufzeichnen');
define('_COM_SEF_TT_SH_RECORD_DUPLICATES',			'Wenn <strong>Ja</strong>, sh404SEF speichert alle doppelten Nicht-Sef-URLs. Damit kann später entschieden werden welche verwendet werden soll (siehe SEF-URL Liste)');
define('_COM_SEF_META_TITLE',						'Titeltag');
define('_COM_SEF_TT_META_TITLE',					'Hier den Text angeben welcher für die aktuelle URL im Tag <br /><strong>META Title</strong> im Seitenheader aufscheinen soll');
define('_COM_SEF_META_DESC',						'Beschreibungstag');
define('_COM_SEF_TT_META_DESC',						'Hier den Text angeben welcher für die aktuelle URL im <br /><strong>META Description</strong> Tag im Seitenheader aufscheinen soll');
define('_COM_SEF_META_KEYWORDS',					'Schlüsselwörter Tag');
define('_COM_SEF_TT_META_KEYWORDS',					'Hier den Text angeben welcher für die aktuelle URL im <br /><strong>META keywords</strong> Tag im Seitenheader aufscheinen soll. Jedes Wort oder jede Wortgruppe mit Komma trennen');
define('_COM_SEF_META_ROBOTS',						'Robots Tag');
define('_COM_SEF_TT_META_ROBOTS',					'Hier den Text angeben welcher für die aktuelle URL im <br /><strong>META Robots</strong> Tag im Seitenheader aufscheinen soll. Dieser Tag sagt den Suchmaschinen was sie zu tun haben.<hr />Übliche Parameter:<br /><strong>INDEX,FOLLOW</strong>: indiziere aktuelle Seite und folge den Links<br /><strong>INDEX,NO FOLLOW</strong>: indiziere aktuelle Seite, aber folge keinen Links<br /><strong>NO INDEX, NO FOLLOW</strong>: keine Indexierung und Folgen der Links<br />');
define('_COM_SEF_META_LANG',						'Sprachen Tag');
define('_COM_SEF_TT_META_LANG',						'Hier den Sprachencode angeben welcher im <br /><strong>META http-equiv= Content-Language</strong> Tag eingetragen werden soll');
define('_COM_SEF_SH_CONF_TAB_META',					'Meta/SEO');
define('_COM_SEF_SH_CONF_META_DOC',					'WARNUNG : Um die Titel-, Beschreibungs-, Schlüsselwörter-, Robots- und Sprachentags zu aktivieren, <strong>muss das shCustomTags Modul aktiviert sein</strong> welches automatisch installiert wurde. Die <strong>Position</strong> an welcher dieses Modul veröffentlicht wird, ist sehr wichtig! Mehr darüber im Modul<br/>sh404SEF selbst hat mehrere Plugins welche <strong>automatisch</strong> META Tags für einige Komponenten erzeugt. Diesen automatisch Generierten ist der Vorzug zugeben, ausser das Ergebnis ist nicht zufriedenstellend!!');
define('_COM_SEF_SH_REMOVE_JOOMLA_GENERATOR',		'Entferne CMS eigenen Generator.Tag');
define('_COM_SEF_TT_SH_REMOVE_JOOMLA_GENERATOR',	'Wenn <strong>Ja</strong> wird der &quot;Generator = CMS-Name&quot; Meta Tag entfernt wenn');
define('_COM_SEF_SH_PUT_H1_TAG',					'h1 Tags einfügen');
define('_COM_SEF_TT_SH_PUT_H1_TAG',					'Wenn <strong>Ja</strong> werden reguläre Inhaltstitel mit h1-Tags ersetzt. Diese Titel werden normalerweise vom CSS mit einer CSS-Klasse generiert welche mit  <strong>contentheading</strong> beginnt');
define('_COM_SEF_SH_META_MANAGEMENT_ACTIVATED',		'Aktiviere Meta Verwaltung');
define('_COM_SEF_TT_SH_META_MANAGEMENT_ACTIVATED',	'Wenn <strong>Ja</strong>, Titel, Beschreibung, Schlüsselwörter, Robots und Sprachen META Tags werden von sh404SEF (und den shCustomTags Modulen) verwaltet. Ansonsten bleiben die Originalwerte - erzeigt vom CMS und den Komponenten - unberührt');
define('_COM_SEF_TITLE_META_MANAGEMENT',			'Metatag Verwaltung');
define('_COM_SEF_META_EDIT',						'Modifiziere Tags');
define('_COM_SEF_META_ADD',							'Tags hinzufügen');
define('_COM_SEF_META_TAGS',						'META Tags');
define('_COM_SEF_META_TAGS_DESC',					'Meta Tags erstellen/bearbeiten');
define('_COM_SEF_PURGE_META_DESC',					'Meta Tags löschen');
define('_COM_SEF_PURGE_META',						'Lösche META');
define('_COM_SEF_IMPORT_EXPORT_META',				'Import/Export META');
define('_COM_SEF_NEW_META',							'Neuer META');
define('_COM_SEF_NEWURL_META',						'Nicht SEF-URL');
define('_COM_SEF_TT_NEWURL_META',					'Hier die SEF-URL angeben für welche META-Tags gesetzt werden soll. HINWEIS: muss mit <strong>index.php</strong> beginnen!');
define('_COM_SEF_BAD_META',							'Bitte Daten überprüfen: einige Angaben sind nicht gültig');
define('_COM_SEF_META_TITLE_PURGE',					'Lösche Meta Tags');
define('_COM_SEF_META_SUCCESS_PURGE',				'Meta Tags gelöscht');
define('_COM_SEF_IMPORT_META',						'Import Meta Tags');
define('_COM_SEF_EXPORT_META',						'Export Meta Tags');
define('_COM_SEF_IMPORT_META_OK',					'Meta Tags erfolgreich importiert');
define('_COM_SEF_SELECT_ONE_URL',					'Bitte nur eine (!) URL wählen');
define('_COM_SEF_MANAGE_DUPLICATES',				'URL-Verwaltung für: ');
define('_COM_SEF_MANAGE_DUPLICATES_RANK',			'Rank');
define('_COM_SEF_MANAGE_DUPLICATES_BUTTON',			'Doppelte URL');
define('_COM_SEF_MANAGE_MAKE_MAIN_URL',				'Haupt URL');
define('_COM_SEF_BAD_DUPLICATES_DATA',				'FEHLER: ungültige URL-Daten');
define('_COM_SEF_BAD_DUPLICATES_NOTHING_TO_DO',		'Diese URL ist bereits die Haupt-URL');
define('_COM_SEF_MAKE_MAIN_URL_OK',					'Vorgang erfolgreich abgeschlossen');
define('_COM_SEF_MAKE_MAIN_URL_ERROR',				'FEHLER: Vorgang fehlgeschlagen!');
define('_COM_SEF_SH_CONTENT_TITLE',					'Inhaltskonfiguration');
define('_COM_SEF_SH_INSERT_CONTENT_TABLE_NAME',		'Inhaltstabellenname einfügen');
define('_COM_SEF_TT_SH_INSERT_CONTENT_TABLE_NAME',	'Wenn <strong>Ja</strong> wird der Elementitel der Kategorie oder des Bereichs der SEF-URL vorangestellt');
define('_COM_SEF_SH_CONTENT_TABLE_NAME',			'Vorgabe verlinkter Tabellenname');
define('_COM_SEF_TT_SH_CONTENT_TABLE_NAME',			'Wenn ja, kann der automatisch eingefügte Text in der SEF-URL überschrieben werden. HINWEIS: dieser Text wird nicht übersetzt!');
define('_COM_SEF_SH_REDIRECT_WWW',					'301 redirect www/non-www');
define('_COM_SEF_TT_SH_REDIRECT_WWW',				'Wenn <strong>Ja</strong> wird sh404SEF einen 301-Redirect auf Seiten <strong>ohne</strong> www durchführen. Damit werden doppelte Seiten vermieden welche einerseits durch fehlerhafte Apacheserverkonfiguration, andererseits durch manche CMS-Editoren verursacht werden und bei manchen Suchmaschinen zu eine Rückstufung des Rankings (z.B. Google) führen kann');
define('_COM_SEF_SH_INSERT_PRODUCT_NAME',			'Produktname einfügen');
define('_COM_SEF_TT_SH_INSERT_PRODUCT_NAME',		'Fügt den Produktnamen in die URL ein');
define('_COM_SEF_SH_VM_USE_PRODUCT_SKU_124S',		'Produktcode einfügen');
define('_COM_SEF_TT_SH_VM_USE_PRODUCT_SKU_124S',	'Fügt den Produktcode (SKU in Virtuemart genannt) in die URL ein');

// V 1.2.4.t
define('_COM_SEF_SH_DOCMAN_INSERT_CAT_ID',			'Kategorie ID einfügen');
define('_COM_SEF_TT_SH_DOCMAN_INSERT_CAT_ID',		'Wenn <strong>Ja</strong> wir die KategorieID der URl vorangestellt (zur Unterscheidung wenn z.B. 2 Kategorien den selben Namen haben)');
define('_COM_SEF_SH_DOCMAN_INSERT_CATEGORIES',		'Kategorienamen einfügen');
define('_COM_SEF_TT_SH_DOCMAN_INSERT_CATEGORIES',	'Wenn <strong>None/Kein</strong> wird der Kategoriename nicht eingefügt:<br />z.B. mysite.com/joomla-cms.html<br />Wenn <strong>Nur Letzter</strong> der Kategoriename wir din die URL eingefügt: <br /> mysite.com/joomla/joomla-cms.html<br />Wenn <strong>Alle versteckten Kategorien</strong> werden die Namen aller Kategorien hinzugefügt: <br /> mysite.com/software/cms/joomla/joomla-cms.html');
define('_COM_SEF_SH_FORCED_HOMEPAGE',				'Homepage URL');
define('_COM_SEF_TT_SH_FORCED_HOMEPAGE',			'Hier kann die Startseite forciert werden. Nützlich wenn eine Landingpage (normalerweise eine index.html Seite) definiert wurde. In der Form angeben: www.meineseite.com/index.php (kein abschliessender Slash / !). Damit wird dann die CMS-Startseite angezeigt wenn auch den Homelink geklickt wird');
define('_COM_SEF_SH_INSERT_CONTENT_BLOG_NAME',		'Blog Anzeigename einfügen');
define('_COM_SEF_TT_SH_INSERT_CONTENT_BLOG_NAME',	'Wenn <strong>Ja</strong> wird der Titel des Blogs einer Kategorie oder eines Bereichs der URL vorangestellt');
define('_COM_SEF_SH_CONTENT_BLOG_NAME',				'Standard Blog Anzeigename');
define('_COM_SEF_TT_SH_CONTENT_BLOG_NAME',			'Ist voriger Parameter aktiviert, kann hier der Text für die SEF-URL angegeben werden. Hinweis: der Text wird nicht übersetzt');
// mosets tree
define('_COM_SEF_SH_MTREE_TITLE',					'Mosets Tree Konfiguration');
define('_COM_SEF_SH_MTREE_INSERT_NAME',				'MTree Name einfügen');
define('_COM_SEF_TT_SH_MTREE_INSERT_NAME',			'Wenn <strong>Ja</strong>  wird der Menütitel der Mosets Komponente vorangestellt');
define('_COM_SEF_SH_MTREE_NAME',					'Standard MTree Name');
define('_COM_SEF_SH_MTREE_INSERT_LISTING_ID',		'Listen ID einfügen');
define('_COM_SEF_TT_SH_MTREE_INSERT_LISTING_ID',	'Wenn <strong>Ja</strong> wird der SEF-URL die Listen-ID vorangestellt (Nützlich wenn es 2 gleiche Listennamen gibt');
define('_COM_SEF_SH_MTREE_PREPEND_LISTING_ID',		'ID zum Namen einfügen');
define('_COM_SEF_TT_SH_MTREE_PREPEND_LISTING_ID',	'Wenn <strong>Ja</strong> und voriger Parameter ebenfalls auf Ja, wird die ID <strong>vorangestellt</strong>, ansonsten <strong>angehängt</strong>');
define('_COM_SEF_SH_MTREE_INSERT_LISTING_NAME',		'Listename einfügen');
define('_COM_SEF_TT_SH_MTREE_INSERT_LISTING_NAME',	'Wenn <strong>Ja</strong> wird der Listenname der URLs welche eine Aktion auslösen, hinzugefügt');
// iJoomla portal
define('_COM_SEF_SH_IJOOMLA_NEWSP_TITLE',			'News Portal Konfiguration');
define('_COM_SEF_SH_INSERT_IJOOMLA_NEWSP_NAME',		'News Portal Name einfügen');
define('_COM_SEF_TT_SH_INSERT_IJOOMLA_NEWSP_NAME',	'wenn <strong>Ja</strong> wird der Menüelementtitel zur SEF-URL vorangestellt');
define('_COM_SEF_SH_IJOOMLA_NEWSP_NAME',			'Standard News Portal Name');
define('_COM_SEF_SH_INSERT_IJOOMLA_NEWSP_CAT_ID',	'Kategorie ID einfügen');
define('_COM_SEF_TT_SH_INSERT_IJOOMLA_NEWSP_CAT_ID',	'Wenn <strong>Ja</strong> wird die Kategorie-ID der URl vorangestellt (nützlich wenn es 2 gleiche Namen gibt)');
define('_COM_SEF_SH_INSERT_IJOOMLA_NEWSP_SECTION_ID',	'Bereichs ID einfügen');
define('_COM_SEF_TT_SH_INSERT_IJOOMLA_NEWSP_SECTION_ID',	'Wenn <strong>Ja</strong> wird die Bereichs-ID der URl vorangestellt (falls es 2 gleiche Bereichsnamen gibt)');
// remository
define('_COM_SEF_SH_REMO_TITLE',					'Remository Konfiguration');
define('_COM_SEF_SH_REMO_INSERT_NAME',				'Remository Name einfügen');
define('_COM_SEF_TT_SH_REMO_INSERT_NAME',			'Wenn <strong>Ja</strong> wird der Menütitel der SEF-URL vorangestellt');
define('_COM_SEF_SH_REMO_NAME',						'Vorgabe Remository Name');
// CB
define('_COM_SEF_SH_CB_SHORT_USER_URL',				'Kurzurl zum Benutzerprofil');
define('_COM_SEF_TT_SH_CB_SHORT_USER_URL',			'Wenn <strong>Ja</strong> können Benutzer mit einer Kurzurl ihre Profile aufrufen (ähnlich www.mysite.com/benutzername). Vor Aktivierung dieser Option bitte überpürfen auf allfällige Konflikte mit bereits bestehenden URLs');

define('_COM_SEF_NEW_HOME_META',					'Homepage Meta');
define('_COM_SEF_CONF_ERASE_HOME_META',				'Soll die vorhandenen Homepage Titel und Metatags gelsöcht werden?');
define('_COM_SEF_SH_UPGRADE_TITLE',					'Upgrade Konfiguration');
define('_COM_SEF_SH_UPGRADE_KEEP_URL',				'Automatische URLs sichern');
define('_COM_SEF_TT_SH_UPGRADE_KEEP_URL',			'Wenn <strong>Ja</strong> werden von sh404SEF automatisch erstellte SEF-URLs gespeichert und beim Löschen der Komponente nicht gelöscht. Somit brauchen bei einer Neuisnatllierung diese URLs nicht emhr neu erstellt werden');
define('_COM_SEF_SH_UPGRADE_KEEP_CUSTOM',			'Individuelle URLs sichern');
define('_COM_SEF_TT_SH_UPGRADE_KEEP_CUSTOM',		'WEnn <strong>Ja</strong> werden selber erstellte URLs nicht gelöscht und können bei einer Komponentenneuinstallierung weiterverwendet werden');
define('_COM_SEF_SH_UPGRADE_KEEP_META',				'Titel und Meta sichern');
define('_COM_SEF_TT_SH_UPGRADE_KEEP_META',			'Wenn <strong>Ja</strong> werden eigene Titel- und Meta.TAGS gespeichert und können bei einer Komponentenneuinstallierung weiter verwendet werden');
define('_COM_SEF_SH_UPGRADE_KEEP_MODULES',			'Modulparameter sichern');
define('_COM_SEF_TT_SH_UPGRADE_KEEP_MODULES',		'Wenn <strong>Ja</strong> werden aktuelle Modulparameter gesichert und können bei einer Komponentenneuinstallierung weiterverwendet werden');
define('_COM_SEF_IMPORT_OPEN_SEF',					'Import aus OpenSEF');
define('_COM_SEF_IMPORT_ALL',						'Importiere Alles');
define('_COM_SEF_EXPORT_ALL',						'Exportiere Alles');
define('_COM_SEF_IMPORT_EXPORT_CUSTOM',				'Import/Export eigene Umleitungen');
define('_COM_SEF_DUPLICATE_NOT_ALLOWED',			'Diese URL existiert bereits - Duplikate wind nicht erlaubt');
define('_COM_SEF_SH_INSERT_CONTENT_MULTIPAGES_TITLE',	'Mehrseitenartikel Smart Titel aktivieren');
define('_COM_SEF_TT_SH_INSERT_CONTENT_MULTIPAGES_TITLE',	'Wenn Ja verwendet sh404SEF bei Artikel über mehrere Seiten (solche mit einer Inhaltstabelle) den Seitentitel im mospagebrak Modul: {mospagebreak title=Next_Page_Title &amp; heading=Previous_Page_Title}, anstatt der Seitennummer<br />Als Beispiel:  www.mysite.com/user-documentation/<strong>Page-2</strong>.html wird ersetzt durch  www.mysite.com/user-documentation/<strong>Getting-started-with-sh404SEF</strong>.html.');

// v x
define('_COM_SEF_SH_UPGRADE_KEEP_CONFIG',			'Konfiguration sichern');
define('_COM_SEF_TT_SH_UPGRADE_KEEP_CONFIG',		'Wenn <strong>Ja</strong> wird die aktuelle Konfiguration gesichert. So kann bei einer Neuinstallierung sofort darauf zurück gegriffen werden');
define('_COM_SEF_SH_CONF_TAB_SECURITY',				'Sicherheit');
define('_COM_SEF_SH_SECURITY_TITLE',				'Sicherheitskonfiguration');
define('_COM_SEF_SH_HONEYPOT_TITLE',				'Projekt "Honey Pot" Konfiguration');
define('_COM_SEF_SH_CONF_HONEYPOT_DOC',				'Project "Honey Pot" ist eine Initiative um Webseiten vor Spamrobots zu schützen. Es stellt eine Datenbank zur Verfügung um die Besucher-IP auf bekannte Spamrobots zu überprüfen. Zugriff auf diese Datenbank erfordert einen kostenlosen Zugriffscode welcher unter der <a href="http://www.projecthoneypot.org/httpbl_configure.php" target="_blank">Projektseite</a> beantragt werden kann<br />(Es muss vor dem Zugriffscode ein kostenloser Account erstellt werden). Falls möglich ist jede Hilfe dafür willkommen, indem "Fallen" auf den Webseiten erstellt werden - damit werden Spamrobots in Zukunft leichter identifiziert');
define('_COM_SEF_SH_ACTIVATE_SECURITY',				'Aktiviere Sicherheitsfunktionen');
define('_COM_SEF_TT_SH_ACTIVATE_SECURITY',			'Wenn <strong>Ja</strong> aktiviert sh404SEF einige Checks an den angefragten URLs dieser Seite um Attacken abzuwehren');
define('_COM_SEF_SH_LOG_ATTACKS',					'Attacken mitloggen');
define('_COM_SEF_TT_SH_LOG_ATTACKS',				'Wenn aktiviert, werden identifizierte Attacken in einer Textdatei gespeichert, inklusive IP-Adresse und Seitenanforderung.<br />Pro Monat wird so eine Datei erstellt welche im Verzeichnis <root>/administrator/com_sef/logs gespeichert wird. Sie kann per FTP downgeloaded werden (oder z.B. JoomlaExplorer) um sie später anzusehen. Diese Datei kann dann in einer Tabellensoftware (z.B. MS Excel) angesehen werden');	            
define('_COM_SEF_SH_CHECK_HONEY_POT',				'Verwende Honey Pot');
define('_COM_SEF_TT_SH_CHECK_HONEY_POT',			'Wenn aktiviert wird die Besucher-IP in der HoneyPot-Datenbank gecheckt (unter Verwendung dessen HTTP:BL Service). Obwohl Gratis, muss dort ein Zugang erstellt werden!');
define('_COM_SEF_SH_HONEYPOT_KEY',					'Honey Pot Zugriffskey');
define('_COM_SEF_TT_SH_HONEYPOT_KEY',				'Wenn die Honey Pot Option aktiviert ist, muss hier der Zugriffskey (12 Zeichen) angegeben werden');	             
define('_COM_SEF_SH_HONEYPOT_ENTRANCE_TEXT',		'Alternativtext');
define('_COM_SEF_TT_SH_HONEYPOT_ENTRANCE_TEXT',		'Ist eine Besucher-IP als Spamrobot erkannt, wird der weitere Zugang gespeerrt (Fehlerseite 403).<br />Sollte der Besucher aber kein Spamrobot sein, wird ihm dieser Text hier angezeigt inklusive Link für weiteren Seitenzugriff. Maschinen verstehen diesen Text nicht und fangen mit dem Link nichts an<br />Der Text kann nach Belieben angepasst werden' );	             
define('_COM_SEF_SH_SMELLYPOT_TEXT',				'Robot Fallentext');
define('_COM_SEF_TT_SH_SMELLYPOT_TEXT',				'Wurde ein Spamrobot durch den Honey Pot erkannt und der weitere Seitenzugang gesperrt, wird ein Link auf dieser Seite angezeigt welcher im Honey Pot Projekt zur Nachverfolgung gespeichert wird. Menschen können der Nachricht und dem Link folgen falls dennoch ein Fehler passierte');
define('_COM_SEF_SH_ONLY_NUM_VARS',					'Numerische Werte');
define('_COM_SEF_TT_SH_ONLY_NUM_VARS',				'Werte in dieser Liste werden gegen Zahlenn ausgetauscht: nur Zahlen von 0 - 9 möglich!<br />Jeweils ein Wert pro Zeile');
define('_COM_SEF_SH_ONLY_ALPHA_NUM_VARS',			'Alphanumerische Werte');
define('_COM_SEF_TT_SH_ONLY_ALPHA_NUM_VARS',		'Werte in dieser Liste werden gegen Alpanumerische getauscht: Zeichen von 0 - 9 und Kleinbuchstaben von a - z. Jeweils ein Wert pro Zeile');
define('_COM_SEF_SH_NO_PROTOCOL_VARS',				'Prüfe Hyperlinks in Werten');
define('_COM_SEF_TT_SH_NO_PROTOCOL_VARS',			'Werte in dieser Liste werden auf enthaltene Links geprüft: http://, https://, ftp:// ');
define('_COM_SEF_SH_IP_WHITE_LIST',					'IP White List');
define('_COM_SEF_TT_SH_IP_WHITE_LIST',				'Jede Anfrage einer IP-ADresse aus dieser Liste wird <strong>Akzeptiert</strong> nachdem sie alle vorherigen Checks (siehe oben) passiert hat.<br />Jeweils eine IP-Adresse pro Zeile<br />Wildcards können verwendet werden, z.B.: : 192.168.0.* es werden alle Adressen des Bereichs 192.168.0.1 bis 192.168.0.255 akzepiert.');
define('_COM_SEF_SH_IP_BLACK_LIST',					'IP Black List');
define('_COM_SEF_TT_SH_IP_BLACK_LIST',				'Jede Anfrage einer IP-ADresse aus dieser Liste wird <strong>Blockiert</strong> nachdem sie eventuell alle vorherigen Checks (siehe oben) passiert hat.<br />Jeweils eine IP-Adresse pro Zeile<br />Wildcards können verwendet werden, z.B.: : 192.168.0.* es werden alle Adressen des Bereichs 192.168.0.1 bis 192.168.0.255 blockiert.');
define('_COM_SEF_SH_UAGENT_WHITE_LIST',				'UserAgent White List');
define('_COM_SEF_TT_SH_UAGENT_WHITE_LIST',			'Jede Anfrage mit einem UserAgent aus dieser Liste wird <strong>akzeptiert</strong>, nachdem sie alle vorigen Tests bestanden hat.<br />Jeweils 1 UserAgent pro Zeile');
define('_COM_SEF_SH_UAGENT_BLACK_LIST',				'UserAgent Black List');
define('_COM_SEF_TT_SH_UAGENT_BLACK_LIST',			'Jede Anfrage mit einem UserAgent aus dieser Liste wird <strong>blockiert</strong>, nachdem sie alle vorigen Tests bestanden hat.<br />Jeweils 1 UserAgent pro Zeile');
define('_COM_SEF_SH_MONTHS_TO_KEEP_LOGS',			'Monate zur Logspeicherung');
define('_COM_SEF_TT_SH_MONTHS_TO_KEEP_LOGS',		'Wenn die Logspeicherung von Attacken aktiviert ist, kann hier die Anzahl der Monate dieser Speicherung angegeben werden. Z.B. wird hier 1 angegeben, bedeutet dies dass das aktuelle PLUS dem VORmonat zur Verfügung steht. Vorige Monate werden gelöscht');

// anti flood
define( '_COM_SEF_SH_ANTIFLOOD_TITLE',				'Anti-Flood' );
define('_COM_SEF_SH_ACTIVATE_ANTIFLOOD',			'Anti-Flood aktivieren');
define('_COM_SEF_TT_SH_ACTIVATE_ANTIFLOOD',			'Wenn aktiviert wird sh404SEF alle IP-Adressen auf zu häufige Seitenfragen prüfen. Tritt der Fall zu häufiger Seitenaufrufe ein, kann es sein, dass diese Webseite damit überladen wird und nicht aufzurufen ist!');
define('_COM_SEF_SH_ANTIFLOOD_ONLY_ON_POST',		'Nur bei POST-Daten (Forms)');
define('_COM_SEF_TT_SH_ANTIFLOOD_ONLY_ON_POST',		'Wenn aktiviert, wird nur überprüft wenn die Daten aus einem Formular kommen. Das passiert normalerweise durch Spamrobots');
define('_COM_SEF_SH_ANTIFLOOD_PERIOD',				'Anti-Flood Kontrolle');
define('_COM_SEF_TT_SH_ANTIFLOOD_PERIOD',			'Zeit (in Sekunden) in welcher Anfragen derselben IP.Adresse überprüft werden');
define('_COM_SEF_SH_ANTIFLOOD_COUNT',				'Max. Anzahl Anfragen');
define('_COM_SEF_TT_SH_ANTIFLOOD_COUNT',			'Maximale Anzahl von Seitenabfragen/-aufrufen derselben IP.Adresse nachdem der Aufruf geblockt wird.<br />Z.B. eine Anfragenanzahl von 4 innerhalb von 10 Sekunden ruft eine 403er-Seite (Verboten) auf. Weitere Anfragen derselben IP.Adresse werden geblockt, andere nicht');
// tab language
define('_COM_SEF_SH_CONF_TAB_LANGUAGES',			'Sprachen');
define('_COM_SEF_SH_DEFAULT',						'Vorgabe');
define('_COM_SEF_SH_YES',							'Ja');
define('_COM_SEF_SH_NO',							'Nein');
define('_COM_SEF_TT_SH_INSERT_LANGUAGE_CODE_PER_LANG',	'Wenn aktiviert, wird er Sprachencode für <strong>diese Sprache</strong> in die URL einegfügt.<br />WEnn <strong>Nein</strong> wird der Sprachencode <strong>nie</strong> eingefügt.<br />Wenn auf <strong>Vorgabe>/strong> wird der Sprachencode für alle anderen als die Standardseitensprache eingefügt');
define('_COM_SEF_TT_SH_TRANSLATE_URL_PER_LANG',		'Wenn Ja und die Webseite ist mehrsprachig, wird die URL <strong>in diese Sprache lt. JoomFish</strong> übersetzt.<br />Wenn Nein, URLs werden nei übersetzt.<br />Bei Vorgabe wird ebenfalls übersetzt, wird nur 1 Sprache verwendet, hat diese Einstellung keine Effekt');
define('_COM_SEF_TT_SH_INSERT_LANGUAGE_CODE_GEN',	'Wenn Ja wird der Sprachencode von sh404SEf in die URL eingefügt (es kann auch eine individuelle Einstellung vorgenommen werden, siehe weiter unten)');
define('_COM_SEF_TT_SH_TRANSLATE_URL_GEN',			'Wenn Ja und die Webseite ist mehrsprachig, wird die URL in die Sprache des Besuchers übersetzt (lt. JoomFish).<br />Ansonsten beleibt die URL wie sie ist (es kann auch eine individuelle Einstellung vorgenommen werden, siehe weiter unten)');
define('_COM_SEF_SH_ADV_COMP_DEFAULT_STRING',		'Vorgabe Name');
define('_COM_SEF_TT_SH_ADV_COMP_DEFAULT_STRING',	'Wird hier ein Text angegeben, wird er in <strong>alle</strong> URLs dieser Komponente am Beginn eingefügt.<hr />Normalerweise nicht anzuwenden, ausgenommen wenn ältere SEF-URls anderer SEF-Komponenten verwendet werden (bei Import derselben)');
define('_COM_SEF_TT_SH_NAME_BY_COMP',				'. <br />YHier kann ein Name angegeben werden welche anstatt des Menünamens verwendet wird. Dazu bitte den TAB <strong>Komponenten</strong> aufrufen (dieser Text wird nicht übersetzt!)');
// admin display
define('_COM_SEF_STANDARD_ADMIN',					'Hier klicken für einfache Anzeige');
define('_COM_SEF_ADVANCED_ADMIN',					'Hier klicken für erweiterte Anzeige (alle Parameter)');
define('_COM_SEF_SH_MULTIPLE_H1_TO_H2',				'h1 in h2 Ändern');
define('_COM_SEF_TT_SH_MULTIPLE_H1_TO_H2',			'Wenn aktiviert und es sind mehrere h1.Tags auf der Seite, werden diese in h2.Tags umgewandelt.<br />Ist nur 1 h1.Tag auf der Seite, sollte dieser WErt nicht geändert werden');
define('_COM_SEF_SH_INSERT_NOFOLLOW_PDF_PRINT',		'Nofollow Tag bei Druck- &amp; PDF-Links');
define('_COM_SEF_TT_SH_INSERT_NOFOLLOW_PDF_PRINT',	'Wenn aktiviert, wird allen Druck- und PDF-Links das Attribut &quot;rel=nofellow&quot; hinzugefügt<br />Damit wird doppelter Inhalt bei Suchmaschinen vermeiden');
define('_COM_SEF_SH_INSERT_READMORE_PAGE_TITLE',	'Titel Weiterlesen ... Links');
define('_COM_SEF_TT_SH_INSERT_READMORE_PAGE_TITLE', 'Wenn aktiviert und ein &quot;Weiterlesen ... Link&quot; angezeigt wird, wird ein Titel.Tag hinzugefügt (verbessert die Linkgewichtung in Suchmaschinen<hr />HINWEIS:<br />vorher OHNE Testen!');
define('_COM_SEF_VM_USE_ITEMS_PER_PAGE',			'Verwende Zahlen bei Dropdown-Listen');
define('_COM_SEF_TT_VM_USE_ITEMS_PER_PAGE',			'Wenn aktiv, werden DropDown-Listen mit Zahlen versehen mit denen Benutzer diese Listen per Zahl aufrufen können.<br />Werden keine DropDown-Listen verwendet oder sind diese URLs bereits von Suchmaschinen indeziert, sollte diese Einstellung nicht angewendet werden');
define('_COM_SEF_SH_CHECK_POST_DATA',				'Prüfe Formdaten (POST)');
define('_COM_SEF_TT_SH_CHECK_POST_DATA',			'Wenn aktiv werden alle daten aus Forumlaren auf Eisnchleusen von ungültigem code bzw. Variablen überprüft.<br />Sollten in Formularen aus z.B. Foren Codeteile mitgesendet werden, können diese Beiträge blockiert werden, dann sollte dieser Parameter nicht aktiviert werden!');

// admin panel
define('_COM_SEF_SH_SEC_STATS_TITLE',				'Sicherheitsstatistik');
define('_COM_SEF_SH_SEC_STATS_UPDATE',				'Update');
define('_COM_SEF_SH_TOTAL_ATTACKS',					'Attacken');
define('_COM_SEF_SH_TOTAL_CONFIG_VARS',				'Variable "mosConfig" in URL');
define('_COM_SEF_SH_TOTAL_BASE64',					'Base64 injection');
define('_COM_SEF_SH_TOTAL_SCRIPTS',					'Script injection');
define('_COM_SEF_SH_TOTAL_STANDARD_VARS',			'Illegale Standard Vars');
define('_COM_SEF_SH_TOTAL_IMG_TXT_CMD',				'Remote File Inclusion');
define('_COM_SEF_SH_TOTAL_IP_DENIED',				'IP.Adressen geblockt');
define('_COM_SEF_SH_TOTAL_USER_AGENT_DENIED',		'User Agents geblockt');
define('_COM_SEF_SH_TOTAL_FLOODING',				'Zuviele Anfragen (Flooding)');
define('_COM_SEF_SH_TOTAL_PHP',						'Rückweisungen lt. Honey Pot');
define('_COM_SEF_SH_TOTAL_PER_HOUR',				' / Std.');
define('_COM_SEF_SH_SEC_DEACTIVATED',				'Sek. Functionen nicht in Verwendung');
define('_COM_SEF_SH_TOTAL_PHP_USER_CLICKED',		'PHP durch Benutzer');

// smf forum
define('_COM_SEF_SH_COM_SMF_TITLE',					'SMF Bridge');
define('_COM_SEF_SH_INSERT_SMF_NAME',				'Forumsname einfügen');
define('_COM_SEF_TT_SH_INSERT_SMF_NAME',			'Wenn aktiviert wird der Forumstitel in die URL voran gestellt');
define('_COM_SEF_SH_SMF_ITEMS_PER_PAGE',			'Anzahl pro Seite');
define('_COM_SEF_TT_SH_SMF_ITEMS_PER_PAGE',			'Anzahl der Forumsartikel auf einer Seite');
define('_COM_SEF_SH_INSERT_SMF_BOARD_ID',			'Forums.ID einfügen');
define('_COM_SEF_TT_SH_INSERT_SMF_BOARD_ID', 		_COM_SEF_TT_SH_FB_INSERT_CATEGORY_NAME );
define('_COM_SEF_SH_INSERT_SMF_TOPIC_ID',			'Topic.ID einfügen');
define('_COM_SEF_TT_SH_INSERT_SMF_TOPIC_ID',		_COM_SEF_TT_SH_FB_INSERT_MESSAGE_ID);
define('_COM_SEF_SH_INSERT_SMF_USER_NAME',			'Benutzername einfügen');
define('_COM_SEF_TT_SH_INSERT_SMF_USER_NAME',		'Wenn aktiviert, wird anstatt der Benutzer.ID dessen Name in die URLs eingesetzt.<br />HINWEIS: diese Funktion benötigt viel Speicherplatz da für jede URL eine Einmalige in der Datenbank generiert wird');
define('_COM_SEF_SH_INSERT_SMF_USER_ID',			'Benutzer.ID einfügen');
define('_COM_SEF_TT_SH_INSERT_SMF_USER_ID',			'Wenn aktiviert, wird zusätzlich zum Benutzernamen dessen ID hinzugefügt');
define('_COM_SEF_SH_PREPEND_TO_PAGE_TITLE',			'Vor Seitentitel einfügen');
define('_COM_SEF_TT_SH_PREPEND_TO_PAGE_TITLE',		'Text welcher <strong>vor jedem Seitentitel</strong> hinzu gefügt wird');
define('_COM_SEF_SH_APPEND_TO_PAGE_TITLE',			'Nach Seitentitel einfügen');
define('_COM_SEF_TT_SH_APPEND_TO_PAGE_TITLE',		'Text welcher <strong>nach jedem Seitentitel</strong> hinzu gefügt wird');
define('_COM_SEF_SH_DEBUG_TO_LOG_FILE',				'Schreibe Fehlerinfo');
define('_COM_SEF_TT_SH_DEBUG_TO_LOG_FILE',			'Wenn Ja wird sh404SEF alle Aktionen mitschreiben um bei Fehlern Hilfe geben zu können<br /><strong style=&quot;color:red&quot;>diese Datei kann sehr groß werden, zudem wird der Seitenaufbau dadurch langsamer!</strong><br />Daher nur anschalten wenn benötigt wird! Aus Sicherheitsgründen wird - wenn aktiviert - diese Funktion automatisch nach 1 Stunde abgeschaltet.<br />Die Datei wird im Verzeichnis  /administrator/components/com_sef/logs/ gespeichert');

define('_COM_SEF_ALIAS_LIST',						'Alias Liste');
define('_COM_SEF_TT_ALIAS_LIST',					'Hier eine Liste aller Alias für diese URL angeben<br />Pro Zeile eine URL<hr />Beispiel:<br />old-url.html<br/>oder<br/>my-other-old-url.php?var=12&amp;test=15<hr />sh404SEF macht dann einen 301 redirect zur aktuellen URL wenn eine dieser URLs verlangt wird');
define('_COM_SEF_HOME_ALIAS',						'HomePage Alias');
define('_COM_SEF_TT_HOME_PAGE_ALIAS_LIST',			'Hier eine Liste (pro Zeile Einen) angeben für die aktuelle Homepage<hr />Beispiel:<br />old-url.html<br/>oder<br/>my-other-old-url.php?var=12&amp;test=15<hr />sh404SEF macht dann einen 301 redirect zur Startseite wenn eine dieser URLs verlangt wird');

define('_COM_SEF_SH_USE_DEFAULT_ITEMIDS',			'Itemid einfügen wenn Keine');
define('_COM_SEF_TT_SH_USE_DEFAULT_ITEMIDS',		'Wenn aktiviert und eine Nicht-SEF-URL keine Itemid hat, versucht sh404SEF eine Standard-Itemid zu verwenden (pro Komponente - siehe TAB Komponenten)');
define('_COM_SEF_SH_ADV_COMP_DEFAULT_ITEMID',		'Vorgabe Itemid');
define('_COM_SEF_TT_SH_ADV_COMP_DEFAULT_ITEMID',	'Ist die Einstellung <b>' . _COM_SEF_SH_USE_DEFAULT_ITEMIDS . '</b> aktiviert (Tab Erweitert), kann hier eine Standard-Itemid angegben werden.<br />Sie wird imemr dann angewendet, wennd as CMS oder eine Komponente eine URL ohne Itemid erzeugt');

define('_COM_SEF_SH_INSERT_OUTBOUND_LINKS_IMAGE',	'Externurl Symbol');
define('_COM_SEF_TT_SH_INSERT_OUTBOUND_LINKS_IMAGE',	'Wenn aktiviert, wird bei jedem Link welche diese Webseiten verlässt ein Symbol angezeigt (zur leichteren Identifikation dieser Links)');
define('_COM_SEF_SH_OUTBOUND_LINKS_IMAGE_BLACK',	'Schwarzes Symbol');
define('_COM_SEF_SH_OUTBOUND_LINKS_IMAGE_WHITE',	'Weißes Symbol');
define('_COM_SEF_SH_OUTBOUND_LINKS_IMAGE',			'Externlinks Farbensymbol');
define('_COM_SEF_TT_SH_OUTBOUND_LINKS_IMAGE',		'Beide Bilder verwenden einen transparenten Hintergrund<br />Je nach aktuellem Webseitenhintergrund das Passende wählen<hr />Die Bilder sind im Ordner /administrator/components/com_sef/images/ unter external-white.png und external-black.png gespeichert und sind  15x16 pixels Groß');

// V 1.3.3
define('_COM_SEF_DEFAULT_PARAMS_TITLE', 			'Very adv.');
define('_COM_SEF_DEFAULT_PARAMS_WARNING', 			'WARNING: change these values only if you know what you are doing! In case of wrongdoing, you could make damages you will have trouble repairing.');

// V 1.0.12
define('_COM_SEF_USE_CAT_ALIAS', 'Use category alias');
define('_COM_SEF_TT_USE_CAT_ALIAS', 'If set to <strong>Yes</strong>, sh404sef will use a category alias instead of its actual name every time that name is required to build a url');
define('_COM_SEF_USE_SEC_ALIAS', 'Use section alias');
define('_COM_SEF_TT_USE_SEC_ALIAS', 'If set to <strong>Yes</strong>, sh404sef will use a section alias instead of its actual name every time that name is required to build a url');
define('_COM_SEF_USE_MENU_ALIAS', 'Use menu alias');
define('_COM_SEF_TT_USE_MENU_ALIAS', 'If set to <strong>Yes</strong>, sh404sef will use a menu item alias instead of its actual title every time that title is required to build a url');
define('_COM_SEF_SH_ENABLE_TABLE_LESS', 'Use table-less output');
define('_COM_SEF_TT_SH_ENABLE_TABLE_LESS', 'If set to <strong>Yes</strong>, sh404sef will make Joomla use only div tags (no table tags) when outputing content, regardless of the template you are using. You should not have removed the Beez template for this to work. Beez template is installed by default with Joomla.<br /><strong>WARNING</strong> : you will have to adjust your template stylesheet to match this new html output format.');

// V 1.0.13
define( '_COM_SEF_JC_MODULE_CACHING_DISABLED', 'Caching for Joomfish language selection module has been disabled!');


?>