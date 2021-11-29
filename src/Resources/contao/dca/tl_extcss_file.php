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

use PBDKN\ExtAssets\Resources\contao\models\ExtCssModel;

/*
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2013 Leo Feyer
 *
 * @package   ExtAssets
 * @author    r.kaltofen@heimrich-hannot.de
 * @license   GNU/LGPL
 * @copyright Heimrich & Hannot GmbH
 */
$this->loadLanguageFile('tl_files');

/*
 * Table tl_extcss_file
 */
$GLOBALS['TL_DCA']['tl_extcss_file'] = [
    // Config
    'config' => [
        'dataContainer' => 'Table',
        'ptable' => 'tl_extcss',
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
            ],
        ],
        'onload_callback' => [
            [
                'tl_extcss_file',
                'observeFolder',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => 4,
            'fields' => [
                'sorting',
            ],
            'headerFields' => [
                'title',
                'tstamp',
            ],
            'panelLayout' => 'filter;sort,search,limit',
            'child_record_callback' => [
                'tl_extcss_file',
                'listCSSFiles',
            ],
            'child_record_class' => 'no_padding',
            'disableGrouping' => true,
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
                'label' => &$GLOBALS['TL_LANG']['tl_extcss_file']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_extcss_file']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif',
            ],
            'cut' => [
                'label' => &$GLOBALS['TL_LANG']['tl_extcss_file']['cut'],
                'href' => 'act=paste&amp;mode=cut',
                'icon' => 'cut.gif',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_extcss_file']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_extcss_file']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        'default' => '{src_legend},src;',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'foreignKey' => 'tl_extcss.id',
            'sql' => "int unsigned NOT NULL default '0'",
            'relation' => [
                'type' => 'belongsTo',
                'load' => 'eager',
            ],
        ],
        'sorting' => [
            'sorting' => true,
            'flag' => 11,
            'sql' => "int unsigned NOT NULL default '0'",
        ],
        'tstamp' => [
            'sql' => "int unsigned NOT NULL default '0'",
        ],
        'src' => [
            'label' => &$GLOBALS['TL_LANG']['tl_extcss_file']['src'],
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => [
                'fieldType' => 'radio',
                'filesOnly' => true,
                'mandatory' => true,
                'extensions' => 'css, less',
            ],
            'sql' => (version_compare(VERSION, '3.2', '<')) ? "varchar(255) NOT NULL default ''" : 'binary(16) NULL',
        ],
    ],
];

class tl_extcss_file extends Backend
{
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    public function observeFolder($dc)
    {
        $objModel = ExtCssModel::findByPk($dc->id);

        if (null === $objModel) {
            return false;
        }

        $dca = &$GLOBALS['TL_DCA']['tl_extcss_file'];

        //\ExtAssets\ExtCss::observeCssGroupFolder($dc->id);
        \PBDKN\ExtAssets\Resources\contao\classes\ExtCss::observeCssGroupFolder($dc->id);
    }

    /**
     * Add the type of input field.
     *
     * @param
     *            array
     *
     * @return string
     */
    public function listCSSFiles($arrRow)
    {

        $objFiles = FilesModel::findById($arrRow['src']);

        // Return if there is no result
        if (null === $objFiles) {
            return '';
        }

        // Show files and folders
        if ('folder' === $objFiles->type) {
            $thumbnail = $this->generateImage('folderC.gif');
        } else {
            $objFile = new \File($objFiles->path, true);
            $thumbnail = $this->generateImage($objFile->icon);
        }

        return '<div class="tl_content_left" style="line-height:21px"><div style="float:left; margin-right:2px;">'.$thumbnail.'</div>'.$objFiles->name
               .'<span style="color:#b3b3b3;padding-left:3px">['.str_replace($objFiles->name, '', $objFiles->path).']</span></div>'."\n";
    }
}
