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

use Contao\Dbafs;

//require_once TL_ROOT.'/vendor/pbd-kn/contao-extassets-bundle/src/Resources/contao/classes/vendor/php_css_splitter/src/Splitter.php';
require_once \System::getContainer()->getParameter('kernel.project_dir').'/vendor/pbd-kn/contao-extassets-bundle/src/Resources/contao/classes/vendor/php_css_splitter/src/Splitter.php';

/**
 * Class ExtCss.
 *
 * @copyright  Heimrich & Hannot GmbH
 * @copyright Peter Broghammer 2021-
 */
class ExtCss extends \Frontend                      
{
    /**
     * If is in live mode.
     */
    protected $blnLiveMode = false;

    /**
     * Cached be login status.
     */
    protected $blnBeLoginStatus;

    /**
     * The variables cache.
     */
    protected $arrVariables;

    /**
     * Singleton.
     */
    private static $instance = null;

    /**
     * Get the singleton instance.
     *
     * @return \ExtAssets\ExtCSS
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();

            // remember cookie FE_PREVIEW state
            $fePreview = \Input::cookie('FE_PREVIEW');

            // set into preview mode
            \Input::setCookie('FE_PREVIEW', true);

            // request the BE_USER_AUTH login status
            static::setDesignerMode(self::$instance->getLoginStatus('BE_USER_AUTH'));

            // restore previous FE_PREVIEW state
            \Input::setCookie('FE_PREVIEW', $fePreview);
        }

        return self::$instance;
    }

    /**
     * Get productive mode status.
     */
    public static function isLiveMode()
    {
        return static::getInstance()->blnLiveMode ? true : false;
    }

    /**
     * Set productive mode.
     */
    public static function setLiveMode($liveMode = true): void
    {
        static::getInstance()->blnLiveMode = $liveMode;
    }

    /**
     * Get productive mode status.
     */
    public static function isDesignerMode()
    {
        return static::getInstance()->blnLiveMode ? false : true;
    }

    /**
     * Set designer mode.
     */
    public static function setDesignerMode($designerMode = true): void
    {
        static::getInstance()->blnLiveMode = !$designerMode;
    }

    public static function observeCssGroupFolder($groupId)
    {
        $objCss = \PBDKN\ExtAssets\Resources\contao\models\ExtCssModel::findByPk($groupId);

        if (null === $objCss || '' === $objCss->observeFolderSRC) {
            return false;
        }

        $objObserveModel = \FilesModel::findByUuid($objCss->observeFolderSRC);
        $rootDir=\System::getContainer()->getParameter('kernel.project_dir');

        if (null === $objObserveModel || !is_dir($rootDir.'/'.$objObserveModel->path)) {
            return false;
        }

        $lastUpdate = filemtime($rootDir.'/'.$objObserveModel->path);

        // check if folder content has updated
        if ($lastUpdate <= $objObserveModel->tstamp) {
            return false;
        }

        $objCssFiles = \PBDKN\ExtAssets\Resources\contao\models\ExtCssFileModel::findMultipleByPids([$groupId]);

        $arrOldFileNames = [];

        if (null !== $objCssFiles) {
            $objCssFilesModel = \FilesModel::findMultipleByUuids($objCssFiles->fetchEach('src'));

            if (null !== $objCssFilesModel) {
                $arrOldFileNames = $objCssFilesModel->fetchEach('path');
            }
        }

        $arrFileNames = static::scanLessFiles($objObserveModel->path);

        $arrDiff = array_diff($arrFileNames, $arrOldFileNames);

        // exclude bootstrap variables src
        $objVariablesModel = \FilesModel::findMultipleByUuids(deserialize($objCss->variablesOrderSRC, true));

        $arrRemove = [];

        if (null !== $objVariablesModel) {
            $arrVariables = $objVariablesModel->fetchEach('path');

            // remove variables from oberserve files
            $arrDiff = array_diff($arrDiff, $arrVariables);

            // remove variables from oberserve files
            $arrRemove = array_intersect($arrOldFileNames, $arrVariables);
        }

        if (!empty($arrDiff)) {
            // add new files
            foreach ($arrDiff as $key => $path) {
                static::addCssFileToGroup($path, $groupId);
            }
        }

        // cleanup
        $arrRemove = array_merge($arrRemove, array_diff($arrOldFileNames, $arrFileNames));

        if (!empty($arrRemove)) {
            // add new files
            foreach ($arrRemove as $key => $path) {
                // file is not part of the observed folder
                if (false === strpos($path, $objObserveModel->path)) {
                    continue;
                }

                static::removeCssFileFromGroup($path, $groupId);
            }
        }

        $objObserveModel->tstamp = $lastUpdate;
        $objObserveModel->save();

        return true;
    }

