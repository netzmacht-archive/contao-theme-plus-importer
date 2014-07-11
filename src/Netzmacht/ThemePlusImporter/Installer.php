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


use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Netzmacht\ThemePlusImporter\Event\CollectAssetsEvent;

class Installer
{

	/**
	 * @var EventDispatcherInterface
	 */
	private $eventDispatcher;

	/**
	 * @var int
	 */
	private $themeId;

	/**
	 * @var array
	 */
	private $javascripts;

	/**
	 * @var array
	 */
	private $stylesheets;

	/**
	 * @var array
	 */
	private $installedStylesheets = array();

	/**
	 * @var array
	 */
	private $installedJavascripts = array();


	/**
	 * @param $themeId
	 * @param EventDispatcherInterface $eventDispatcher
	 */
	function __construct($themeId, EventDispatcherInterface $eventDispatcher)
	{
		$this->themeId         = $themeId;
		$this->eventDispatcher = $eventDispatcher;

		$this->loadAvailableAssets();
		$this->loadInstalledAssets();
	}


	/**
	 * @return array
	 */
	public function getUninstalledStylesheets()
	{
		$uninstalled = array();

		foreach($this->stylesheets as $package => $files) {
			$uninstalled[$package] = array_filter($files, function($file) {
				return !in_array($file, $this->installedStylesheets);
			});
		}

		return $uninstalled;
	}


	/**
	 * @return array
	 */
	public function getUninstalledJavascripts()
	{
		$uninstalled = array();

		foreach($this->javascripts as $package => $files) {
			$uninstalled[$package] = array_filter($files, function($file) {
				return !in_array($file, $this->installedJavascripts);
			});
		}

		return $uninstalled;
	}


	/**
	 * @param $file
	 * @param array $layouts
	 * @param null $conditional
	 * @param null $asseticFilter
	 * @return $this
	 */
	public function installStylesheet($file, $layouts=array(), $conditional=null, $asseticFilter=null)
	{
		$modelClass = $GLOBALS['TL_MODELS']['tl_theme_plus_stylesheet'];

		$this->installAsset($modelClass, $file, $layouts, $conditional, $asseticFilter, 'theme_plus_stylesheets');
		$this->installedStylesheets[] = $file;

		return $this;
	}


	/**
	 * @param $file
	 * @param array $layouts
	 * @param null $conditional
	 * @param null $asseticFilter
	 * @return $this
	 */
	public function installJavascript($file, $layouts=array(), $conditional=null, $asseticFilter=null)
	{
		$modelClass = $GLOBALS['TL_MODELS']['tl_theme_plus_javascript'];

		$this->installAsset($modelClass, $file, $layouts, $conditional, $asseticFilter, 'theme_plus_javascripts');
		$this->installedStylesheets[] = $file;

		return $this;
	}


	/**
	 * Load assets which are available to be synced
	 */
	private function loadAvailableAssets()
	{
		if($this->stylesheets !== null && $this->javascripts !== null) {
			return;
		}

		$event = new CollectAssetsEvent();
		$this->eventDispatcher->dispatch($event::NAME, $event);

		$this->stylesheets = $event->getStylesheets();
		$this->javascripts = $event->getJavascripts();
	}


	/**
	 * Load installed theme+ assets
	 */
	private function loadInstalledAssets()
	{
		/** @var \Model  $modelClass */
		$modelClass = $GLOBALS['TL_MODELS']['tl_theme_plus_stylesheet'];
		$collection = $modelClass::findBy('pid', $this->themeId);

		if($collection !== null) {
			$this->installedStylesheets = $collection->fetchEach('file');
		};

		/** @var \Model  $modelClass */
		$modelClass = $GLOBALS['TL_MODELS']['tl_theme_plus_javascript'];
		$collection = $modelClass::findBy('pid', $this->themeId);

		if($collection !== null) {
			$this->installedJavascripts = $collection->fetchEach('file');
		};
	}


	/**
	 * @param \Model|string $modelClass
	 * @param $file
	 * @param array $layouts
	 * @param $conditional
	 * @param $asseticFilter
	 *
	 * @param $field
	 * @return \Model
	 */
	private function installAsset($modelClass, $file, $layouts=array(), $conditional, $asseticFilter, $field)
	{
		$result  = $modelClass::findAll(array('limit' => '1', 'order' => 'sorting DESC'));
		$sorting = $result === null ? 0 : $result->sorting;
		$model   = null;
		$type    = 'file';
		$source  = '';

		if(substr($file, 0, 6) == 'assets') {
			$source = 'assets';
		} elseif(substr($file, 0, 5) == 'files') {
			$source = 'files';
		}
		elseif(substr($file, 0, 7) == 'http://' || substr($file, 0, 8) == 'https://' || substr($file, 0, 2) == '//') {
			$type = 'url';
		}
		else {
			$source = 'system/modules';
		}

		/** @var \Model $model */
		$model                = new $modelClass();
		$model->tstamp        = time();
		$model->pid           = $this->themeId;
		$model->type          = $type;
		$model->file          = $file;
		$model->filesource    = $source;
		$model->cc            = (string)$conditional;
		$model->asseticFilter = (string)$asseticFilter;
		$model->sorting       = ++$sorting;
		$model->layouts		  = $layouts;
		$model->save();

		if($layouts) {
			$layouts = \LayoutModel::findMultipleByIds($layouts);

			if($layouts) {
				while($layouts->next()) {
					$value   = deserialize($layouts->$field);
					$value[] = $model->id;

					$layouts->$field = $value;
					$layouts->save();
				}
			}
		}

		return $model;
	}

	/**
	 * @return bool
	 */
	public function hastUninstalledStylesheets()
	{
		foreach($this->getUninstalledStylesheets() as $files) {
			if(!empty($files)) {
				return true;
			}
		}

		return false;
	}


	/**
	 * @return bool
	 */
	public function hasUninstalledJavascripts()
	{
		foreach($this->getUninstalledJavascripts() as $files) {
			if(!empty($files)) {
				return true;
			}
		}

		return false;
	}
} 