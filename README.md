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

#### Contao

1. Install via Contao Manager Contao 4

```
composer require pbd-kn/contao-extassets-bundle
Version 1.6..
```
1. Install via Contao Manager Contao 4 und 5
```
composer require pbd-kn/contao-extassets-bundle
Version 2...
```
