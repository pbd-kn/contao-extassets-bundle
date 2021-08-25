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

namespace PBDKN\ExtAssets\Resources\contao\classes;

require_once TL_ROOT.'/vendor/pbd-kn/contao-extassets-bundle/src/Resources/contao/classes/vendor/php_css_splitter/src/Splitter.php';

class ExtCssCombiner extends \Frontend
{
    public static $userCssKey = 'usercss';

    public static $bootstrapCssKey = 'bootstrap';

    public static $bootstrapPrintCssKey = 'bootstrap-print';

    public static $bootstrapResponsiveCssKey = 'bootstrap-responsive';

    public static $fontAwesomeCssKey = 'font-awesome';

    public $debug = false;

    protected $rewrite = false;

    protected $rewriteBootstrap = false;

    protected $arrData = [];

    protected $arrCss = [];

    protected $arrReturn = [];

    protected $mode = 'static';

    protected $variablesSrc;

    protected $objUserCssFile; // Target File of combined less output

    protected $uriRoot;

    protected $objLess;

    protected $arrLessOptions = [];

    protected $arrLessImportDirs = [];

    protected $cache = true;

    public function __construct(\Model\Collection $objCss, $arrReturn, $blnCache)
    {
        parent::__construct();

        $this->loadDataContainer('tl_extcss');
        while ($objCss->next()) {
            $this->arrData[] = $objCss->row();
        }
        AssetsLog::setAssetDebugmode($this->setDebug);
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'Contao VERSION '.VERSION.' BUILD '.BUILD.' blnCache '.$blnCache.' Debug '.$this->setDebug);
        $this->start = microtime(true);
        $this->cache = $blnCache;

