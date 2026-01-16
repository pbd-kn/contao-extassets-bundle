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

//use HeimrichHannot\Haste\Util\StringUtil;
use Contao\System;
use Contao\Frontend;
use Contao\LayoutModel;


//use Contao;


class ExtJs extends Frontend
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
    {   // wird als callback von extcss und extjs gerufen
        global $objPage;

        if (!$objPage) {
            return $strBuffer;
        }

        // zugehoeriges Layout zur Seite
        $objLayout = LayoutModel::findByPk($objPage->layout);

        if (!$objLayout) {
            return $strBuffer;
        }

        // the dynamic script replacement array
        $arrReplace = [];

        $this->parseExtJs($objLayout, $arrReplace);

        return $strBuffer;
    }

    public function getTwitterBootstrapjs()
    {

        // do not include more than once
        /*
        if (isset($arrJs['bootstrap'])) {
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'bootstrap js doppelt');
            return false;
        }
        */
        // im debugfall not .min.
        //$rootDir = \System::getContainer()->getParameter('kernel.project_dir');;
        //$in = $rootDir.'\\'.BOOTSTRAPDISTDIR.'js/bootstrap.bundle'.(!$GLOBALS['TL_CONFIG']['debugMode'] ? '.min' : '').'.js';
        //$in = '../vendor/pbd-kn/contao-extassets-bundle/src/Resources/contao/assets/bootstrap5/'.'js/bootstrap.bundle'.(!$GLOBALS['TL_CONFIG']['debugMode'] ? '.min' : '').'.js';
//die ("getTwitterBootstrapjs");
        $fn='bootstrap.bundle'.(!$GLOBALS['TL_CONFIG']['debugMode'] ? '.min' : '').'.js';
        $distnm = BOOTSTRAPDISTDIR.'js/'.$fn;
        $distobj = new \File($distnm);
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'bootstrap js distname '.$distobj->value);
        $ret='';
        if ($distobj->exists()) {           // bootstrap liegt in  vor
            $objOut = new \File(BOOTSTRAPJSDIR.$fn, true);   // File in asset des webzugriffs 
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'bootstrap js asset objOut '.$objOut->value);
            if (!$objOut->exists()) {
               $distobj->copyTo($objOut->value); // copy File to assets im web Bereich
               AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'copy js '.$distobj->value.' to '.$objOut->value);
            }
            $ret=$objOut->value;
        } else {
            \System::log('js/bootstrap.bundle.. js not in '.BOOTSTRAPDISTDIR.' please install twbs', __METHOD__, TL_ERROR);
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'not exist '.$distobj->path);
        }

        return $ret;
        /*
        $objFiledist = new \File($in);
        if ($objFiledist->exists()) {           // bootstrap liegt in  vor
        	array_insert($GLOBALS[$jsPosition], 1, array('bootstrap' => "$objFiledist->path|none"));
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, "bootstrap js insert in globals[$jsPosition] ".$objFiledist->path);
        } else {
            \System::log('js/bootstrap.bundle.. js not in '.BOOTSTRAPDISTDIR.' please install twbs', __METHOD__, TL_ERROR);
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'not exist '.$objFiledist->path);
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
        $GLOBALS[$jsPosition] = $arrJs;
        */
    }
    /*
     * objLayout pointer auf zugehoeriges Layout
     * 
     */

    protected function parseExtJs($objLayout, &$arrReplace)
    {
        $arrJs = [];
        $rootDir=System::getContainer()->getParameter('kernel.project_dir');
        // alle aktivierten Objekte aus dem Layout
        //$objJs = \PBDKN\ExtAssets\Resources\contao\models\ExtJsModel::findMultipleByIds(deserialize($objLayout->extjs));
        $objJs = \PBDKN\ExtAssets\Resources\contao\models\ExtJsModel::findMultipleByIds(unserialize($objLayout->extjs));
        if (null === $objJs) {
            // extjs ist in Layout nicht gesetzt
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'extjs nicht in Layouts enthalten');
            return false;
        }
        AssetsLog::setAssetDebugmode($objJs->setDebug);            // debug aus der ersten Gruppe
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'Layout extjs '.$objLayout->extjs);
        // position head oder body
        $cache = !$GLOBALS['TL_CONFIG']['debugMode'];
        // nochmals da der erste zugriff schon eine Gruppe weiter schaltet 
        //$objJs = \PBDKN\ExtAssets\Resources\contao\models\ExtJsModel::findMultipleByIds(deserialize($objLayout->extjs));
        $objJs = \PBDKN\ExtAssets\Resources\contao\models\ExtJsModel::findMultipleByIds(unserialize($objLayout->extjs));
