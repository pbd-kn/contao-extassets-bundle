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
 * Table tl_extcss
 */
$GLOBALS['TL_DCA']['tl_extcss'] = [
    // Config
    'config' => [
        'dataContainer' => 'Table',
        'enableVersioning' => true,
        'ctable' => ['tl_extcss_file'],
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
                'label' => &$GLOBALS['TL_LANG']['tl_extcss']['edit'],
                'href' => 'table=tl_extcss_file',
                'icon' => 'edit.gif',
            ],
            'editheader' => [
                'label' => &$GLOBALS['TL_LANG']['tl_extcss']['editheader'],
                'href' => 'act=edit',
                'icon' => 'header.gif',
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_extcss']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_extcss']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_extcss']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
        ],
    ],

    // Palettes
	//	'default' => '{title_legend},title,observeFolderSRC;{bootstrap_legend},addingbootstrap;setDebug;'
	//	'default' => '{title_legend},title,observeFolderSRC;{bootstrap_legend},addingbootstrap;{font_awesome_legend},addFontAwesome,setDebug;'
 	//			'__selector__'				=> array('addBootstrap'),
	//			'default'                   => '{title_legend},title,observeFolderSRC;{bootstrap_legend},addBootstrap;{font_awesome_legend},addFontAwesome;'

    'palettes' => [
//        '__selector__' => array('addBootstrap','addFontAwesome'),
        '__selector__' => array('addBootstrap','addFontAwesome'),
		'default' => '{title_legend},title;{less_legend},observeFolderSRC,variablesSRC;{bootstrap_legend},addbootstrap;{font_awesome_legend},addFontAwesome;setDebug;'
    ],
	// Subpalettes
	'subpalettes' => array
	(
//		'addBootstrap'				=> 'variablesSRC',
        'addFontAwesome'            => 'selectAweSome,setTinymce',
	),
    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title' => [
            'label' => &$GLOBALS['TL_LANG']['tl_extcss']['title'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 64],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'addBootstrapPrint' => [
            'label' => &$GLOBALS['TL_LANG']['tl_extcss']['addBootstrapPrint'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'variablesSRC' => [
            'label' => &$GLOBALS['TL_LANG']['tl_extcss']['variablesSRC'],
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => ['multiple' => true,
                'fieldType' => 'checkbox',
                'orderField' => 'variablesOrderSRC',
                'files' => true,
                'extensions' => 'css, less',
            ],
            'sql' => 'blob NULL',
        ],
        'variablesOrderSRC' => [
            'label' => &$GLOBALS['TL_LANG']['tl_content']['variablesOrderSRC'],
            'sql' => 'blob NULL',
        ],
        'addElegantIcons' => [
            'label' => &$GLOBALS['TL_LANG']['tl_extcss']['addElegantIcons'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'default' => true,
            'sql' => "char(1) NOT NULL default ''",
        ],
        'addbootstrap' => [
            'label' => &$GLOBALS['TL_LANG']['tl_extcss']['addingbootstrap'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'default' => true,
            'sql' => "char(1) NOT NULL default ''",
        ],

		'bootstrapVariablesSRC'	=> array(
					'label'                   => &$GLOBALS['TL_LANG']['tl_extcss']['bootstrapVariablesSRC'],
					'exclude'                 => true,
					'inputType'               => 'fileTree',
					'eval'                    => array('fieldType'=>'radio', 'filesOnly'=>true, 'extensions'=>'css, less'),
					'sql'                     => (version_compare(VERSION, '3.2', '<')) ? "varchar(255) NOT NULL default ''" : "binary(16) NULL"
		),
        'observeFolderSRC' => [
            'label' => &$GLOBALS['TL_LANG']['tl_extcss']['observeFolderSRC'],
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => ['fieldType' => 'radio', 'filesOnly' => false, 'extensions' => 'css, less'],
            'sql' => (version_compare(VERSION, '3.2', '<')) ? "varchar(255) NOT NULL default ''" : 'binary(16) NULL',
        ],
       
        'addFontAwesome' => array(
			'label'                   => &$GLOBALS['TL_LANG']['tl_extcss']['addFontAwesome'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'default'                 => true,
            'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'long'),
			'sql'                     => "char(1) NOT NULL default ''",
		),
        'selectAweSome' => array(
			'label'                   => &$GLOBALS['TL_LANG']['tl_extcss']['selectFontAwesome'],
			'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => array('4' => 'Version 4.7', '5' => 'Version 5'),
            'default'                 => "Version 4.7",
//            'eval'                    => ['tl_class'=>'w50'],
            'eval'                    => array('submitOnChange'=>true, 'maxlength'=>16),
			'sql'                     => "char(10) NOT NULL default '4.7'",
		),
        'setTinymce' => array(
			'label'                   => &$GLOBALS['TL_LANG']['tl_extcss']['setTinymce'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'default'                 => true,
//            'eval'                    => ['tl_class'=>'w50'],
            'eval'                    => array('submitOnChange'=>true),
			'sql'                     => "char(1) NOT NULL default ''",
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

\HeimrichHannot\Haste\Dca\General::addDateAddedToDca('tl_extcss');
