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


class Module extends \BackendModule
{
	/**
	 * @var string
	 */
	protected $strTemplate = 'be_theme_plus_import';

	/**
	 * @var Importer
	 */
	private $importer;

	/**
	 * @var Form
	 */
	private $form;


	/**
	 * Construct
	 */
	function __construct()
	{
		parent::__construct();

		$dispatcher = $GLOBALS['container']['event-dispatcher'];
		$installer  = new Importer(\Input::get('id'), $dispatcher);

		$this->importer = $installer;
		$this->form     = new Form('theme_plus_import');

		$this->form
			->setOptions('files', $this->getFilesOption())
			->setOptions('filters', $this->getAsseticFilters())
			->initialize();
	}


	/**
	 * @return string
	 */
	public function generate()
	{
		if($this->form->validate()) {
			$this->importAssets();

			if(\Input::post('saveNback')) {
				$this->redirect($this->getReferer());
			}

			$this->reload();
		}

		return parent::generate();
	}


	/**
	 * Compile import page
	 */
	protected function compile()
	{
		$table = \Input::get('table');

		$this->Template->form         = $this->form;
		$this->Template->messages     = \Message::generate();
		$this->Template->href         = $this->getReferer(true);
		$this->Template->title        = specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']);
		$this->Template->button       = $GLOBALS['TL_LANG']['MSC']['backBT'];
		$this->Template->headline     = $GLOBALS['TL_LANG'][$table]['importer_headline'];
		$this->Template->action       = ampersand(\Environment::get('request'));
		$this->Template->selectAll    = $GLOBALS['TL_LANG']['MSC']['selectAll'];
		$this->Template->saveButton   = specialchars($GLOBALS['TL_LANG']['MSC']['save']);
		$this->Template->saveNBack    = specialchars($GLOBALS['TL_LANG']['MSC']['saveNback']);
	}


	/**
	 * Import all given assets
	 */
	private function importAssets()
	{
		$files         = (array)\Input::post('files');
		$layouts       = (array)\Input::post('layouts');
		$conditional   = \Input::post('conditional');
		$asseticFilter = \Input::post('asseticFilter');

		switch(\Input::get('table')) {
			case 'tl_theme_plus_stylesheet':
				$method = 'importStylesheets';
				break;

			case 'tl_theme_plus_javascript':
				$method = 'importJavascripts';
				break;

			default:
				throw new \RuntimeException('Can not import assets. Unknown assets table given');
				break;
		}

		$this->importer->$method($files, $layouts, $conditional, $asseticFilter);

		foreach($files as $file) {
			\Message::addInfo(sprintf($GLOBALS['TL_LANG']['MSC']['theme_plus_file_imported'], $file));
		}
	}


	/**
	 * Get all assetic filters
	 *
	 * @return array
	 */
	private function getAsseticFilters()
	{
		if(class_exists('Bit3\Contao\ThemePlus\DataContainer\Stylesheet')) {
			$class = 'Bit3\Contao\ThemePlus\DataContainer\Stylesheet';
		}
		else {
			$class = 'ThemePlus\DataContainer\Stylesheet';
		}

		$callback = new $class;
		return $callback->getAsseticFilterOptions();
	}


	/**
	 * @throws \RuntimeException
	 * @return array
	 */
	private function getFilesOption()
	{
		switch(\Input::get('table')) {
			case 'tl_theme_plus_stylesheet':
				$files   = $this->importer->getUninstalledStylesheets();
				break;

			case 'tl_theme_plus_javascript':
				$files   = $this->importer->getUninstalledJavascripts();
				break;

			default:
				throw new \RuntimeException('Can not import assets. Unknown assets table given');
				break;
		}


		$prepared = array();

		foreach($files as $package => $assets) {
			if(!$assets) {
				continue;
			}

			$prepared[$package] = array_map(function ($asset) {
				return array(
					'value' => $asset,
					'label' => $asset,
				);
			}, $assets);
		}

		ksort($prepared);

		return $prepared;
	}

} 