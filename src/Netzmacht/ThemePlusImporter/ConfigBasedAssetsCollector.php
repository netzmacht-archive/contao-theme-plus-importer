<?php

/**
 * @package    dev
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2014 netzmacht creative David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

namespace Netzmacht\ThemePlusImporter;

use Netzmacht\ThemePlusImporter\Event\CollectAssetsEvent;

/**
 * Class ConfigBasedAssetsCollector
 * @package Netzmacht\ThemePlusImporter
 */
class ConfigBasedAssetsCollector
{

	/**
	 * @param CollectAssetsEvent $event
	 */
	public static function handle(CollectAssetsEvent $event)
	{
		if(isset($GLOBALS['THEME_PLUS_IMPORT_STYLESHEETS'])) {
			foreach((array) $GLOBALS['THEME_PLUS_IMPORT_STYLESHEETS'] as $package => $files) {
				foreach($files as $file) {
					$event->addStylesheet($package, $file);
				}
			}
		}

		if(isset($GLOBALS['THEME_PLUS_IMPORT_JAVASCRIPTS'])) {
			foreach((array) $GLOBALS['THEME_PLUS_IMPORT_JAVASCRIPTS'] as $package => $files) {
				foreach($files as $file) {
					$event->addJavaScript($package, $file);
				}
			}
		}
	}

} 