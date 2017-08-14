<?php
/*
Plugin Name: Webling
Plugin URI: https://www.webling.eu
Description: Mitgliederdaten aus der Vereinssoftware webling.eu auf deiner Webseite anzeigen.
Version: 3.2.1
Author: uSystems GmbH
Author URI: http://www.usystems.ch
*/

/*
Webling (Wordpress Plugin)
Copyright (C) 2014 uSystems GmbH
Contact me at http://www.usystems.ch
*/

// Make sure we don't expose any info if called directly
defined('ABSPATH') or die('.');

define('WEBLING_PLUGIN_FILE_NAME', 'webling/webling.php');
define('WEBLING_PLUGIN_DIR', __DIR__);


// Load Vendor Libs
require_once(WEBLING_PLUGIN_DIR . '/vendor/autoload.php');

require_once(WEBLING_PLUGIN_DIR . '/src/setup/deactivate.php');
require_once(WEBLING_PLUGIN_DIR . '/src/setup/setup.php');
require_once(WEBLING_PLUGIN_DIR . '/src/setup/uninstall.php');
require_once(WEBLING_PLUGIN_DIR . '/src/actions/webling_custom_css.php');
require_once(WEBLING_PLUGIN_DIR . '/src/actions/webling_clear_cache.php');
require_once(WEBLING_PLUGIN_DIR . '/src/actions/webling_form_submit.php');
require_once(WEBLING_PLUGIN_DIR . '/src/shortcodes/webling_memberlist.php');
require_once(WEBLING_PLUGIN_DIR . '/src/shortcodes/webling_form.php');
require_once(WEBLING_PLUGIN_DIR . '/src/WeblingAPI/WeblingApiHelper.php');
require_once(WEBLING_PLUGIN_DIR . '/src/WeblingAPI/WordpressCache.php');


// Setup / Upgrade
define('WEBLING_DB_VERSION', 5);
register_activation_hook( __FILE__ , 'webling_plugin_setup');
register_deactivation_hook( __FILE__ , 'webling_plugin_deactivate');
register_uninstall_hook( __FILE__ , 'webling_plugin_uninstall');
add_action('plugins_loaded', 'webling_plugin_update_check');


// Load Admin
if (is_admin()){
	require_once(WEBLING_PLUGIN_DIR . '/src/admin/admin.php');
}


// Register Shortcodes
add_shortcode('webling_memberlist', ['webling_memberlist_shortcode', 'handler']);
add_shortcode('webling_form', ['webling_form_shortcode', 'handler']);

// Shortcode submit handler
add_action('init', 'webling_form_submit');

// Add webling css
add_action('wp_head', 'webling_custom_css');
