<?php

namespace PBDKN\ExtAssets\Resources\contao\classes;

require_once TL_ROOT . "/vendor/pbd-kn/contao-extassets-bundle/src/Resources/contao/classes/vendor/php_css_splitter/src/Splitter.php";

class ExtCssCombiner extends \Frontend
{

    protected $rewrite = false;

    protected $rewriteBootstrap = false;

    protected $arrData = [];

    protected $arrCss = [];

    protected $arrReturn = [];

    protected $mode = 'static';

    protected $variablesSrc;

    public static $userCssKey = 'usercss';

    public static $bootstrapCssKey = 'bootstrap';

    public static $bootstrapPrintCssKey = 'bootstrap-print';

    public static $bootstrapResponsiveCssKey = 'bootstrap-responsive';

    public static $fontAwesomeCssKey = 'font-awesome';

    protected $objUserCssFile; // Target File of combined less output

    protected $uriRoot;

    public $debug = false;

    protected $objLess;

    protected $arrLessOptions = [];

    protected $arrLessImportDirs = [];

    protected $cache = true;

    public function __construct(\Model\Collection $objCss, $arrReturn = [], $blnCache)
    {
        parent::__construct();
        AssetsLog::setAssetDebugmode(1);
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'Contao VERSION '.VERSION.' BUILD '.BUILD.' blnCache '.$blnCache);

        $this->start = microtime(true);
        $this->cache = $blnCache;

        $this->loadDataContainer('tl_extcss');

        while ($objCss->next())
        {
            $this->arrData[] = $objCss->row();
        }

