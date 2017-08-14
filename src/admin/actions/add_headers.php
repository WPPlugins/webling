<?php

function webling_admin_add_headers($hook)
{
	// only on webling admin pages
	if (substr($hook,0,7) == 'webling' || $hook == 'toplevel_page_webling_page_main') {

		// Plugin
		wp_enqueue_style('webling_admin_css', plugins_url('css/admin.css?pluginver='.WEBLING_DB_VERSION, WEBLING_PLUGIN_FILE_NAME));
		wp_enqueue_script('webling_admin_js', plugins_url('/js/admin.js?pluginver='.WEBLING_DB_VERSION, WEBLING_PLUGIN_FILE_NAME));

		// jQuery UI
		wp_enqueue_style('webling_admin_css_jquery_ui', plugins_url('js/jquery-ui-1.12.1.custom/jquery-ui.min.css?pluginver='.WEBLING_DB_VERSION, WEBLING_PLUGIN_FILE_NAME));
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-sortable');
	}
}
