<?php

function webling_admin_init()
{
	register_setting('webling-options-group', 'webling-options');

	// register cache state option
	register_setting('webling-cache', 'webling-cache-state');
}
