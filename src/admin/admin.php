<?php

// Make sure we don't expose any info if called directly
defined('ABSPATH') or die('.');

require_once (__DIR__ . '/actions/add_headers.php');
require_once (__DIR__ . '/actions/add_menue.php');
require_once (__DIR__ . '/actions/bulk_actions.php');
require_once (__DIR__ . '/actions/init.php');
require_once (__DIR__ . '/actions/save_form.php');
require_once (__DIR__ . '/actions/save_memberlist.php');

require_once (__DIR__ . '/filters/add_action_links.php');

require_once (__DIR__ . '/pages/form_edit.php');
require_once (__DIR__ . '/pages/form_list.php');
require_once (__DIR__ . '/pages/memberlist_edit.php');
require_once (__DIR__ . '/pages/memberlist_list.php');
require_once (__DIR__ . '/pages/settings.php');


if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
require_once (__DIR__ . '/lists/Form_List.php');
require_once (__DIR__ . '/lists/Memberlist_List.php');


// init webling settings etc.
add_action('admin_init', 'webling_admin_init');
add_action('admin_init', 'webling_bulk_actions');


// save hooks
add_action('admin_post_save_memberlist', 'webling_admin_save_memberlist');
add_action('admin_post_save_form', 'webling_admin_save_form');


// add menu items
add_action('admin_menu', 'webling_admin_add_menue');

// Add link to setting on plugin page
add_filter('plugin_action_links_' . WEBLING_PLUGIN_FILE_NAME, 'webling_add_action_links');

// Add admin CSS
add_action('admin_enqueue_scripts', 'webling_admin_add_headers');

// Clear the cache after updating the settings
add_action('update_option_webling-options', 'webling_clear_cache');
