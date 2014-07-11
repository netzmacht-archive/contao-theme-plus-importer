<?php

$GLOBALS['TL_DCA']['tl_theme_plus_stylesheet']['config']['onload_callback'][] = array(
	'Netzmacht\ThemePluseImporter\Dca\ThemePlus',
	'onLoadCallback'
);

array_insert($GLOBALS['TL_DCA']['tl_theme_plus_stylesheet']['list']['global_operations'], 0, array(
	'import' => array(
		'label' => &$GLOBALS['TL_LANG']['tl_theme_plus_stylesheet']['import_button'],
		'href' 	=> 'key=import_assets&table=tl_theme_plus_stylesheet',
		'icon'  => 'theme_import.gif',
	)
));