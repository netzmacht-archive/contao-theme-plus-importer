<?php

use Netzmacht\ThemePlusImporter\Event\CollectAssetsEvent;

// add custom module
$GLOBALS['BE_MOD']['design']['themes']['import_assets'] = array('Netzmacht\ThemePlusImporter\Module', 'generate');

// event handler
$GLOBALS['TL_EVENTS'][CollectAssetsEvent::NAME][] = 'Netzmacht\ThemePlusImporter\ConfigBasedAssetsCollector::handle';