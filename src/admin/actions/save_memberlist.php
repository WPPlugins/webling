<?php

function webling_admin_save_memberlist()
{
	global $wpdb;

	$_POST = stripslashes_deep($_POST);

	// sanitize id
	$id = intval($_POST['list_id']);

	// sanitize design
	$avaliable_designs = array('LIST', 'CUSTOM');
	if (!in_array($_POST['design'], $avaliable_designs)) {
		$_POST['design'] = 'LIST';
	}

	// sanitize show_all_groups
	$show_all_groups = (isset($_POST['show_all_groups']) ? 1 : 0);

	// serialize groups value
	if (isset($_POST['groups'])) {
		$groups = serialize(array_keys($_POST['groups']));
	} else {
		$groups = serialize(array());
	}

	if ($id) {
		// update list
		$wpdb->query(
			$wpdb->prepare("
				UPDATE {$wpdb->prefix}webling_memberlists
				SET 
				`title` = %s,
				`show_all_groups` = %s,
				`groups` = %s,
				`fields` = %s,
				`class` = %s,
				`sortfield` = %s,
				`sortorder` = %s,
				`design` = %s,
				`custom_template` = %s
				WHERE id = %d",
				$_POST['title'],
				$show_all_groups,
				$groups,
				$_POST['fields'],
				$_POST['class'],
				$_POST['sortfield'],
				$_POST['sortorder'],
				$_POST['design'],
				$_POST['custom_template'],
				$id
			)
		);
	} else {
		// create list
		$wpdb->query(
			$wpdb->prepare("
				INSERT INTO {$wpdb->prefix}webling_memberlists
				(
					`title`,
					`show_all_groups`,
					`groups`,
					`fields`,
					`class`,
					`sortfield`,
					`sortorder`,
					`design`,
					`custom_template`
				) VALUES (
					%s,
					%s,
					%s,
					%s,
					%s,
					%s,
					%s,
					%s,
					%s
				)",
				$_POST['title'],
				$show_all_groups,
				$groups,
				$_POST['fields'],
				$_POST['class'],
				$_POST['sortfield'],
				$_POST['sortorder'],
				$_POST['design'],
				$_POST['custom_template']
			)
		);

		// update created_at field
		$wpdb->query(
			$wpdb->prepare("
				UPDATE {$wpdb->prefix}webling_memberlists set `created_at` = `updated_at` WHERE id = %d",
				$wpdb->insert_id
			)
		);

	}

	wp_redirect(admin_url('admin.php?page=webling_page_memberlist_list'));
	exit;
}
