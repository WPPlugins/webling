<?php

function webling_admin_add_menue()
{
	global $_wp_last_object_menu;

	$_wp_last_object_menu++;

	add_menu_page(
		__('Webling', 'webling'),
		__('Webling', 'webling'),
		'manage_options',
		'webling_page_main',
		['webling_page_form_list', 'html'],
		'dashicons-groups', // plugins_url( 'myplugin/images/icon.png' ),
		$_wp_last_object_menu
	);

	add_submenu_page(
		'webling_page_main',
		__('Anmeldeformulare', 'webling'),
		__('Anmeldeformulare', 'webling'),
		'manage_options',
		'webling_page_main',
		['webling_page_form_list', 'html']
	);

	add_submenu_page(
		'webling_page_main',
		__('Anmeldeformular erstellen', 'webling'),
		__('Anmeldeformular erstellen', 'webling'),
		'manage_options',
		'webling_page_form_edit',
		['webling_page_form_edit', 'html']
	);

	add_submenu_page(
		'webling_page_main',
		__('Mitgliederlisten', 'webling'),
		__('Mitgliederlisten', 'webling'),
		'manage_options',
		'webling_page_memberlist_list',
		['webling_page_memberlist_list', 'html']
	);

	add_submenu_page(
		'webling_page_main',
		__('Mitgliederliste erstellen', 'webling'),
		__('Mitgliederliste erstellen', 'webling'),
		'manage_options',
		'webling_page_memberlist_edit',
		['webling_page_memberlist_edit', 'html']
	);

	add_submenu_page(
		'webling_page_main',
		__('Einstellungen', 'webling'),
		__('Einstellungen', 'webling'),
		'manage_options',
		'webling_page_settings',
		['webling_page_settings', 'html']
	);
}
