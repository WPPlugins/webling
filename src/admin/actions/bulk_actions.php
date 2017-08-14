<?php

function webling_bulk_actions()
{
	// f*** yeah, is this really the way to go, WP?
	if (isset($_POST['action']) || isset($_POST['action2'])) {
		if ((isset($_POST['action']) && $_POST['action'] == 'webling_form_bulk_delete')
			|| (isset($_POST['action2']) && $_POST['action2'] == 'webling_form_bulk_delete')
		) {
			$forms = new Form_List();
			$forms->process_bulk_action();
		}

		if ((isset($_POST['action']) && $_POST['action'] == 'webling_list_bulk_delete')
			|| (isset($_POST['action2']) && $_POST['action2'] == 'webling_list_bulk_delete')
		) {
			$memberlist = new Memberlist_List();
			$memberlist->process_bulk_action();
		}
	}
}
