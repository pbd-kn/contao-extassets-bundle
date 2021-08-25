<?php

declare(strict_types=1);

/*
 *
 *  Contao Open Source CMS
 *
 *  Copyright (c) 2005-2014 Leo Feyer
 *
 *
 *  Contao Open Source CMS
 *
 *  Copyright (C) 2005-2013 Leo Feyer
 *   @package   Extassets
 *   @author    r.kaltofen@heimrich-hannot.de
 *   @license   GNU/LGPL
 *   @copyright Heimrich & Hannot GmbH
 *
 *  The namespaces for psr-4 were revised.
 *
 *  @package   contao-extasset-bundle
 *  @author    Peter Broghammer <pb-contao@gmx.de>
 *  @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 *  @copyright Peter Broghammer 2021-
 *
 *  Bootstrap's selection introduced.
 *
 */

/*
 * Fields
 */
$GLOBALS['TL_LANG']['tl_extcss']['title'] = ['Titel', 'Bitte geben Sie einen Titel an.'];
$GLOBALS['TL_LANG']['tl_extcss']['addBootstrapPrint'] = ['print.css aktivieren', 'Fügen Sie der CSS Gruppe die Bootstrap print.css hinzu.'];
$GLOBALS['TL_LANG']['tl_extcss']['variablesSRC'] = ['Variablen Quellen', 'Sollen globale Variablen, so z.B. die Bootstrap Variablen, überschrieben werden, so geben Sie die Dateien hier an.'];
$GLOBALS['TL_LANG']['tl_extcss']['variablesOrderSRC'] = ['Sortierreihenfolge ', 'Sortierreihenfolge der Variablen Quellen.'];
$GLOBALS['TL_LANG']['tl_extcss']['observeFolderSRC'] = ['Ordner überwachen', 'Geben Sie einen Ordner an, der überwacht werden soll, und neue Dateien automatisch hinzugefügt werden.'];
$GLOBALS['TL_LANG']['tl_extcss']['addElegantIcons'] = ['Elegant Icons hinzufügen', 'Elegant Icon Font der Gruppe hinzufügen.'];
$GLOBALS['TL_LANG']['tl_extcss']['addingbootstrap'] = ['Bootstrap hinzufügen', 'Bootstrap hinzufügen (Asset/bootstrap/less als less-src. BOOTSTRAPDISTDIR als compiled Version css/bootstrap.min.css). Bitte less cache leeren'];
$GLOBALS['TL_LANG']['tl_extcss']['setDebug'] = ['set Debug', 'write Debug to var/logs/prod-[Date]-extasset_debug.log'];

/*
 * Legends
 */
$GLOBALS['TL_LANG']['tl_extcss']['title_legend'] = 'Titel';
$GLOBALS['TL_LANG']['tl_extcss']['config_legend'] = 'Konfiguration';
$GLOBALS['TL_LANG']['tl_extcss']['font_legend'] = 'Icon-Fonts';

/*
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_extcss']['new'] = ['Neue Gruppe', 'Eine neue CSS Gruppe erstellen.'];
$GLOBALS['TL_LANG']['tl_extcss']['show'] = ['Gruppendetails', 'Details der Gruppe ID %s anzeigen'];
$GLOBALS['TL_LANG']['tl_extcss']['edit'] = ['Gruppe bearbeiten ', 'Gruppe ID %s bearbeiten'];
$GLOBALS['TL_LANG']['tl_extcss']['editheader'] = ['Einstellungen der Gruppe bearbeiten ', 'Einstellungen der Gruppe ID %s bearbeiten'];
$GLOBALS['TL_LANG']['tl_extcss']['cut'] = ['Gruppe verschieben', 'Verschieben der Gruppe ID %s'];
$GLOBALS['TL_LANG']['tl_extcss']['copy'] = ['Gruppe kopieren ', 'Kopieren der Gruppe ID %s'];
$GLOBALS['TL_LANG']['tl_extcss']['delete'] = ['Gruppe löschen', 'Löschen der Gruppe ID %s'];
