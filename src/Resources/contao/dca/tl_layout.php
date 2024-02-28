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
use Contao\CoreBundle\DataContainer\PaletteManipulator;
$dc = &$GLOBALS['TL_DCA']['tl_layout'];

/*
 * fields
 */

$dc['fields']['extcss'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_layout']['extcss'],
    'exclude' => true,
    'inputType' => 'checkboxWizard',
    'foreignKey' => 'tl_extcss.title',
    'eval' => ['multiple' => true],
    'sql' => "varchar(255) NOT NULL default ''",
];

$dc['fields']['extjs'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_layout']['extjs'],
    'exclude' => true,
    'inputType' => 'checkboxWizard',
    'foreignKey' => 'tl_extjs.title',
    'eval' => ['multiple' => true],
    'sql' => "varchar(255) NOT NULL default ''",
];

/*
 * palettes
 */

PaletteManipulator::create()
    // apply the field "extcss" after the field "stylesheet"
    ->addField('extcss', 'stylesheet')
    ->addField('extjs', 'script')
    // now the field is registered in the PaletteManipulator
    // but it still has to be registered in the globals array:
    ->applyToPalette('default', 'tl_layout') 
;