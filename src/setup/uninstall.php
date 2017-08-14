<?php

function webling_plugin_uninstall() {
	// removing options
	// we don't want an apikey to be laying around in the database
	// the cache should already be cleared by the deactivation hook
	// we keep the webling tables, they don't contain sensitive data
	delete_option('webling-db-version');
	delete_option('webling-cache-state');
	delete_option('webling-options');
}
