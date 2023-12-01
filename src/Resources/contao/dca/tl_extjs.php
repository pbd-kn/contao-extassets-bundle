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
use Contao\Controller;
Controller::loadDataContainer('tl_content');
$tlContentDCA = &$GLOBALS['TL_DCA']['tl_content'];
/*
 * Table tl_extjs
 */
$GLOBALS['TL_DCA']['tl_extjs'] = [
    // Config
    'config' => [
        'dataContainer' => 'Table',
        'enableVersioning' => true,
        'ctable' => ['tl_extjs_file'],
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => 1,
            'fields' => ['title'],
            'flag' => 1,
        ],
        'label' => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"',
            ],
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_extjs']['edit'],
                'href' => 'table=tl_extjs_file',
                'icon' => 'edit.gif',
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_extjs']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_extjs']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_extjs']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        'default' => '{title_legend},title;{bootstrap_legend},addBootstrap;jsPosition;setDebug;',
    ],
    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title' => [
            'label' => &$GLOBALS['TL_LANG']['tl_extjs']['title'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 64],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'addBootstrap' => [
            'label' => &$GLOBALS['TL_LANG']['tl_extjs']['addBootstrap'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'default' => true,
            'sql' => "char(1) NOT NULL default ''",
        ],
        'jsPosition' => array(
			'label'                   => &$GLOBALS['TL_LANG']['tl_extjs']['position'],
			'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => array('0' => 'head','1'=> 'body'),
            'default'                 => 'body',
//            'eval'                    => array('submitOnChange'=>true,'tl_class'=>'w50'),
            'eval'                    => array('tl_class'=>'w50'),
			'sql'                     => "varchar(64) NOT NULL default '1'",
		),
        
        'setDebug' => [
            'label' => &$GLOBALS['TL_LANG']['tl_extcss']['setDebug'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'default' => true,
            'sql' => "char(1) NOT NULL default '0'",
        ],

    ],
];

//echo 'vor insert'."\n";
// Merge the 'palettes' and 'subpalettes' from tl_content to tl_my_custom_element
//echo 'vor insert1 '.$tlContentDCA['palettes']."\n";
$GLOBALS['TL_DCA']['tl_extjs']['palettes'] += $tlContentDCA['palettes'];
//$GLOBALS['TL_DCA']['tl_extjs']['subpalettes'] += $tlContentDCA['subpalettes'];
//echo 'nach insert'."\n";
