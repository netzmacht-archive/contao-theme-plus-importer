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


class Module extends \BackendModule
{
	/**
	 * @var string
	 */
	protected $strTemplate = 'theme_plus_importer';

	/**
	 * @var Installer
	 */
	private $installer;


	/**
	 * Construct
	 */
	function __construct()
	{
		$dispatcher = $GLOBALS['container']['event-dispatcher'];
		$installer  = new Installer(\Input::get('id'), $dispatcher);

		$this->installer = $installer;
	}


	/**
	 * @return string
	 */
	public function generate()
	{
		if(\Input::post('TL_SUBMIT' == 'theme_plus_import')) {
			$this->importAssets();
			$this->reload();
		}

		return parent::generate();
	}


	/**
	 * Compile the current element
	 */
	protected function compile()
	{
		$this->Template->files = $this->installer->getUninstalledStylesheets();
	}


	/**
	 *
	 */
	private function importAssets()
	{
		$files = \Input::post('files');

		switch(\Input::get('table')) {
			case 'tl_theme_plus_stylesheet':
				$method = 'installStylesheet';
				break;

			case 'tl_theme_plus_javascript':
				$method = 'installJavascript';
				break;

			default:
				throw new \RuntimeException('Can not import assets. Unknown assets table given');
				break;
		}

		foreach($files as $file) {
			if(!$file['file']) {
				continue;
			}

			$this->installer->$method($file['file'], $file['conditional'], $file['filter']);
			\Message::addNew(sprintf($GLOBALS['TL_LANG']['MSC']['theme_plus_file_imported'], $file['file']));
		}
	}

} 