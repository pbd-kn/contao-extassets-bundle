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

/**
 * Namespace.
 */

namespace PBDKN\ExtAssets\Resources\contao\classes;

use HeimrichHannot\Haste\Util\StringUtil;

class ExtJs extends \Frontend
{
    /**
     * Singleton.
     */
    private static $instance = null;

    /**
     * Get the singleton instance.
     *
     * @return \ExtJs\ExtJs
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function hookReplaceDynamicScriptTags($strBuffer)
    {
        global $objPage;

        if (!$objPage) {
            return $strBuffer;
        }

        $objLayout = \LayoutModel::findByPk($objPage->layout);

        if (!$objLayout) {
            return $strBuffer;
        }

        // the dynamic script replacement array
        $arrReplace = [];

        $this->parseExtJs($objLayout, $arrReplace);

        return $strBuffer;
    }

    public function addTwitterBootstrap()
    {
        $arrJs = $GLOBALS['TL_JAVASCRIPT'];

        // do not include more than once
        if (isset($arrJs['bootstrap'])) {
            return false;
        }

        $in = BOOTSTRAPJSDIR.'bootstrap'.(!$GLOBALS['TL_CONFIG']['debugMode'] ? '.min' : '').'.js';

        if (!file_exists(TL_ROOT.'/'.$in)) {
            return false;
        }

        $intJqueryIndex = 0;
        $i = 0;

        // detemine jquery index from file name, as we should append bootstrapper always after jquery
        foreach ($arrJs as $index => $strFile) {
            if (!StringUtil::endsWith($strFile, 'jquery.min.js|static')) {
                ++$i;
                continue;
            }

            $intJqueryIndex = $i + 1;
            break;
        }

        array_insert($arrJs, $intJqueryIndex, ['bootstrap' => $in.(!$GLOBALS['TL_CONFIG']['debugMode'] ? '|static' : '')]);
        $GLOBALS['TL_JAVASCRIPT'] = $arrJs;
    }

    protected function parseExtJs($objLayout, &$arrReplace)
    {
        $arrJs = [];

        $objJs = \PBDKN\ExtAssets\Resources\contao\models\ExtJsModel::findMultipleByIds(deserialize($objLayout->extjs));

        if (null === $objJs) {
            return false;
        }

        $cache = !$GLOBALS['TL_CONFIG']['debugMode'];

        while ($objJs->next()) {
            $objFiles = \PBDKN\ExtAssets\Resources\contao\models\ExtJsFileModel::findMultipleByPid($objJs->id);

            if (null === $objFiles) {
                continue;
            }
            $strChunk = '';

            $strFile = 'assets/js/'.$objJs->title.'.js';
            $strFileMinified = str_replace('.js', '.min.js', $strFile);

            $objGroup = new \File($strFile, file_exists(TL_ROOT.'/'.$strFile));
            $objGroupMinified = new \File($strFileMinified, file_exists(TL_ROOT.'/'.$strFile));

            if (!$objGroupMinified->exists()) {
                $objGroupMinified->write('');
                $objGroupMinified->close();
            }

            if (!$objGroup->exists()) {
                $objGroup->write('');
                $objGroup->close();
            }

            $rewrite = ($objJs->tstamp > $objGroup->mtime || 0 === $objGroup->size || ($cache && 0 === $objGroupMinified->size));

            while ($objFiles->next()) {
                $objFileModel = \FilesModel::findByPk($objFiles->src);

                if (null === $objFileModel || !file_exists(TL_ROOT.'/'.$objFileModel->path)) {
                    continue;
                }
                $objFile = new \File($objFileModel->path);

                $strChunk .= $objFile->getContent()."\n";

                if ($objFile->mtime > $objGroup->mtime) {
                    $rewrite = true;
                }
            }

            // simple file caching
            if ($rewrite) {
                $objGroup->write($strChunk);
                $objGroup->close();

                // minify js
                if ($cache) {
                    $objGroup = new \File($strFileMinified);
                    $objMinify = new \MatthiasMullie\Minify\JS();
                    $objMinify->add($strChunk);
                    $objGroup->write(rtrim($objMinify->minify(), ';').';'); // append semicolon, otherwise "(intermediate value)(...) is not a function"
                    $objGroup->close();
                }
            }

            $arrJs[] = $cache ? ("$strFileMinified|static") : "$strFile";
        }

        // HOOK: add custom css
        if (isset($GLOBALS['TL_HOOKS']['parseExtJs']) && \is_array($GLOBALS['TL_HOOKS']['parseExtJs'])) {
            foreach ($GLOBALS['TL_HOOKS']['parseExtJs'] as $callback) {
                $arrJs = static::importStatic($callback[0])->{$callback[1]}($arrJs);
            }
        }

        if ($objJs->addBootstrap) {
            $this->addTwitterBootstrap();
        }

        // inject extjs before other plugins, otherwise bootstrap may not work
        $GLOBALS['TL_JAVASCRIPT'] = \is_array($GLOBALS['TL_JAVASCRIPT']) ? array_merge($GLOBALS['TL_JAVASCRIPT'], $arrJs) : $arrJs;
    }
}
