<?php

use ThemePlusImporter\Event\CollectAssetsEvent;

$GLOBALS['BE_MOD']['design']['themes']['import_assets'] = array('ThemePlusImporter\Module', 'generate');

// hooks
$GLOBALS['TL_HOOKS']['loadDataContainer'][]       = array('ThemePlusImporter\Subscriber', 'onLoadDataContainer');

// event listener
$GLOBALS['TL_EVENTS'][CollectAssetsEvent::NAME][] = 'ThemePlusImporter\Subscriber::handleCollectAssetsEvent';