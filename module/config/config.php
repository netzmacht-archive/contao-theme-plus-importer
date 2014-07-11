<?php

use ThemePlusImporter\Event\CollectAssetsEvent;

// hooks
$GLOBALS['TL_HOOKS']['loadDataContainer'][]       = array('ThemePlusImporter\Subscriber', 'onLoadDataContainer');

// event listener
$GLOBALS['TL_EVENTS'][CollectAssetsEvent::NAME][] = 'ThemePlusImporter\Subscriber::handleCollectAssetsEvent';