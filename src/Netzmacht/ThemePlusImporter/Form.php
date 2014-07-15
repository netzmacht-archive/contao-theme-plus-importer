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


final class Form
{
	/**
	 * @var array|\Widget[]
	 */
	private $widgets = array();

	/**
	 * @var array
	 */
	private $options = array();

	/**
	 * @var string
	 */
	private $name;


	/**
	 * @param $name
	 */
	function __construct($name)
	{
		$this->name = $name;
	}


	/**
	 * @param $name
	 * @param $values
	 *
	 * @return $this
	 */
	public function setOptions($name, $values)
	{
		$this->options[$name] = $values;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * @return $this
	 */
	public function initialize()
	{
		$table = \Input::get('table');
		
		// files widget
		$attributes = array(
			'label'       => &$GLOBALS['TL_LANG'][$table]['theme_plus_import_file'][0],
			'description' => &$GLOBALS['TL_LANG'][$table]['theme_plus_import_file'][1],
			'type'        => 'checkbox',
			'name'        => 'files',
			'options'     => $this->options['files'],
			'multiple'    => true,
		);

		$this->widgets[] = new \CheckBox($attributes);

		// layouts widget
		$config          = $GLOBALS['TL_DCA'][$table]['fields']['layouts'];
		$attributes      = \Widget::getAttributesFromDca($config, 'layouts');
		$this->widgets[] = new \CheckBox($attributes);

		$config          = $GLOBALS['TL_DCA'][$table]['fields']['cc'];
		$attributes      = \Widget::getAttributesFromDca($config, 'conditional');
		$this->widgets[] = new \TextField($attributes);

		$config          = $GLOBALS['TL_DCA'][$table]['fields']['asseticFilter'];
		$attributes      = \Widget::getAttributesFromDca($config, 'asseticFilter');
		$this->widgets[] = new \SelectMenu($attributes);

		return $this;
	}


	/**
	 * @return bool
	 */
	public function validate()
	{
		if(\Input::post('FORM_SUBMIT') != $this->name) {
			return false;
		}

		$hasErrors = false;

		foreach($this->widgets as $widget) {
			$widget->validate();

			if($widget->hasErrors()) {
				$hasErrors = true;
			}
		}

		return !$hasErrors;
	}


	/**
	 * @return string
	 */
	public function generate()
	{
		$buffer = '';

		foreach($this->widgets as $widget) {
			$label = '';

			if($widget->label && $widget->type != 'checkbox') {
				$label = sprintf('<label for="%s" class="tl_label">%s</label>', $widget->id, $widget->label);
			}

			$buffer .= sprintf(
				'<div class="%s"><h3>%s</h3>%s<p class="tl_help tl_tip">%s</p></div>',
				$widget->tl_class,
				$label,
				$widget->generate(),
				$widget->description
			);
		}

		return $buffer;
	}

} 