        $this->variablesSrc = 'variables-'.$this->title.'.less';
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'this->variablesSrc '.$this->variablesSrc);
        $this->mode = $this->cache ? 'none' : 'static';

        $this->arrReturn = $arrReturn;

        $this->objUserCssFile = new \File($this->getSrc($this->title.'.css'));
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'this->objUserCssFile '.$this->getSrc($this->title.'.css'));

        if (!$this->objUserCssFile->exists()) {
            $this->objUserCssFile->write('');
            $this->objUserCssFile->close();
        }

        if (0 === $this->objUserCssFile->size || $this->lastUpdate > $this->objUserCssFile->mtime) {
            $this->rewrite = true;
            $this->rewriteBootstrap = true;
            $this->cache = false;
        }

        $this->uriRoot = (TL_ASSETS_URL ?: \Environment::get('url')).'/assets/css/';

        $this->arrLessOptions = [
            'compress' => !\Config::get('bypassCache'),
            'cache_dir' => TL_ROOT.'/assets/css/lesscache',
        ];

        if (!$this->cache) {
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'Kein cache vorhanden ');
            $this->objLess = new \Less_Parser($this->arrLessOptions);      // geparste Files werden in less zwischengespeichert

            $this->addBootstrapVariables();
            $this->addFontAwesomeVariables();
            $this->addFontAwesomeCore();
            $this->addFontAwesomeMixins();
            $this->addFontAwesome();
            if ($this->addingbootstrap) {             // add full bootstrap
                $this->addBootstrapMixins();
                $this->addBootstrapAlerts();
                $this->addBootstrap();
                $this->addBootstrapUtilities();
                $this->addBootstrapType();
            }

            if ($this->addElegantIcons) {
                $this->addElegantIconsVariables();
                $this->addElegantIcons();
            }

            // HOOK: add custom assets
            if (isset($GLOBALS['TL_HOOKS']['addCustomAssets']) && \is_array($GLOBALS['TL_HOOKS']['addCustomAssets'])) {
                foreach ($GLOBALS['TL_HOOKS']['addCustomAssets'] as $callback) {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($this->objLess, $this->arrData, $this);
                }
            }

            $this->addCustomLessFiles();

            $this->addCssFiles();
        } else {
            // remove custom less files as long as we can not provide mixins and variables in cache mode
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'Cache vorhanden ');
            unset($GLOBALS['TL_USER_CSS']);

            // always add bootstrap
            if ($this->addingbootstrap) {
                $this->addBootstrap();
            }
        }
    }

    public function __get($strKey)
    {
        switch ($strKey) {
            case 'title':
                return standardize(\StringUtil::restoreBasicEntities(implode('-', $this->getEach('title'))));
            case 'addBootstrapPrint':
            case 'addFontAwesome':
                return max($this->getEach($strKey));
            case 'addFontAwesome':
                return max($this->getEach($strKey));
            case 'addingbootstrap':
                return max($this->getEach($strKey));
            case 'setDebug':
                return max($this->getEach($strKey));
            case 'variablesSRC':
            case 'variablesOrderSRC':
                return $this->getEach($strKey);
            case 'ids':
                return $this->getEach('id'); // must be id
            case 'lastUpdate':
                return max($this->getEach('tstamp')); // return max tstamp from css groups
        }

        if (isset($this->arrData[$strKey])) {
            return $this->arrData[$strKey];
        }

        return parent::__get($strKey);
    }

    public function getUserCss()
    {
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'getUserCss ');
        $arrReturn = $this->arrReturn;

        $strCss = $this->objUserCssFile->getContent();
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'len strCss '.\count($strCss));

        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'rewrite '.$this->rewrite.' rewriteBootstrap '.$this->rewriteBootstrap);
        if (($this->rewrite || $this->rewriteBootstrap)) {
            try {
                $this->objLess->SetImportDirs($this->arrLessImportDirs);
                $strCss = $this->objLess->getCss();                           // aufgesammelte Werte im less Parser
                AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'objUserCssFile name '.$this->objUserCssFile->value.' less len strCss '.\count($strCss));
                $this->objUserCssFile->write($strCss);
                $this->objUserCssFile->close();
            } catch (\Exception $e) {
                echo '<pre>';
                echo $e->getMessage();
                echo '</pre>';
            }
        }
        //$splitter = new \CssSplitter\Splitter();
        $splitter = new \PBDKN\ExtAssets\Resources\contao\classes\vendor\php_css_splitter\src\Splitter();
        $count = $splitter->countSelectors($strCss);
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'splitter count '.$count);

        // IE 6 - 9 has a limit of 4096 selectors
        if ($count > 0) {
            $parts = ceil($count / 4095);
            for ($i = 1; $i <= $parts; ++$i) {
                $objFile = new \File("assets/css/$this->title-part-{$i}.css");
                $objFile->write($splitter->split($strCss, $i));
                $objFile->close();

                $arrReturn[self::$userCssKey][] = [
                    'src' => $objFile->value,
                    'type' => 'all', // 'all' is required by print media css
                    'mode' => '', // mustn't be static, otherwise contao will aggregate the files again (splitting not working)
                    'hash' => version_compare(VERSION, '3.4', '>=') ? $this->objUserCssFile->mtime : $this->objUserCssFile->hash,
                ];
            }
        } else {
            $arrReturn[self::$userCssKey][] = [
                'src' => $this->objUserCssFile->value,
                'type' => 'all', // 'all' is required by print media css
                'mode' => $this->mode,
                'hash' => version_compare(VERSION, '3.4', '>=') ? $this->objUserCssFile->mtime : $this->objUserCssFile->hash,
            ];
        }
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'ExtCssCombiner user less execution time: '.(microtime(true) - $this->start).' seconds');

        return $arrReturn;
    }

    public function getEach($strKey)
    {
        $return = [];

        foreach ($this->arrData as $key => $value) {
            $value = $value[$strKey];

            $varUnserialized = @unserialize($value);

            if (\is_array($varUnserialized)) {
                // flatten array
                $return = array_merge($return, $varUnserialized);
                continue;
            }

            $return[] = $value;
        }

        return $return;
    }

    protected function addCssFiles()
    {
        $objFiles = \PBDKN\ExtAssets\Resources\contao\models\ExtCssFileModel::findMultipleByPids($this->ids, ['order' => 'FIELD(pid, '.implode(',', $this->ids).'), sorting DESC']);

        if (null === $objFiles) {
            return false;
        }

        while ($objFiles->next()) {                     // alle lessfiles
            $objFileModel = \FilesModel::findByPk($objFiles->src);
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'src '.$objFiles->path);
            if (null === $objFileModel) {
                continue;
            }

            if (!file_exists(TL_ROOT.'/'.$objFileModel->path)) {
                continue;
            }

            $objFile = new \File($objFileModel->path);
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'objFileModel->path '.$objFileModel->path.' len: '.$objFile->size);

            if (0 === $objFile->size) {
                continue;
            }

            if ($this->isFileUpdated($objFile, $this->objUserCssFile)) {
                $this->rewrite = true;
            }

            $this->objLess->parseFile(TL_ROOT.'/'.$objFile->value);
        }
    }

    protected function addBootstrap(): void
    {
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'objFile bootstrap.less'.$this->getBootstrapSrc('bootstrap.less')); //assets/bootstrap/less/bootstrap.less
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'objTarget '.$this->getBootstrapCustomSrc('bootstrap-'.$this->title.'.less')); //assets/bootstrap/less/custom/bootstrap-pbdlessundcssfiles.less
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'objOut '.$this->getSrc('bootstrap-'.$this->title.'.css')); //assets/css/bootstrap-pbdlessundcssfiles.css
        $objFile = new \File($this->getBootstrapSrc('bootstrap.less'));
        $objTarget = new \File($this->getBootstrapCustomSrc('bootstrap-'.$this->title.'.less'));
        $objOut = new \File($this->getSrc('bootstrap-'.$this->title.'.css'), true);           // enthaelt das evtl. neu compilierte bootstra

        //$this->objLess->addImportDir($objFile->dirname);

        if ($this->rewriteBootstrap || !$objOut->exists()) {   // Bootstrap neu erzeugen          file title-bootstrap.
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'this->rewriteBootstrap '.$this->rewriteBootstrap.' file exist '.$objOut->value.' exist '.$objOut->exists());
            if ($objFile->exists()) {           // bootstrap liegt in less-form vor
                AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'this->rewriteBootstrap '.$this->rewriteBootstrap.'add content from '.$objFile->value);
                $strCss = $objFile->getContent();               // enthaelt wohl alle notwendigen imports

                $strCss = str_replace('@import "', '@import "../', $strCss);
                if (\is_array($this->variablesOrderSRC)) {             // eigene Variable einsetzen
                    $strCss = str_replace('../variables.less', $this->variablesSrc, $strCss);
                }

                // remove print
                if (!$this->addBootstrapPrint) {
                    $strCss = str_replace('@import "../print.less";', '//@import "../print.less";', $strCss);
                }

                $objTarget->write($strCss);
                $objTarget->close();

                $objParser = new \Less_Parser($this->arrLessOptions);                  // neuer Parser zum parsen von bootstrap
                AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'parse File '.$objTarget->value);
                $objParser->parseFile(TL_ROOT.'/'.$objTarget->value);

                $objOut = new \File($objOut->value);
                $objOut->write($objParser->getCss());
                $objOut->close();
                AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'bootstrap less neu compiliert '.$objOut->value);
            } else {  // check ob dist css min vorhanden ist
                $objFiledist = new \File($this->getBootstrapDist('css/bootstrap.min.css'));
                if ($objFiledist->exists()) {           // bootstrap liegt in less-form vor
                    AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'copy bootstrap dist from '.$objFiledist->value.' copy to '.$objTarget->value);
                    $objFiledist->copyTo($objOut->value);
                } else {
                    \System::log('bootstrap not in asset/bootstrap/less or '.BOOTSTRAPDISTDIR.' please install twbs', __METHOD__, TL_ERROR);
                    AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'bootstrap not in asset/bootstrap/less or '.BOOTSTRAPDISTDIR.' please install twbs');

                    return;
                }
            }
        } else {
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'bootstrap file exist '.$objOut->value.' exist '.$objOut->exists());
        }

        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'add bootstrap from '.$objFile->value); // in objFile ist das compilierte bootstrap file
        $this->arrReturn[self::$bootstrapCssKey][] = [
            'src' => $objOut->value,
            'type' => 'all', // 'all' is required for .hidden-print class, not 'screen'
            'mode' => $this->mode,
            'hash' => version_compare(VERSION, '3.4', '>=') ? $objOut->mtime : $objOut->hash,
        ];
    }

    /**
     * variables.less must not be changed
     * use custom bootstrapVariablesSRC to change variables.
     */
    protected function addBootstrapVariables(): void
    {
        $objFile = new \File($this->getBootstrapSrc('variables.less'));

        $strVariables = '';

        if ($objFile->size > 0) {
            $strVariables = $objFile->getContent();
        }
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, ' add Bootstrapvariables from '.$this->getBootstrapSrc('variables.less').' lng: '.\strlen($strVariables)); //assets/bootstrap/less/variables.less lng: 0

        if (!\is_array($this->variablesOrderSRC)) {
            return;
        }

        $objTarget = new \File($this->getBootstrapCustomSrc($this->variablesSrc));  // Zwischenspeicher fuer user less files
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, ' getBootstrapCustomSrc from (objTarget)'.$this->getBootstrapCustomSrc($this->variablesSrc).' lng: '.$objTarget->size); // assets/bootstrap/less/custom/variables-pbdlessundcssfiles.less lng: 29555

        // overwrite bootstrap variables with custom variables
        $objFilesModels = \FilesModel::findMultipleByUuids($this->variablesOrderSRC);

        if (null !== $objFilesModels) {
            while ($objFilesModels->next()) {
                $objFile = new \File($objFilesModels->path);
                //AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__,' fileContent from '.$objFilesModels->path.' lng: '.$objFile->size);//files/co4-rawFiles/themes/standard/bootstrap/myvariables.less lng: 29554
                $strContent = $objFile->getContent();

                if ($this->isFileUpdated($objFile, $objTarget)) {
                    AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'fileUpdated objFile->value '.$objFile->value.' objTarget->value '.$objTarget->value);
                    $this->rewrite = true;
                    $this->rewriteBootstrap = true;
                    if ($strContent) {
                        $strVariables .= "\n".$strContent;
                    }
                } else {
                    $strVariables .= "\n".$strContent;
                }
            }
        }

        if ($this->rewriteBootstrap) {
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'add bootstrapvariables rewriteBootstrap');
            $objTarget->write($strVariables);
            $objTarget->close();
        }

        $this->objLess->parse($strVariables);
    }

    /**
     * mixins.less must not be changed, no hash check.
     */
    protected function addBootstrapMixins(): void
    {
        $objFile = new \File($this->getBootstrapSrc('mixins.less'));
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, $this->getBootstrapSrc('mixins.less').' lng: '.$objFile->size);

        /* obsolet
                if (str_replace('v', '', BOOTSTRAPVERSION) >= '3.2.0') {
                    preg_match_all('/@import "(.*)";/', $objFile->getContent(), $arrImports);

                    if (\is_array($arrImports[1])) {
                        foreach ($arrImports[1] as $strFile) {
                            if (!file_exists(TL_ROOT.'/'.BOOTSTRAPLESSDIR.'/'.$strFile)) {
                                continue;
                            }

                            $objMixinFile = new \File(BOOTSTRAPLESSDIR.'/'.$strFile);
                            $this->objLess->parseFile(TL_ROOT.'/'.$objMixinFile->value);
                        }
                    }

                    return;
                }
        */
        if ($objFile->size > 0) {
            $this->objLess->parseFile(TL_ROOT.'/'.$objFile->value);
        }
    }

    /**
     * alerts.less must not be changed, no hash check.
     */
    protected function addBootstrapAlerts(): void
    {
        $objFile = new \File($this->getBootstrapSrc('alerts.less'));
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, $this->getBootstrapSrc('alerts.less').' lng: '.$objFile->size);

        if ($objFile->size > 0) {
            $this->objLess->parseFile(TL_ROOT.'/'.$objFile->value);
        }
    }

    protected function addBootstrapUtilities(): void
    {
        $arrUtilities = [
            'utilities.less',
            'responsive-utilities.less',
            'forms.less',
            'buttons.less',
            'alerts.less',
            'grid.less',
        ];

        foreach ($arrUtilities as $strFile) {
            $objFile = new \File($this->getBootstrapSrc($strFile));
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, $this->getBootstrapSrc($strFile).' lng: '.$objFile->size);

            if ($objFile->size > 0) {
                $this->objLess->parseFile(TL_ROOT.'/'.$objFile->value);
            }
        }
    }

    protected function addBootstrapType(): void
    {
        $objFile = new \File($this->getBootstrapSrc('type.less'));
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, $this->getBootstrapSrc('type.less').' lng: '.$objFile->size);

        if ($objFile->size > 0) {
            $this->objLess->parseFile(TL_ROOT.'/'.$objFile->value);
        }
    }

    protected function addFontAwesomeVariables(): void
    {
        $objFile = new \File($this->getFontAwesomeLessSrc('variables.less'));
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, ' add addFontAwesomeVariables from '.$this->getFontAwesomeLessSrc('variables.less').' lng: '.$objFile->size);

        if ($objFile->exists() && $objFile->size > 0) {
            $this->objLess->parseFile(TL_ROOT.'/'.$objFile->value);
        }
    }

    protected function addFontAwesomeCore(): void
    {
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, ' add addFontAwesomeCore from '.$this->getFontAwesomeLessSrc('core.less'));
        $objFile = new \File($this->getFontAwesomeLessSrc('core.less'));

        if ($objFile->exists() && $objFile->size > 0) {
            $this->objLess->parseFile(TL_ROOT.'/'.$objFile->value);
        }
    }

    protected function addFontAwesomeMixins(): void
    {
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, ' add addFontAwesomeMixins from '.$this->getFontAwesomeLessSrc('mixins.less'));
        $objFile = new \File($this->getFontAwesomeLessSrc('mixins.less'));

        if ($objFile->exists() && $objFile->size > 0) {
            $this->objLess->parseFile(TL_ROOT.'/'.$objFile->value);
        }
    }

    protected function addFontAwesome(): void
    {
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, ' add addFontAwesome from '.$this->getFontAwesomeCssSrc('font-awesome.less'));
        $objFile = new \File($this->getFontAwesomeCssSrc('font-awesome.css'), true);
        if ($objFile->exists() && $objFile->size > 0) {
            $strCss = $objFile->getContent();
            $strCss = str_replace('../fonts', '/'.rtrim(FONTAWESOMEFONTDIR, '/'), $strCss);
            $this->objLess->parse($strCss);
        }
    }

    protected function addElegantIconsVariables(): void
    {
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, ' add addElegantIconsVariables from '.$this->getElegentIconsLessSrc('variables.less'));
        $objFile = new \File($this->getElegentIconsLessSrc('variables.less'));

        if ($objFile->exists() && $objFile->size > 0) {
            $this->objLess->parseFile(TL_ROOT.'/'.$objFile->value);
        }
    }

    protected function addElegantIcons(): void
    {
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, ' add addElegantIcons from '.$this->getElegentIconsCssSrc('elegant-icons.less'));
        $objFile = new \File($this->getElegentIconsCssSrc('elegant-icons.css'), true);
        if ($objFile->exists() && $objFile->size > 0) {
            $strCss = $objFile->getContent();
            $strCss = str_replace('../fonts', '/'.rtrim(ELEGANTICONSFONTDIR, '/'), $strCss);
            $this->objLess->parse($strCss);
        }
    }

    protected function addCustomLessFiles()
    {
        if (!\is_array($GLOBALS['TL_USER_CSS']) || empty($GLOBALS['TL_USER_CSS'])) {
            return false;
        }

        foreach ($GLOBALS['TL_USER_CSS'] as $key => $css) {
            $arrCss = trimsplit('|', $css);

            $objFile = new \File($arrCss[0]);

            if ($this->isFileUpdated($objFile, $this->objUserCssFile)) {
                $this->rewrite = true;
            }

            $strContent = $objFile->getContent();

            // replace variables.less by custom variables.less
            $hasImports = preg_match_all('!@import(\s+)?(\'|")(.+)(\'|");!U', $strContent, $arrImport);

            if ($hasImports) {
                $this->arrLessImportDirs[$objFile->dirname] = $objFile->dirname;
            }

            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, ' add \'TL_USER_CSS\' addCustomLessFiles from '.$objFile->value);
            $this->objLess->parseFile(TL_ROOT.'/'.$objFile->value);  // muss da nicht TL_ROOT\    dazu ???

            unset($GLOBALS['TL_USER_CSS'][$key]);
        }
    }

    protected function isFileUpdated(\File $objFile, \File $objTarget)
    {
        return $objFile->size > 0 && ($this->rewrite || $this->rewriteBootstrap || $objFile->mtime > $objTarget->mtime);
    }

    protected function getSrc($src)
    {
        return CSSDIR.$src;
    }

    protected function getBootstrapSrc($src)
    {
        return BOOTSTRAPLESSDIR.$src;
    }

    protected function getBootstrapDist($src)
    {
        return BOOTSTRAPDISTDIR.$src;
    }

    protected function getBootstrapCustomSrc($src)
    {
        return BOOTSTRAPLESSCUSTOMDIR.$src;
    }

    protected function getFontAwesomeCssSrc($src)
    {
        return FONTAWESOMECSSDIR.$src;
    }

    protected function getFontAwesomeLessSrc($src)
    {
        return FONTAWESOMELESSDIR.$src;
    }

    protected function getFontAwesomeCustomSrc($src)
    {
        return FONTAWESOMELESSCUSTOMDIR.$src;
    }

    protected function getElegentIconsCssSrc($src)
    {
        return ELEGANTICONSCSSDIR.$src;
    }

    protected function getElegentIconsLessSrc($src)
    {
        return ELEGANTICONSLESSDIR.$src;
    }
}
