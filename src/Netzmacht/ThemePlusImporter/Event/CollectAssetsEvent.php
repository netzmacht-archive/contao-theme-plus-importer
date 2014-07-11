<?php

/**
 * @package    dev
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2014 netzmacht creative David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

namespace Netzmacht\ThemePlusImporter\Event;

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
	 * @param $package
	 * @param $path
	 * @param null $name
	 *
	 * @return $this
	 */
	public function addJavaScript($package, $path, $name=null)
	{
		if($name) {
			$this->javascripts[$package][$name] = $path;
		}
		else {
			$this->javascripts[$package][] = $path;
		}

		return $this;
	}


	/**
	 * @param $package
	 * @param $path
	 * @param null $name
	 *
	 * @return $this
	 */
	public function addStylesheet($package, $path, $name=null)
	{
		if($name) {
			$this->stylesheets[$package][$name] = $path;
		}
		else {
			$this->stylesheets[$package][] = $path;
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