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
 *  awesomeselection introduced.
 *
 */

/*
 * Custom Variables
 */

\define('CSSDIR', 'assets/css/');

\define('BOOTSTRAPDISTDIR', 'vendor/twbs/bootstrap/dist/');
\define('BOOTSTRAPLESSCUSTOMDIR', 'assets/bootstrap/less/custom/');
\define('BOOTSTRAPCSSDIR', 'assets/bootstrap/css/');
\define('BOOTSTRAPJSDIR', 'assets/bootstrap/js/');

\define('FONTAWESOMEVERSION', 'v4.7.0');
\define('FONTAWESOMEDIR', 'assets/font-awesome/');
\define('FONTAWESOMECSSDIR', FONTAWESOMEDIR.'css/');
\define('FONTAWESOMELESSDIR', FONTAWESOMEDIR.'less/');
\define('FONTAWESOMELESSCUSTOMDIR', FONTAWESOMEDIR.'less/custom/');
\define('FONTAWESOMEFONTDIR', FONTAWESOMEDIR.'fonts/');

\define('ELEGANTICONSDIR', 'assets/elegant-icons/');
\define('ELEGANTICONSCSSDIR', ELEGANTICONSDIR.'css/');
\define('ELEGANTICONSLESSDIR', ELEGANTICONSDIR.'less/');
\define('ELEGANTICONSFONTDIR', ELEGANTICONSDIR.'fonts/');

\define('LESSCSSCACHEDIR', 'assets/css/lesscache/');

\define('TINYMCEDIR','assets/tinymce4/');
\define('TINYMCEPLUGINDIR',TINYMCEDIR.'js/plugins/');

/*
 * BACK END MODULES
 *
 * Back end modules are stored in a global array called "BE_MOD". You can add
 * your own modules by adding them to the array.
 */

$GLOBALS['BE_MOD']['design']['extcss'] = [
    'tables' => ['tl_extcss', 'tl_extcss_file', 'tl_files'],
    'icon' => 'vendor/pbd-kn/contao-extassets/src/Resources/contao/assets/extcss/icon.png',
];

$GLOBALS['BE_MOD']['design']['extjs'] = [
    'tables' => ['tl_extjs', 'tl_extjs_file', 'tl_files'],
    'icon' => 'vendor/pbd-kn/contao-extassets/src/Resources/contao/assets/extcss/icon.png',
];

/*
 * Mime types
 */
$GLOBALS['TL_MIME']['less'] = ['text/css', 'iconCSS.gif'];

/*
 * Hooks
 */
$GLOBALS['TL_HOOKS']['replaceDynamicScriptTags'][] = ['PBDKN\ExtAssets\Resources\contao\classes\ExtCss', 'hookReplaceDynamicScriptTags'];
$GLOBALS['TL_HOOKS']['replaceDynamicScriptTags'][] = ['PBDKN\ExtAssets\Resources\contao\classes\ExtJs', 'hookReplaceDynamicScriptTags'];
$GLOBALS['TL_HOOKS']['getPageLayout'][] = ['PBDKN\ExtAssets\Resources\contao\classes\ExtCss', 'hookGetPageLayout'];

/*
 * Models
 */
$GLOBALS['TL_MODELS']['tl_extcss'] = 'PBDKN\ExtAssets\Resources\contao\models\ExtCssModel';
$GLOBALS['TL_MODELS']['tl_extcss_file'] = 'PBDKN\ExtAssets\Resources\contao\models\ExtCssFileModel';
$GLOBALS['TL_MODELS']['tl_extjs'] = 'PBDKN\ExtAssets\Resources\contao\models\ExtJsModel';
$GLOBALS['TL_MODELS']['tl_extjs_file'] = 'PBDKN\ExtAssets\Resources\contao\models\ExtJsFileModel';

/*
 * PurgeData
 */
$GLOBALS['TL_PURGE']['folders']['less'] = [
    'affected' => [BOOTSTRAPLESSCUSTOMDIR, FONTAWESOMELESSCUSTOMDIR, LESSCSSCACHEDIR, 'assets/css/'],
    'callback' => ['PBDKN\ExtAssets\Resources\contao\classes\ExtAutomator', 'purgeLessCache'],
];

