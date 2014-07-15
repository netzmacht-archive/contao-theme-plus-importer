
Theme Plus Importer
===================

This extension provides an import feature for the great Theme+ extension for Contao. 3rd party extension can use
**Theme Plus Importer** to easily integrate their assets into Theme+.

Install
--------

Theme Plus importer can be installed using the composer repository: `netzmacht/contao-theme-plus-importer`


Import types
--------

The importer auto detects which type the asset has. It detects urls (beginning with `http://`, `https://` or `//`)
and files. The different file sources of Theme+ are supported as well (`files`, `assets` or `system/modules`).

Add assets using the config files
--------

Extensions can define their assets using the config vars.

```php
<?php

// import javascripts
$GLOBALS['THEME_PLUS_IMPORT_JAVASCRIPTS']['package-name'][] 'path/to/javascripts.js';

// import stylesheets
$GLOBALS['THEME_PLUS_IMPORT_STYLESHEETS']['package-name'][] 'path/to/style.css';

```

Add assets using the event dispatcher
-------

Besides assets can be imported by using the event dispatcher. This is useful, if you using some third party libraries
where you want to ensure they are installed.

```php
<?php

use Netzmacht\ThemePlusImporter\Event\CollectAssetsEvent;

$GLOBALS['TL_EVENTS'][CollectAssetsEvent::NAME][] = function(CollectAssetsEvent $event) {
    $event->addJavascript('package-name', 'path/to/javascripts.js');
}

```

Note: If you do not depend on the importer you should use the internal used event name `theme-plus-importer.collect-assets`
instead of the class constant.