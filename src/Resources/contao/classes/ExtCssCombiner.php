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
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'Kein cache vorhanden neu aufbauen');
            $this->objLess = new \Less_Parser($this->arrLessOptions);      // geparste Files werden in less zwischengespeichert

            $this->addBootstrapVariables();        // parse it to objLess
            if ($this->addbootstrap) {             // add full bootstrap  copy from twbs
              $objOut = new \File($this->getBootstrapCss('bootstrap.min.css'), true);    // assets/bootstrap/css
              $objFiledist = new \File($this->getBootstrapDist('css/bootstrap.min.css'));
              if ($objFiledist->exists()) {           // bootstrap liegt in min.css vor
                AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'copy bootstrap dist from '.$objFiledist->value.' copy to '.$objOut->value);
                $objFiledist->copyTo($objOut->value);
                $this->addBootstrap();  //add bootstrap css
              } else {
                \System::log('bootstrap not in  '.$this->getBootstrapDist('css/bootstrap.min.css').' please install twbs', __METHOD__, TL_ERROR);
                AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'bootstrap not in  '.$this->getBootstrapDist('css/bootstrap.min.css').' please install twbs');
              }
            }
            if ($this->addFontAwesome) {             // add full awesome  copy from vendor assets
              $awecssFile=$this->getFontAwesomeCssSrc('font-awesome.min.css');
              $aweFontDir=$this->getFontAwesomeFontSrc('');
              $objOut = new \File($awecssFile, true);
              if (!$objOut->exists()) {
                AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'awecssFile not exist '); 
                \System::log('install default font-awesome.min.css 4.7 to '.TL_ROOT.'/'.FONTAWESOMEDIR.'css', __METHOD__,TL_GENERAL);
                $src='vendor/pbd-kn/contao-extassets-bundle/src/Resources/contao/assets/font-awesome/css/font-awesome.min.css';
                $target=FONTAWESOMEDIR.'css/font-awesome.min.css';
                $awefile= new \File($src,true);
                $awefile->copyTo($target);
              } else {
                AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'awecssFile dir exist '); 
              }
              $objOut = new \File($aweFontDir.'./.', true);             // check datei in fonts
              if (!$objOut->exists()) {
                $src='vendor/pbd-kn/contao-extassets-bundle/src/Resources/contao/assets/font-awesome/fonts';
                $fontFiles = scan(TL_ROOT.'/'.$src, true);
                AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'scan '.TL_ROOT.'/'.$src.' count '.count($fontFiles), __METHOD__,TL_ERROR);
                foreach ($fontFiles as $strFile) {
                  $srcf=$src.$strFile;
                  $targetf=FONTAWESOMEDIR.'fonts/'.$strFile;
                  $awefile= new \File($srcf,true);
                  $awefile->copyTo($targetf);                    
                }
              } else {
                AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'fonts dir exist '); 
              }
              $this->addFontAwesome();
            }
            // HOOK: add custom asset
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

            if ($this->addbootstrap) {
                $this->addBootstrap();  //add bootstrap css
            } else {
                \System::log('bootstrap not selected', __METHOD__, TL_GENERAL);
            }
		    if($this->addFontAwesome)
		    {
			  $this->addFontAwesome();
            } else {
                \System::log('fontawesome not selected', __METHOD__, TL_GENERAL);
            }
        }
    }

    public function __get($strKey)
    {
        switch ($strKey) {
            case 'title':
                return standardize(\StringUtil::restoreBasicEntities(implode('-', $this->getEach('title'))));
            case 'addBootstrapPrint':
                return max($this->getEach($strKey));
            case 'addFontAwesome':
                //return 0;
                return max($this->getEach($strKey));
            case 'addbootstrap':
                return max($this->getEach($strKey));
            case 'setDebug':
                $arr= $this->getEach($strKey);
                if (count($arr)==0) return 0;               // debug wurde noch nie gesetzt
                return max($this->getEach($strKey));
            case 'variablesSRC':
            case 'variablesOrderSRC':                       // das ist Geschichte hieß frueher so
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
    
    /*
     * get all parsed files
     */

    public function getUserCss()
    {
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'getUserCss ');
        $arrReturn = $this->arrReturn;

        $strCss = $this->objUserCssFile->getContent();
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'strCss path '.$this->objUserCssFile->path);
        if ($strCss) {
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'len strCss '.\strlen($strCss));
        }

        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'rewrite '.$this->rewrite.' rewriteBootstrap '.$this->rewriteBootstrap);
        if (($this->rewrite || $this->rewriteBootstrap)) {
            try {
                $this->objLess->SetImportDirs($this->arrLessImportDirs);
                $strCss = $this->objLess->getCss();                           // aufgesammelte Werte im less Parser
                AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'objUserCssFile name '.$this->objUserCssFile->value.' less len strCss '.\strlen($strCss));
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
            if (empty($value)) {
                continue;
            }
            $varUnserialized = @unserialize((string)$value);

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
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'add less files and parse it ');

        while ($objFiles->next()) {                     // alle lessfiles
            $objFileModel = \FilesModel::findByPk($objFiles->src);
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

        $objOut = new \File($this->getBootstrapCss('bootstrap.min.css'), true);    
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'objOut '.$objOut->value); 

        if (!$objOut->exists()) {
            \System::log('bootstrap not in  '.$this->getBootstrapDist('css/bootstrap.min.css').' please install twbs', __METHOD__, TL_ERROR);
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'bootstrap not in  '.$this->getBootstrapDist('css/bootstrap.min.css').' please install twbs');
            return;
        }

        $this->arrReturn[self::$bootstrapCssKey][] = [              // css-file fuer return merken
            'src' => $objOut->value,
            'type' => 'all', // 'all' is required for .hidden-print class, not 'screen'
            'mode' => $this->mode,
            'hash' => version_compare(VERSION, '3.4', '>=') ? $objOut->mtime : $objOut->hash,
        ];
    }

    /**
     * variables.less must not be changed
     * use custom bootstrapVariablesSRC to change variables.
     * ubernimmt den Inhalt von den angegebenen Variablen ->
     */
    protected function addBootstrapVariables(): void
    {
        $objFile = new \File($this->getBootstrapSrc('variables.less'));  // assets/bootstrap/less/variables.less  gibts wohl nicht mehr

        $strVariables = '';            // aufgesammelte Variablen

        if ($objFile->exists() && $objFile->size > 0) {
            $strVariables = $objFile->getContent();
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, ' add Bootstrapvariables from '.$this->getBootstrapSrc('variables.less').' lng: '.\strlen($strVariables)); //assets/bootstrap/less/variables.less lng: 0
        }

        if (!\is_array($this->variablesSRC)) {    // der getter macht au dem Input ein array
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, ' this->variablesSRC kein ARRAY'); //assets/bootstrap/less/variables.less lng: 0
            return;
        }

        $objTarget = new \File($this->getBootstrapCustomSrc($this->variablesSrc));  // assets/bootstrap/less/custom/variables-twitter-bootstrap.less
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, ' getBootstrapCustomSrc to (objTarget) '.$this->getBootstrapCustomSrc($this->variablesSrc)); // assets/bootstrap/less/custom/variables-pbdlessundcssfiles.less lng: 29555
        // $this->variablesSrc = 'variables-'.$this->title.'.less' title leerzeichen werden im getter durch - ersetzt

        // overwrite bootstrap variables with custom variables
        // lies die variables
        $objFilesModels = \FilesModel::findMultipleByUuids($this->variablesSRC);  // es können mehrere Variablenfiles angegeben werden s. dca
                                                                                  // Reihenfolge ??

        if (null !== $objFilesModels) {
            while ($objFilesModels->next()) {
                $objFile = new \File($objFilesModels->path);
                AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__,' fileContent from '.$objFilesModels->path.' lng: '.$objFile->size);//files/co4-rawFiles/themes/standard/bootstrap/myvariables.less lng: 29554
                $strContent = $objFile->getContent();

                if ($this->isFileUpdated($objFile, $objTarget)) {
                    AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'fileUpdated objFile->value '.$objFile->value.' objTarget->value '.$objTarget->value);
                    $this->rewrite = true;
                    $this->rewriteBootstrap = true;
                    if ($strContent) {
                        $strVariables .= "\n".$strContent;
                    }
                } else {
                    AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'NO fileUpdated objFile->value '.$objFile->value);
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


    protected function addFontAwesome(): void
    {
      $awecssFile=$this->getFontAwesomeCssSrc('font-awesome.min.css');
      AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'awecssFile '.$this->getFontAwesomeCssSrc('font-awesome.min.css')); 
      $objOut = new \File($awecssFile, true);
      if (!$objOut->exists()) {
            \System::log('fontawesome not in  '.$this->getFontAwesomeCssSrc('font-awesome.min.css').' please install purge less files', __METHOD__, TL_ERROR);
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'fontsawesome not in  '.$this->getFontAwesomeCssSrc('font-awesome.min.css').' please install twbs');
            return;
      }
      $this->arrReturn[self::$fontAwesomeCssKey][] = [              // css-file fuer return merken
            'src' => $objOut->value,
            'type' => 'all', // 'all' is required for .hidden-print class, not 'screen'
            'mode' => $this->mode,
            'hash' => version_compare(VERSION, '3.4', '>=') ? $objOut->mtime : $objOut->hash,
      ];

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
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'addCustomLessFiles ');

        if (!\is_array($GLOBALS['TL_USER_CSS']) || empty($GLOBALS['TL_USER_CSS'])) {
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'keine TL_USER_CSS ');
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
        return '/bootstrap/less/'.$src;
    }

    protected function getBootstrapCss($src)
    {
        return BOOTSTRAPCSSDIR.$src;
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
    protected function getFontAwesomeFontSrc($src)
    {
        return FONTAWESOMEFONTDIR.$src;
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
