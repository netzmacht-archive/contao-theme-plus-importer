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
	 * @var Installer
	 */
	private $installer;


	/**
	 * Construct
	 */
	function __construct()
	{
		parent::__construct();

		$dispatcher = $GLOBALS['container']['event-dispatcher'];
		$installer  = new Installer(\Input::get('id'), $dispatcher);

		$this->installer = $installer;
		$this->loadLanguageFile('tl_layout');
	}


	/**
	 * @return string
	 */
	public function generate()
	{
		if(\Input::post('FORM_SUBMIT') == 'theme_plus_import') {
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

		$this->Template->files        = $this->generateFilesWidget();
		$this->Template->filesLabel   = $GLOBALS['TL_LANG']['tl_layout']['theme_plus_import_files'][0];
		$this->Template->filesHelp    = $GLOBALS['TL_LANG']['tl_layout']['theme_plus_import_files'][1];

		$this->Template->layouts      = $this->generateLayoutsWidget();
		$this->Template->layoutsLabel = $GLOBALS['TL_LANG']['tl_layout']['theme_plus_import_layouts'][0];
		$this->Template->layoutsHelp  = $GLOBALS['TL_LANG']['tl_layout']['theme_plus_import_layouts'][1];

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
		$files   = \Input::post('files');
		$layouts = \Input::post('layouts');

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

			$this->installer->$method($file['file'], $layouts, $file['conditional'], $file['asseticFilter']);
			\Message::addInfo(sprintf($GLOBALS['TL_LANG']['MSC']['theme_plus_file_imported'], $file['file']));
		}
	}


	/**
	 * @return string
	 */
	private function generateFilesWidget()
	{
		$files   = $this->installer->getUninstalledStylesheets();
		ksort($files);

		$filters = $this->getAsseticFilters();

		$attributes = array(
			'name'         => 'files',
			'label'        => 'Foo',
			'id'		   => 'files',
			'description'  => 'bar',
			'type'         => 'multiColumnWizard',
			'columnFields' => array(
				'file' => array(
					'label'     => &$GLOBALS['TL_LANG']['tl_layout']['theme_plus_import_file'],
					'inputType' => 'select',
					'options'   => $files,
					'eval'                    => array('style' => 'width: 300px'),
				),
				'conditional' => array
				(
					'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['theme_plus_import_conditional'],
					'exclude'                 => true,
					'inputType'               => 'text',
					'eval'                    => array('style' => 'width: 140px'),
				),

				'asseticFilter' => array
				(
					'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['theme_plus_import_filter'],
					'exclude'                 => true,
					'inputType'               => 'select',
					'reference'               => &$GLOBALS['TL_LANG']['assetic'],
					'options'        		  => $filters,
					'eval'                    => array('style' => 'width: 140px', 'includeBlankOption' => true),
				),
			)
		);

		$widget = new \MultiColumnWizard($attributes);

		return $widget->generate();
	}


	/**
	 * @return string
	 */
	private function generateLayoutsWidget()
	{
		$config     = $GLOBALS['TL_DCA'][\Input::get('table')]['fields']['layouts'];
		$attributes = \Widget::getAttributesFromDca($config, 'layouts');
		$widget     = new \CheckBox($attributes);

		return $widget->generate();
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

} 