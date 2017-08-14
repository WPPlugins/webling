<?php
/**
 * Handler to add custom css to the page head
 *
 * @return string Custom Webling Plugin CSS
 */
function webling_custom_css(){
	$options = get_option('webling-options');

	$css = file_get_contents(WEBLING_PLUGIN_DIR . '/css/frontend.css');
	echo '<style type="text/css"><!--' . $css;
	if (isset($options['css'])) {
		echo "\n" . $options['css'] . "\n";
	}
	echo '--></style>';
}
