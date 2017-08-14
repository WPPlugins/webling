<?php

function webling_admin_save_form()
{
	global $wpdb;

	$_POST = stripslashes_deep($_POST);

	// sanitize id
	$id = intval($_POST['form_id']);

	if ($id) {

		// check if form exists
		$existing = $wpdb->get_row("SELECT id from {$wpdb->prefix}webling_forms WHERE id = ".$id);
		if (!$existing) {
			die('Could not update form: form does not exist: '.$id);
		}

		// update form
		$wpdb->query(
			$wpdb->prepare("
				UPDATE {$wpdb->prefix}webling_forms
				SET 
				`title` = %s,
				`group_id` = %d,
				`notification_email` = %s,
				`confirmation_text` = %s,
				`submit_button_text` = %s,
				`confirmation_email_enabled` = %d,
				`confirmation_email_subject` = %s,
				`confirmation_email_text` = %s,
				`confirmation_email_webling_field` = %d,
				`class` = %s
				WHERE id = %d",
				$_POST['title'],
				intval($_POST['group_id']),
				$_POST['notification_email'],
				$_POST['confirmation_text'],
				$_POST['submit_button_text'],
				($_POST['confirmation_email_enabled'] ? 1 : 0),
				$_POST['confirmation_email_subject'],
				$_POST['confirmation_email_text'],
				intval($_POST['confirmation_email_webling_field']),
				$_POST['class'],
				$id
			)
		);

	} else {
		// create form
		$wpdb->query(
			$wpdb->prepare("
				INSERT INTO {$wpdb->prefix}webling_forms
				(
					`title`,
					`group_id`,
					`notification_email`,
					`confirmation_text`,
					`submit_button_text`,
					`confirmation_email_enabled`,
					`confirmation_email_subject`,
					`confirmation_email_text`,
					`confirmation_email_webling_field`,
					`class`
				) VALUES (
					%s,
					%d,
					%s,
					%s,
					%s,
					%d,
					%s,
					%s,
					%d,
					%s
				)",
				$_POST['title'],
				intval($_POST['group_id']),
				$_POST['notification_email'],
				$_POST['confirmation_text'],
				$_POST['submit_button_text'],
				($_POST['confirmation_email_enabled'] ? 1 : 0),
				$_POST['confirmation_email_subject'],
				$_POST['confirmation_email_text'],
				intval($_POST['confirmation_email_webling_field']),
				$_POST['class']
			)
		);

		// update created_at field
		$wpdb->query(
			$wpdb->prepare("
				UPDATE {$wpdb->prefix}webling_forms set `created_at` = `updated_at` WHERE id = %d",
				$wpdb->insert_id
			)
		);

		// set $id to newly created id
		$id = $wpdb->insert_id;
	}

	$wpdb->query("DELETE FROM {$wpdb->prefix}webling_form_fields WHERE form_id = ".$id);

	if (is_array($_POST['fields'])) {
		foreach ($_POST['fields'] as $field_id => $field) {
			if (intval($field['order']) > 0) {
				$required = $show_all_groups = (isset($field['required']) ? 1 : 0);
				$wpdb->query(
					$wpdb->prepare("
						INSERT INTO {$wpdb->prefix}webling_form_fields
						(
							`form_id`,
							`order`,
							`webling_field_id`,
							`required`,
							`field_name`,
							`field_name_position`,
							`placeholder_text`,
							`description_text`,
							`class`
						) VALUES (
							%d,
							%d,
							%d,
							%d,
							%s,
							%s,
							%s,
							%s,
							%s
						)",
						$id,
						intval($field['order']),
						intval($field['webling_field_id']),
						$required,
						$field['field_name'],
						$field['field_name_position'],
						$field['placeholder_text'],
						$field['description_text'],
						$field['class']
					)
				);
			}
		}
	}

	wp_redirect(admin_url('admin.php?page=webling_page_main'));
	exit;
}
