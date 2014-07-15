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

class Importer
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
	 * @param array $files
	 * @param array $layouts
	 * @param null $conditional
	 * @param null $asseticFilter
	 * @return $this
	 */
	public function importStylesheets(array $files, $layouts=array(), $conditional=null, $asseticFilter=null)
	{
		$modelClass = $GLOBALS['TL_MODELS']['tl_theme_plus_stylesheet'];

		$this->installAssets($modelClass, $files, $layouts, $conditional, $asseticFilter, 'theme_plus_stylesheets');
		$this->installedStylesheets[] = array_merge($this->installedStylesheets, $files);

		return $this;
	}


	/**
	 * @param array $files
	 * @param array $layouts
	 * @param null $conditional
	 * @param null $asseticFilter
	 * @return $this
	 */
	public function importJavascripts(array $files, $layouts=array(), $conditional=null, $asseticFilter=null)
	{
		$modelClass = $GLOBALS['TL_MODELS']['tl_theme_plus_javascript'];

		$this->installAssets($modelClass, $files, $layouts, $conditional, $asseticFilter, 'theme_plus_javascripts');
		$this->installedJavascripts[] = array_merge($this->installedJavascripts, $files);

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
	 * @param array $files
	 * @param array $layouts
	 * @param $conditional
	 * @param $asseticFilter
	 * @param $field
	 * @throws \RuntimeException
	 * @return \Model[]
	 */
	private function installAssets($modelClass, array $files, $layouts=array(), $conditional, $asseticFilter, $field)
	{
		$result  = $modelClass::findAll(array('limit' => '1', 'order' => 'sorting DESC'));
		$sorting = $result === null ? 0 : $result->sorting;
		$new     = array();

		foreach($files as $file) {
			$model   = null;
			$type    = 'file';
			$source  = '';

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

			if(substr($file, 0, 6) == 'assets') {
				$model->filesource = 'assets';
				$model->file       = $file;
			}
			elseif(substr($file, 0, 5) == 'files') {
				$fileModel = \FilesModel::findBy('path', $file);

				if(!$fileModel) {
					throw new \RuntimeException(sprintf('File "%s" does not exists', $file));
				}

				$model->filesource = 'files';
				$model->file       = $fileModel->uuid;
			}
			elseif(substr($file, 0, 7) == 'http://' || substr($file, 0, 8) == 'https://' || substr($file, 0, 2) == '//') {
				$type = 'url';
				$model->url = $file;
			}
			else {
				$model->source = 'system/modules';
				$model->file   = $file;
			}

			$model->type = $type;
			$model->save();

			$new[] = $model->id;
		}

		if($layouts && $new) {
			$layouts = \LayoutModel::findMultipleByIds($layouts);

			if($layouts) {
				while($layouts->next()) {
					$value = deserialize($layouts->$field);
					$value = array_merge($value, $new);

					$layouts->$field = $value;
					$layouts->save();
				}
			}
		}

		return $new;
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