        $this->variablesSrc = 'variables-' . $this->title . '.less';
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'this->variablesSrc '.$this->variablesSrc);  // variables-pbdlessundcssfiles.less

        $this->mode = $this->cache ? 'none' : 'static';

        $this->arrReturn = $arrReturn;

        $this->objUserCssFile = new \File($this->getSrc($this->title . '.css'));
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'this->objUserCssFile '.$this->getSrc($this->title . '.css')); //assets/css/pbdlessundcssfiles.css

        if(!$this->objUserCssFile->exists())
        {
            $this->objUserCssFile->write('');
            $this->objUserCssFile->close();
        }

        if($this->objUserCssFile->size == 0 || $this->lastUpdate > $this->objUserCssFile->mtime)
        {
            $this->rewrite          = true;
            $this->rewriteBootstrap = true;
            $this->cache            = false;
        }

        $this->uriRoot = (TL_ASSETS_URL ? TL_ASSETS_URL : \Environment::get('url')) . '/assets/css/';

        $this->arrLessOptions = [
            'compress'  => !\Config::get('bypassCache'),
            'cache_dir' => TL_ROOT . '/assets/css/lesscache',

        ];


        if (!$this->cache)
        {
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'Kein cache vorhanden ');
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'addingbootstrap  '.$this->addingbootstrap);
            $this->objLess = new \Less_Parser($this->arrLessOptions);      // geparste Files werden in less zwischengespeichert

            $this->addBootstrapVariables();
            $this->addFontAwesomeVariables();
            $this->addFontAwesomeCore();
            $this->addFontAwesomeMixins();
            $this->addFontAwesome();
            if ($this->addingbootstrap) {
              $this->addBootstrapMixins();
              $this->addBootstrapAlerts();
              $this->addBootstrap();
              $this->addBootstrapUtilities();
              $this->addBootstrapType();
            }

            if ($this->addElegantIcons)
            {
                $this->addElegantIconsVariables();
                $this->addElegantIcons();
            }

            // HOOK: add custom assets
            if (isset($GLOBALS['TL_HOOKS']['addCustomAssets']) && is_array($GLOBALS['TL_HOOKS']['addCustomAssets']))
            {
                foreach ($GLOBALS['TL_HOOKS']['addCustomAssets'] as $callback)
                {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($this->objLess, $this->arrData, $this);
                }
            }

            $this->addCustomLessFiles();

            $this->addCssFiles();
        }
        else
        {
            // remove custom less files as long as we can not provide mixins and variables in cache mode
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'Cache vorhanden ');
            unset($GLOBALS['TL_USER_CSS']);

            // always add bootstrap
            if ($this->addingbootstrap) {
              $this->addBootstrap();  
            }
        }
    }

    public function getUserCss()
    {
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'getUserCss ');
        $arrReturn = $this->arrReturn;

        $strCss = $this->objUserCssFile->getContent();
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'len strCss '.count($strCss));

        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'rewrite '.$this->rewrite.' rewriteBootstrap '.$this->rewriteBootstrap);
        if (($this->rewrite || $this->rewriteBootstrap))
        {
            try
            {
                $this->objLess->SetImportDirs($this->arrLessImportDirs);
                $strCss = $this->objLess->getCss();                           // aufgesammelte Werte im less Parser
                AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'objUserCssFile name '.$this->objUserCssFile->value.' less len strCss '.count($strCss));
                $this->objUserCssFile->write($strCss);
                $this->objUserCssFile->close();
            } catch (\Exception $e)
            {
                echo '<pre>';
                echo $e->getMessage();
                echo '</pre>';
            }
        }
        //$splitter = new \CssSplitter\Splitter();
        $splitter = new \PBDKN\ExtAssets\Resources\contao\classes\vendor\php_css_splitter\src\Splitter();
        $count    = $splitter->countSelectors($strCss);
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'splitter count '.$count);

        // IE 6 - 9 has a limit of 4096 selectors
        if ($count > 0)
        {
            $parts = ceil($count / 4095);
            for ($i = 1; $i <= $parts; $i++)
            {
                $objFile = new \File("assets/css/$this->title-part-{$i}.css");
                $objFile->write($splitter->split($strCss, $i));
                $objFile->close();

                $arrReturn[self::$userCssKey][] = [
                    'src'  => $objFile->value,
                    'type' => 'all', // 'all' is required by print media css
                    'mode' => '', // mustn't be static, otherwise contao will aggregate the files again (splitting not working)
                    'hash' => version_compare(VERSION, '3.4', '>=') ? $this->objUserCssFile->mtime : $this->objUserCssFile->hash,
                ];
            }

        }
        else
        {
            $arrReturn[self::$userCssKey][] = [
                'src'  => $this->objUserCssFile->value,
                'type' => 'all', // 'all' is required by print media css
                'mode' => $this->mode,
                'hash' => version_compare(VERSION, '3.4', '>=') ? $this->objUserCssFile->mtime : $this->objUserCssFile->hash,
            ];
        }

        if ($this->debug)
        {
            print '<pre>';
            print_r('ExtCssCombiner execution time: ' . (microtime(true) - $this->start) . ' seconds');
            print '</pre>';
        }

        return $arrReturn;
    }


    protected function addCssFiles()
    {
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, '-> ');

        $objFiles = \PBDKN\ExtAssets\Resources\contao\models\ExtCssFileModel::findMultipleByPids($this->ids, ['order' => 'FIELD(pid, ' . implode(",", $this->ids) . '), sorting DESC']);

        if ($objFiles === null)
        {
            return false;
        }

        while ($objFiles->next())                     // alle lessfiles
        {
            $objFileModel = \FilesModel::findByPk($objFiles->src);
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'src '.$objFiles->path);
            if ($objFileModel === null)
            {
                continue;
            }

            if (!file_exists(TL_ROOT . '/' . $objFileModel->path))
            {
                continue;
            }

            $objFile = new \File($objFileModel->path);
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'objFileModel->path '.$objFileModel->path.' len: '.$objFile->size);

            if ($objFile->size == 0)
            {
                continue;
            }

            if ($this->isFileUpdated($objFile, $this->objUserCssFile))
            {
                $this->rewrite = true;
            }

            $this->objLess->parseFile(TL_ROOT . '/' . $objFile->value);
        }
    }

    protected function addBootstrap()
    {
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__,'objFile bootstrap.less'.$this->getBootstrapSrc('bootstrap.less')); //assets/bootstrap/less/bootstrap.less
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__,'objTarget '.$this->getBootstrapCustomSrc('bootstrap-' . $this->title . '.less')); //assets/bootstrap/less/custom/bootstrap-pbdlessundcssfiles.less
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__,'objOut '.$this->getSrc('bootstrap-' . $this->title . '.css')); //assets/css/bootstrap-pbdlessundcssfiles.css
        $objFile   = new \File($this->getBootstrapSrc('bootstrap.less'));
        $objTarget = new \File($this->getBootstrapCustomSrc('bootstrap-' . $this->title . '.less'));
        $objOut    = new \File($this->getSrc('bootstrap-' . $this->title . '.css'), true);

        //$this->objLess->addImportDir($objFile->dirname);

        if ($this->rewriteBootstrap || !$objOut->exists())
        {   // Bootstrap neu erzeugen          file title-bootstrap.
          AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'this->rewriteBootstrap '.$this->rewriteBootstrap.' file exist ?? '.$objFile->exists());
          if ($objFile->exists())           // bootstrap liegt in less-form vor
          {
              AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'this->rewriteBootstrap '.$this->rewriteBootstrap);
              AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'add content from '.$objFile->value);
              $strCss = $objFile->getContent();               // enthaelt wohl alle notwendigen imports

              $strCss = str_replace('@import "', '@import "../', $strCss);
              if (is_array($this->variablesOrderSRC))
              {             // eigene Variable einsetzen
                $strCss = str_replace('../variables.less', $this->variablesSrc, $strCss);
              }

              // remove print
              if (!$this->addBootstrapPrint)
              {
                $strCss = str_replace('@import "../print.less";', '//@import "../print.less";', $strCss);
              }

              $objTarget->write($strCss);
              $objTarget->close();

              $objParser = new \Less_Parser($this->arrLessOptions);                  // neuer Parser zum parsen von bootstrap
              AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'parse File '.$objTarget->value);
              $objParser->parseFile(TL_ROOT . '/' . $objTarget->value);

              $objOut = new \File($objOut->value);
              AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'objOut Neu '.$objOut->value);
              $objOut->write($objParser->getCss());
              $objOut->close();
              AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'add bootstrap from '.$objFile->value); //assets/bootstrap/less/bootstrap.less
              $this->arrReturn[self::$bootstrapCssKey][] = [
                  'src'  => $objOut->value,
                  'type' => 'all', // 'all' is required for .hidden-print class, not 'screen'
                  'mode' => $this->mode,
                  'hash' => version_compare(VERSION, '3.4', '>=') ? $objOut->mtime : $objOut->hash,
              ];
          } else {  // check ob dist css min vorhanden ist
              AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__,'distFile check '); 
              $objFile   = new \File($this->getBootstrapDist('css/bootstrap.min.css'));
              AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__,'distFile '.$this->getBootstrapDist('css/bootstrap.min.css')); 
              if ($objFile->exists())           // bootstrap liegt in less-form vor
              {
                AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'add bootstrap dist from '.$objFile->value);
                $this->arrReturn[self::$bootstrapCssKey][] = [
                  'src'  => $objFile->value,
                  'type' => 'all', // 'all' is required for .hidden-print class, not 'screen'
                  'mode' => $this->mode,
                  'hash' => version_compare(VERSION, '3.4', '>=') ? $objFile->mtime : $objFile->hash,
                ];
              } else {
                \System::log('bootstrap not in asset/bootstrap/less or '.BOOTSTRAPDISTDIR.' please install twbs', __METHOD__, TL_ERROR);
                AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'bootstrap not in asset/bootstrap/less or '.BOOTSTRAPDISTDIR.' please install twbs');
              }
          }  
        }
              AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__,'----------------------------------------'); 

    }

    /**
     * variables.less must not be changed
     * use custom bootstrapVariablesSRC to change variables
     */
    protected function addBootstrapVariables()
    {
        $objFile = new \File($this->getBootstrapSrc('variables.less'));

        $strVariables = '';

        if ($objFile->size > 0)
        {
            $strVariables = $objFile->getContent();
        }
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__,' add Bootstrapvariables from '.$this->getBootstrapSrc('variables.less').' lng: '.strlen($strVariables)); //assets/bootstrap/less/variables.less lng: 0

        if (!is_array($this->variablesOrderSRC))
        {
            return;
        }

        $objTarget = new \File($this->getBootstrapCustomSrc($this->variablesSrc));
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__,' getBootstrapCustomSrc from (objTarget)'.$this->getBootstrapCustomSrc($this->variablesSrc).' lng: '.$objTarget->size); // assets/bootstrap/less/custom/variables-pbdlessundcssfiles.less lng: 29555

        // overwrite bootstrap variables with custom variables
        $objFilesModels = \FilesModel::findMultipleByUuids($this->variablesOrderSRC);

        if ($objFilesModels !== null)
        {
            while ($objFilesModels->next())
            {
                $objFile    = new \File($objFilesModels->path);
                AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__,' fileContent from '.$objFilesModels->path.' lng: '.$objFile->size);//files/co4-rawFiles/themes/standard/bootstrap/myvariables.less lng: 29554
                $strContent = $objFile->getContent();

                if ($this->isFileUpdated($objFile, $objTarget))
                {
                    AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__,' fileUpdated');
                    $this->rewrite          = true;
                    $this->rewriteBootstrap = true;
                    if ($strContent)
                    {
                        $strVariables .= "\n" . $strContent;
                    }
                }
                else
                {
                    $strVariables .= "\n" . $strContent;
                }
            }
        }

        if ($this->rewriteBootstrap)
        {
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__,' rewriteBootstrap');
            $objTarget->write($strVariables);
            $objTarget->close();
        }

        $this->objLess->parse($strVariables);
    }

    /**
     * mixins.less must not be changed, no hash check
     */
    protected function addBootstrapMixins()
    {
        $objFile = new \File($this->getBootstrapSrc('mixins.less'));
        AssetsLog::setAssetDebugmode(1);
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, $this->getBootstrapSrc('mixins.less').' lng: '.$objFile->size);

        if (str_replace('v', '', BOOTSTRAPVERSION) >= '3.2.0')
        {
            preg_match_all('/@import "(.*)";/', $objFile->getContent(), $arrImports);

            if (is_array($arrImports[1]))
            {
                foreach ($arrImports[1] as $strFile)
                {
                    if (!file_exists(TL_ROOT . '/' . BOOTSTRAPLESSDIR . '/' . $strFile))
                    {
                        continue;
                    }

                    $objMixinFile = new \File(BOOTSTRAPLESSDIR . '/' . $strFile);
                    $this->objLess->parseFile(TL_ROOT . '/' . $objMixinFile->value);
                }
            }

            return;
        }


        if ($objFile->size > 0)
        {
            $this->objLess->parseFile(TL_ROOT . '/' . $objFile->value);
        }
    }

    /**
     * alerts.less must not be changed, no hash check
     */
    protected function addBootstrapAlerts()
    {
        $objFile = new \File($this->getBootstrapSrc('alerts.less'));
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, $this->getBootstrapSrc('alerts.less').' lng: '.$objFile->size);

        if ($objFile->size > 0)
        {
            $this->objLess->parseFile(TL_ROOT . '/' . $objFile->value);
        }
    }


    protected function addBootstrapUtilities()
    {
        $arrUtilities = [
            'utilities.less',
            'responsive-utilities.less',
            'forms.less',
            'buttons.less',
            'alerts.less',
            'grid.less',
        ];

        foreach ($arrUtilities as $strFile)
        {
            $objFile = new \File($this->getBootstrapSrc($strFile));
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, $this->getBootstrapSrc($strFile).' lng: '.$objFile->size);

            if ($objFile->size > 0)
            {
                $this->objLess->parseFile(TL_ROOT . '/' . $objFile->value);
            }
        }
    }

    protected function addBootstrapType()
    {
        $objFile = new \File($this->getBootstrapSrc('type.less'));
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, $this->getBootstrapSrc('type.less').' lng: '.$objFile->size);

        if ($objFile->size > 0)
        {
            $this->objLess->parseFile(TL_ROOT . '/' . $objFile->value);
        }
    }

    protected function addFontAwesomeVariables()
    {
        $objFile = new \File($this->getFontAwesomeLessSrc('variables.less'));
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__,' add addFontAwesomeVariables from '.$this->getFontAwesomeLessSrc('variables.less').' lng: '.$objFile->size);

        if ($objFile->exists() && $objFile->size > 0)
        {
          $this->objLess->parseFile(TL_ROOT . '/' . $objFile->value);
        }
    }

    protected function addFontAwesomeCore()
    {
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__,' add addFontAwesomeCore from '.$this->getFontAwesomeLessSrc('core.less'));
        $objFile = new \File($this->getFontAwesomeLessSrc('core.less'));

        if ($objFile->exists() && $objFile->size > 0)
        {
          $this->objLess->parseFile(TL_ROOT . '/' . $objFile->value);
        }
    }

    protected function addFontAwesomeMixins()
    {
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__,' add addFontAwesomeMixins from '.$this->getFontAwesomeLessSrc('mixins.less'));
        $objFile = new \File($this->getFontAwesomeLessSrc('mixins.less'));

        if ($objFile->exists() && $objFile->size > 0)
        {
          $this->objLess->parseFile(TL_ROOT . '/' . $objFile->value);
        }
    }

    protected function addFontAwesome()
    {
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__,' add addFontAwesome from '.$this->getFontAwesomeCssSrc('font-awesome.less'));
        $objFile = new \File($this->getFontAwesomeCssSrc('font-awesome.css'), true);
        if ($objFile->exists() && $objFile->size > 0)
        {
          $strCss = $objFile->getContent();
          $strCss = str_replace("../fonts", '/' . rtrim(FONTAWESOMEFONTDIR, '/'), $strCss);
          $this->objLess->parse($strCss);
        }
    }

    protected function addElegantIconsVariables()
    {
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__,' add addElegantIconsVariables from '.$this->getElegentIconsLessSrc('variables.less'));
        $objFile = new \File($this->getElegentIconsLessSrc('variables.less'));

        if ($objFile->exists() && $objFile->size > 0)
        {
          $this->objLess->parseFile(TL_ROOT . '/' . $objFile->value);
        }
    }

    protected function addElegantIcons()
    {
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__,' add addElegantIcons from '.$this->getElegentIconsCssSrc('elegant-icons.less'));
        $objFile = new \File($this->getElegentIconsCssSrc('elegant-icons.css'), true);
        if ($objFile->exists() && $objFile->size > 0)
        {
          $strCss = $objFile->getContent();
          $strCss = str_replace("../fonts", '/' . rtrim(ELEGANTICONSFONTDIR, '/'), $strCss);
          $this->objLess->parse($strCss);
        }
    }

    protected function addCustomLessFiles()
    {
        if (!is_array($GLOBALS['TL_USER_CSS']) || empty($GLOBALS['TL_USER_CSS']))
        {
            return false;
        }

        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__,' add addCustomLessFiles');
        foreach ($GLOBALS['TL_USER_CSS'] as $key => $css)
        {
            $arrCss = trimsplit('|', $css);

            $objFile = new \File($arrCss[0]);

            if ($this->isFileUpdated($objFile, $this->objUserCssFile))
            {
                $this->rewrite = true;
            }

            $strContent = $objFile->getContent();

            // replace variables.less by custom variables.less
            $hasImports = preg_match_all('!@import(\s+)?(\'|")(.+)(\'|");!U', $strContent, $arrImport);

            if ($hasImports)
            {
                $this->arrLessImportDirs[$objFile->dirname] = $objFile->dirname;
            }

            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__,' add addCustomLessFiles from '.$objFile->value);
            $this->objLess->parseFile(TL_ROOT . '/' . $objFile->value);  // muss da nich TL_ROOT\    dazu ???

            unset($GLOBALS['TL_USER_CSS'][$key]);
        }
    }

    protected function isFileUpdated(\File $objFile, \File $objTarget)
    {
        return ($objFile->size > 0 && ($this->rewrite || $this->rewriteBootstrap || $objFile->mtime > $objTarget->mtime));
    }

    public function __get($strKey)
    {
        switch ($strKey)
        {
            case 'title':
                return standardize(\StringUtil::restoreBasicEntities(implode('-', $this->getEach('title'))));
            case 'addBootstrapPrint':
            case 'addFontAwesome':
                return max($this->getEach($strKey));
            case 'addFontAwesome':
                return max($this->getEach($strKey));
            case 'addingbootstrap':
                return max($this->getEach($strKey));
            case 'variablesSRC':
            case 'variablesOrderSRC':
                return $this->getEach($strKey);
            case 'ids':
                return $this->getEach('id'); // must be id
            case 'lastUpdate':
                return max($this->getEach('tstamp')); // return max tstamp from css groups
        }

        if (isset($this->arrData[$strKey]))
        {
            return $this->arrData[$strKey];
        }

        return parent::__get($strKey);
    }

    public function getEach($strKey)
    {
        $return = [];

        foreach ($this->arrData as $key => $value)
        {
            $value = $value[$strKey];

            $varUnserialized = @unserialize($value);

            if (is_array($varUnserialized))
            {
                // flatten array
                $return = array_merge($return, $varUnserialized);
                continue;
            }

            $return[] = $value;
        }

        return $return;
    }

    protected function getSrc($src)
    {
        return CSSDIR . $src;
    }

    protected function getBootstrapSrc($src)
    {
        return BOOTSTRAPLESSDIR . $src;
    }
    protected function getBootstrapDist($src)
    {
        return BOOTSTRAPDISTDIR . $src;
    }

    protected function getBootstrapCustomSrc($src)
    {
        return BOOTSTRAPLESSCUSTOMDIR . $src;
    }

    protected function getFontAwesomeCssSrc($src)
    {
        return FONTAWESOMECSSDIR . $src;
    }

    protected function getFontAwesomeLessSrc($src)
    {
        return FONTAWESOMELESSDIR . $src;
    }

    protected function getFontAwesomeCustomSrc($src)
    {
        return FONTAWESOMELESSCUSTOMDIR . $src;
    }

    protected function getElegentIconsCssSrc($src)
    {
        return ELEGANTICONSCSSDIR . $src;
    }

    protected function getElegentIconsLessSrc($src)
    {
        return ELEGANTICONSLESSDIR . $src;
    }

}
