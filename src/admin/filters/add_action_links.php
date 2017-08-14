<?php
function webling_add_action_links($links) {
	$mylinks = array(
		'<a href="' . admin_url( 'admin.php?page=webling_page_settings' ) . '">Einstellungen</a>',
	);
	return array_merge($links, $mylinks);
}