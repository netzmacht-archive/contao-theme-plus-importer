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

/**
 * Class ThemePlus
 * @package Netzmacht\ThemePlusImporter
 */
class ThemePlus
{
	/**
	 * @param \DataContainer $dc
	 */
	public function onLoadCallback(\DataContainer $dc)
	{
		if(\Input::get('act')) {
			return;
		}

		$dispatcher = $GLOBALS['container']['event-dispatcher'];
		$installer  = new Installer(\Input::get('id'), $dispatcher);

		if($dc->table == 'tl_theme_plus_stylesheet') {
			$addIcon = $installer->hastUninstalledStylesheets();
		}
		else {
			$addIcon = $installer->hasUninstalledJavascripts();
		}

		if(!$addIcon) {
			unset($GLOBALS['TL_DCA'][$dc->table]['list']['global_operations']['import']);
		}
	}

}