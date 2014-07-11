<?php

$GLOBALS['TL_DCA']['tl_theme_plus_javascript']['config']['onload_callback'][] = array(
	'Netzmacht\ThemePluseImporter\Dca\ThemePlus',
	'onLoadCallback'
);

array_insert($GLOBALS['TL_DCA']['tl_theme_plus_javascript']['list']['global_operations'], 0, array(
	'import' => array(
		'label' => &$GLOBALS['TL_LANG']['tl_theme_plus_javascript']['import_button'],
		'href' 	=> 'key=import_assets&table=tl_theme_plus_javascript',
		'icon'  => 'theme_import.gif',
	)
));