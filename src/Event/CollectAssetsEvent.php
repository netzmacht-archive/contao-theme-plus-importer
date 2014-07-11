<?php

/**
 * @package    dev
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2014 netzmacht creative David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

namespace ThemePlusImporter\Event;

use Symfony\Component\EventDispatcher\Event;


class CollectAssetsEvent extends Event
{
	const NAME = 'theme-plus-importer.collect-assets';

	/**
	 * @var array
	 */
	private $javascripts = array();

	/**
	 * @var array
	 */
	private $stylesheets = array();


	/**
	 * @param $source
	 * @param $path
	 * @param null $name
	 *
	 * @return $this
	 */
	public function addJavaScript($source, $path, $name=null)
	{
		if($name) {
			$this->javascripts[$source][$name] = $path;
		}
		else {
			$this->javascripts[$source][] = $path;
		}

		return $this;
	}


	/**
	 * @param $source
	 * @param $path
	 * @param null $name
	 *
	 * @return $this
	 */
	public function addStylesheet($source, $path, $name=null)
	{
		if($name) {
			$this->stylesheets[$source][$name] = $path;
		}
		else {
			$this->stylesheets[$source][] = $path;
		}

		return $this;
	}


	/**
	 * @return array
	 */
	public function getJavascripts()
	{
		return $this->javascripts;
	}


	/**
	 * @return array
	 */
	public function getStylesheets()
	{
		return $this->stylesheets;
	}

} 