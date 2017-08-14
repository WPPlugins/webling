<?php
/**
 * Handler to clear the webling api cache
 *
 * @return void
 */
function webling_clear_cache(){
	try {
		WeblingApiHelper::Instance()->clearCache();
	} catch (Exception $e) {
		// no problem, this may happen...
	}
}