    /**
     * Add viewport if bootstrap responsive is enabled.
     *
     * @param PageModel   $objPage
     * @param LayoutModel $objLayout
     * @param PageRegular $objThis
     */
    public function hookGetPageLayout($objPage, &$objLayout, $objThis)
    {
        //AssetsLog::setAssetDebugmode(1); achtung wird erst im extcsscombiner gesetzt
        //AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'strBuffer '.$strBuffer);

        $objCss = \PBDKN\ExtAssets\Resources\contao\models\ExtCssModel::findMultipleBootstrapByIds(deserialize($objLayout->extcss));

        if (null === $objCss) {
            return false;
        }

        $blnXhtml = ('xhtml' === $objPage->outputFormat);

        $GLOBALS['TL_HEAD'][] = '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"'.($blnXhtml ? ' />' : '>')."\n";
    }

    /**
     * Update all Ext Css Files.
     *
     * @return bool
     */
    public function updateExtCss()
    {
        $objCss = \PBDKN\ExtAssets\Resources\contao\models\ExtCssModel::findAll();

        if (null === $objCss) {
            return false;
        }

        $arrReturn = [];

        while ($objCss->next()) {
            $combiner = new ExtCssCombiner($objCss->current(), $arrReturn);

            $arrReturn = $combiner->getUserCss();
        }
    }

    public function hookReplaceDynamicScriptTags($strBuffer)
    {
        // strbuffer enthält die aktuelle Seite

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

        $this->parseExtCss($objLayout, $arrReplace);

        return $strBuffer;
    }

    protected static function scanLessFiles($path, $arrReturn = [])
    {
        $rootDir=\System::getContainer()->getParameter('kernel.project_dir');

        $arrFileNames = scan($rootDir.'/'.$path);

        foreach ($arrFileNames as $key => $name) {
            $src = $path.'/'.$name;

            if (is_dir($rootDir.'/'.$src)) {
                array_insert($arrReturn, $key, static::scanLessFiles($src));
            } else {
                $arrReturn[] = $src;
            }
        }

        return $arrReturn;
    }

    protected static function removeCssFileFromGroup($path, $groupId)
    {
        $objFileModel = \FilesModel::findBy('path', $path);

        if (null === $objFileModel) {
            return false;
        }

        $objExtCssFileModel = \PBDKN\ExtAssets\Resources\contao\models\ExtCssFileModel::findBy('src', $objFileModel->uuid);

        if (null === $objExtCssFileModel) {
            return false;
        }

        $objExtCssFileModel->delete();

        return true;
    }

    protected static function addCssFileToGroup($path, $groupId)
    {
        // create Files Model
        $objFile = new \File($path);

        if (!\in_array(strtolower($objFile->extension), ['css', 'less'], true)) {
            return false;
        }

        $objFileModel = new \PBDKN\ExtAssets\Resources\contao\models\ExtCssFileModel();
        $objFileModel->pid = $groupId;
        $objFileModel->tstamp = time();

        $objNextSorting = \Database::getInstance()->prepare('SELECT MAX(sorting) AS sorting FROM tl_extcss_file WHERE pid=?')->execute($groupId);

        $objFileModel->sorting = ((int) ($objNextSorting->sorting) + 64);

        if (($objModel = $objFile->getModel()) === null) {
            $objModel = Dbafs::addResource($path);
        }

        $objFileModel->src = $objModel->uuid;

        $objFileModel->save();

        return $objFileModel;
    }

    protected function parseExtCss($objLayout, &$arrReplace)
    {
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'parseExtCss');
        $arrCss = [];

        $objCss = \PBDKN\ExtAssets\Resources\contao\models\ExtCssModel::findMultipleByIds(deserialize($objLayout->extcss));

        if (null === $objCss) {
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'objCss null '); //achtung wird erst im extcsscombiner gesetzt

            if (!\is_array($GLOBALS['TL_USER_CSS']) || empty($GLOBALS['TL_USER_CSS'])) {
                AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'no Usercss'); //achtung wird erst im extcsscombiner gesetzt

