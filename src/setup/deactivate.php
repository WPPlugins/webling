<?php

function webling_plugin_deactivate() {
	// clear cache when deactivating plugin
	try {
		WeblingApiHelper::Instance()->clearCache();
	} catch(Exception $e) {
		// ignore any errors
	}
}
