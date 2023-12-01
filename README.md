    # Contao Extassets Bundle


this bundle has been completely converted from heimrichhannot/contao-extassets to pbd-kn/contao-extasset-bundle 

The namespaces for psr-4 were revised.

Bootstrap's selection introduced.
its  taken over from the twbs package. 

Create your own css & js groups and add them to your contao theme layouts.

## General features
- Backend Module for external css/less
- Backend Module for external js 
- Add multiple CSS/less & JS groups to contao layout 
- Select Bootstrap framework support (for css by default, enable within js group)
  take min version from twbs
- Font Awesome no longer supported 
  Using bundle contao-tinymce-plugin-bundle
- Elegant Icons can be added (availability of all variables and mixins)
- Css file caching for production mode (disable byPassCache in contao settings)

## External CSS

### Features
- Complete less css support, automatically compile all your less files within a external css group to cs
- refresh: clear the less cache 
- Observer folders (recursive) within your external css groups
- Add multiple custom variable files, to overwrite for example bootstrap variables.less (like @brand-primary)
- With bootstrap selected install bootstrap min from twbs package
- bootstrap print.css support
- Internet Explorer 6-9 - 4096 css-selector handling (Internet Explorer 6 - 9 has only a maximum of 4096 css-selectors possible per file. Extassets make usage of https://github.com/zweilove/css_splitter ans solve this problem by splitting aggregated files into parts.)
- all files within $GLOBALS['TL_USER_CSS'] will be parsed within external css groups


### Elegant Icon Font (http://www.elegantthemes.com/blog/resources/elegant-icon-font)

### debug
- set debug for mor information

### Installation

#### Contao 4.0

1. Install via Contao Manager

```
composer require pbd-kn/contao-extassets-bundle
```

2. Add the following to lines to the `$bundles` array in your `app/AppKernel.php` 

```
/**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        $bundles = [
            …
            new ContaoModuleBundle('extassets', $this->getRootDir()),
            new ContaoModuleBundle('haste_plus', $this->getRootDir()),
        ];

        …
    }
```

3. Clear app chache
 
```
bin/console cache:clear -e prod
```



### Hooks

#### addCustomAssets

Attach custom fonts or css libraries to extassets combiner. 

```
// config.php
$GLOBALS['TL_HOOKS']['addCustomAssets'][] = array('MyClass', 'addCustomAssetsHook');


// MyClass.php

public function addCustomAssetsHook(\Less_Parser $objLess, $arrData, \ExtAssets\ExtCssCombiner $objCombiner)
{
    // example: add custom less variables to your css group to provide acces to mixins or variables in your external css files
    $this->objLess->parseFile('/assets/components/my-library/less/my-variables.less'));
    
    // example: add custom font to your css group
    $objFile = new \File('/assets/components/my-library/css/my-font.css, true);
    $strCss = $objFile->getContent();
    $strCss = str_replace("../fonts", '/assets/components/my-library/'), $strCss); // make font path absolut, mostly required
    $this->objLess->parse($strCss);
}

```