                return false;
            }

            // remove TL_USER_CSS less files, otherwise Contao Combiner fails
            foreach ($GLOBALS['TL_USER_CSS'] as $key => $css) {
                AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'remove ['.$key.']: '.$css); //achtung wird erst im extcsscombiner gesetzt
                $arrCss = trimsplit('|', $css);

                $extension = substr($arrCss[0], \strlen($arrCss[0]) - 4, \strlen($arrCss[0]));

                if ('less' === $extension) {
                    unset($GLOBALS['TL_USER_CSS'][$key]);
                }
            }

            return false;
        }

        $arrReturn = [];

        while ($objCss->next()) {
            static::observeCssGroupFolder($objCss->id);
        }

        $objCss->reset();

        $combiner = new ExtCssCombiner($objCss, $arrReturn, !$GLOBALS['TL_CONFIG']['bypassCache']);

        $arrReturn = $combiner->getUserCss();

        // HOOK: add custom css
        if (isset($GLOBALS['TL_HOOKS']['parseExtCss']) && \is_array($GLOBALS['TL_HOOKS']['parseExtCss'])) {
            foreach ($GLOBALS['TL_HOOKS']['parseExtCss'] as $callback) {
                $arrCss = static::importStatic($callback[0])->{$callback[1]}($arrCss);
            }
        }

        $arrBaseCss = []; // TL_CSS
        $arrUserCss = []; // TL_USER_CSS

        $static = true;

        // collect all usercss
        if (isset($arrReturn[ExtCssCombiner::$userCssKey]) && \is_array($arrReturn[ExtCssCombiner::$userCssKey])) {
            foreach ($arrReturn[ExtCssCombiner::$userCssKey] as $arrCss) {
                // if not static, css has been split, and bootstrap mustn't not be aggregated, otherwise
                // will be loaded after user css
                if ('static' !== $arrCss['mode']) {
                    $static = false;
                    // add hash to url, otherwise css file will still be cached
                    $arrCss['src'] .= '?'.$arrCss['hash'];
                }
                $str = sprintf('%s|%s|%s|%s', $arrCss['src'], $arrCss['type'], $arrCss['mode'], $arrCss['hash']);
                $arrUserCss[] = sprintf('%s|%s|%s|%s', $arrCss['src'], $arrCss['type'], $arrCss['mode'], $arrCss['hash']);
            }
        }

        // TODO: Refactor equal logic…
        // at first collect bootstrap to prevent overwrite of usercss
        if (isset($arrReturn[ExtCssCombiner::$bootstrapCssKey]) && \is_array($arrReturn[ExtCssCombiner::$bootstrapCssKey])) {
            $arrHashs = [];

            foreach ($arrReturn[ExtCssCombiner::$bootstrapCssKey] as $arrCss) {
                if (\in_array($arrCss['hash'], $arrHashs, true)) {
                    continue;
                }
                $str = sprintf('%s|%s|%s|%s', $arrCss['src'], $arrCss['type'], !$static ? $static : $arrCss['mode'], $arrCss['hash']);
                $arrBaseCss[] = sprintf('%s|%s|%s|%s', $arrCss['src'], $arrCss['type'], !$static ? $static : $arrCss['mode'], $arrCss['hash']);
                $arrHashs[] = $arrCss['hash'];
            }
        }

        // TODO: Refactor equal logic…
        // at first collect bootstrap to prevent overwrite of usercss
        if (isset($arrReturn[ExtCssCombiner::$bootstrapPrintCssKey]) && \is_array($arrReturn[ExtCssCombiner::$bootstrapPrintCssKey])) {
            $arrHashs = [];

            foreach ($arrReturn[ExtCssCombiner::$bootstrapPrintCssKey] as $arrCss) {
                if (\in_array($arrCss['hash'], $arrHashs, true)) {
                    continue;
                }
                $arrBaseCss[] = sprintf('%s|%s|%s|%s', $arrCss['src'], $arrCss['type'], !$static ? $static : $arrCss['mode'], $arrCss['hash']);
                $arrHashs[] = $arrCss['hash'];
            }
        }
        // TODO: Refactor equal logic…
        // at first collect bootstrap to prevent overwrite of usercss
        if (isset($arrReturn[ExtCssCombiner::$fontAwesomeCssKey]) && \is_array($arrReturn[ExtCssCombiner::$fontAwesomeCssKey])) {
            $arrHashs = [];
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'awesome staticx |'.$static.'|');
            foreach ($arrReturn[ExtCssCombiner::$fontAwesomeCssKey] as $arrCss) {
                if (\in_array($arrCss['hash'], $arrHashs, true)) {
                    continue;
                }
                $arrBaseCss[] = sprintf('%s|%s|%s|%s', $arrCss['src'], $arrCss['type'], !$static ? $static : $arrCss['mode'], $arrCss['hash']);
                $arrHashs[] = $arrCss['hash'];
            }
        }

        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'arrBaseCss len '.count($arrBaseCss));
        //$GLOBALS['TL_CSS'] = array_merge(\is_array($GLOBALS['TL_CSS']) ? $GLOBALS['TL_CSS'] : [], $arrBaseCss);

        $GLOBALS['TL_CSS'] = array_merge(!empty($GLOBALS['TL_CSS']) && \is_array($GLOBALS['TL_CSS']) ? $GLOBALS['TL_CSS'] : [], $arrBaseCss);
        foreach ($GLOBALS['TL_CSS'] as $k => $v) {
          AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, "GLOBALS['TL_CSS'][$k]: [$v]");
        }

        $GLOBALS['TL_USER_CSS'] = array_merge(!empty($GLOBALS['TL_USER_CSS'])&&\is_array($GLOBALS['TL_USER_CSS']) ? $GLOBALS['TL_USER_CSS'] : [], $arrUserCss);
        foreach ($GLOBALS['TL_USER_CSS'] as $k => $v) {
          AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, "GLOBALS['TL_USER_CSS'][$k]: [$v]");
        }
    }
     
}