//        $rewrite = false;
        $headCombiner = new \Contao\Combiner();
        $bodyCombiner = new \Contao\Combiner();
        $bootsTrapHead=false;
        $bootsTrapBody=false;
        while ($objJs->next()) {
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'Title: '.$objJs->title.' Position: '.$objJs->jsPosition);
            // muss das Asset-File neu geschrieben werden
            //$rewrite = ($objJs->tstamp > $objGroup->mtime || 0 === $objGroup->size || ($cache && 0 === $objGroupMinified->size));
            //$strrewrite = $rewrite ? 'true' : 'false';
            //AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'REWRITE: '.$strrewrite);
            
            // bootstrap nur einmal im Body hinzufuegen

            if ($objJs->addBootstrap) {
                if ($objJs->jsPosition == 'head') {
                    AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, '!!Boottrapjs NICHT im Header einfuegen');
                } else { 
                  if ($bootsTrapBody) {
                    AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'bootstrap Body js doppelt');
                  } else {
                    $bootstrapjs=$this->getTwitterBootstrapjs(); // default
                    AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'add Boottrapjs to body '.$bootstrapjs);
                    $bodyCombiner->add($bootstrapjs);
                    $bootsTrapBody=true;
                  }
                }
            }

            $objFiles = \PBDKN\ExtAssets\Resources\contao\models\ExtJsFileModel::findMultipleByPid($objJs->id);
            if ($objFiles) {
              while ($objFiles->next()) {            // ueber alle Gruppen
                $objFileModel = \FilesModel::findByPk($objFiles->src);
                if (null === $objFileModel || !file_exists($rootDir.'/'.$objFileModel->path)) {
                    continue;
                }
                $objFile = new \File($objFileModel->path);
                AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'add '.$objJs->jsPosition.' file '.$objFile->path);
                if ($objJs->jsPosition == 'head') $headCombiner->add($objFile->path);
                else $bodyCombiner->add($objFile->path);
              }
            }
        }
/*
        // HOOK: add custom css
        if (isset($GLOBALS['TL_HOOKS']['parseExtJs']) && \is_array($GLOBALS['TL_HOOKS']['parseExtJs'])) {
            foreach ($GLOBALS['TL_HOOKS']['parseExtJs'] as $callback) {
                AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, "hook ".$callback[0]);
                $arrJs = static::importStatic($callback[0])->{$callback[1]}($arrJs);
            }
        }
*/
//
        if ($headCombiner->hasEntries()) {
            $headFile=$headCombiner->getCombinedFile();
            $GLOBALS['TL_JAVASCRIPT'][]=$headFile;
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, "Add JS aus extjs im Header ".$headFile);
            foreach ($GLOBALS['TL_JAVASCRIPT'] as $k=>$v) {AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, "TL_JAVASCRIPT[$k]: $v");}
        } else {
          AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, "Keine JS aus extjs im Header");
        }
        foreach ($GLOBALS['TL_JAVASCRIPT'] as $k=>$v) {AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, "TL_JAVASCRIPT[$k]: $v");}
        if ($bodyCombiner->hasEntries()) {
          $bodyFile=$bodyCombiner->getCombinedFile();
          $GLOBALS['TL_BODY'][] = \Contao\Template::generateScriptTag($bodyFile, false, null);
          //$GLOBALS['TL_BODY'][]=$bodyFile;
          AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, "Add JS aus extjs im Body ".$bodyFile);
        } else {
          AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, "Keine JS aus extjs im Body");
        }
        foreach ($GLOBALS['TL_BODY'] as $k=>$v) {AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, "TL_BODY[$k]: $v");}
    }
}
