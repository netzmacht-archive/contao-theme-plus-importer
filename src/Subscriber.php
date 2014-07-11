<?php

/**
 * @package    dev
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2014 netzmacht creative David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

namespace ThemePlusImporter;


use ThemePlusImporter\Event\CollectAssetsEvent;

class Subscriber
{
	/**
	 * @param $name
	 */
	public function onLoadDataContainer($name)
	{
		if(!in_array($name, array('tl_theme_plus_stylesheet', 'tl_theme_plus_javascript')) || \Input::get('act')) {
			return;
		}

		try {
			$dispatcher = $GLOBALS['container']['event-dispatcher'];
			$installer  = new Installer(\Input::get('id'), $dispatcher);

			if($name == 'tl_theme_plus_stylesheet') {
				$addIcon = $installer->hastUninstalledStylesheets();
			}
			else {
				$addIcon = $installer->hasUninstalledJavascripts();
			}

			if(!$addIcon) {
				unset($GLOBALS['TL_DCA'][$name]['list']['global_operations']['import']);
			}
		}
		catch(\Exception $e) {

		}
	}


	/**
	 * @param CollectAssetsEvent $event
	 */
	public static function handleCollectAssetsEvent(CollectAssetsEvent $event)
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