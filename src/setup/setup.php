<?php


function webling_plugin_setup() {
	webling_plugin_update_check();
}

function webling_plugin_update_check() {
	$installed_db_version = get_option('webling-db-version', 0);

	// abort if the installed version is the current version
	if ($installed_db_version == WEBLING_DB_VERSION) {
		return;
	}

	if ($installed_db_version < 2) {
		webling_plugin_initial_setup();
	}
	if ($installed_db_version < 3) {
		webling_plugin_setup_upgrade_v3();
	}
	if ($installed_db_version < 4) {
		webling_plugin_setup_upgrade_v4();
	}
	if ($installed_db_version < 5) {
		webling_plugin_setup_upgrade_v5();
	}
}

// ************* Version Upgrade Functions *************


// upgrade to db version 5
// add custom memberlist template fields
function webling_plugin_setup_upgrade_v5() {
	global $wpdb;

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$charset_collate = $wpdb->get_charset_collate();

	// update memberlists table
	// add custom template fields
	$table_name = $wpdb->prefix . 'webling_memberlists';
	$sql = "CREATE TABLE $table_name (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`title` varchar(255) DEFAULT NULL,
		`show_all_groups` tinyint(1) NOT NULL DEFAULT '0',
		`groups` text,
		`fields` text,
		`sortorder` enum('ASC','DESC') DEFAULT 'ASC',
		`sortfield` varchar(255) DEFAULT NULL,
		`class` varchar(255) DEFAULT NULL,
		`updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		`created_at` timestamp NULL DEFAULT NULL,
		`design` enum('LIST','CUSTOM') NOT NULL DEFAULT 'LIST',
		`custom_template` text,
		PRIMARY KEY (`id`)
	) $charset_collate;";
	dbDelta( $sql );

	update_option("webling-db-version", 5);
}

// upgrade to db version 4
// fixing schema for MySQL 5.5 (can not have more than one AUTO CURRENT_TIMESTAMP field)
function webling_plugin_setup_upgrade_v4() {
	global $wpdb;

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$charset_collate = $wpdb->get_charset_collate();

	// update forms table (or create if it failed till now)
	// only updated_at field is now auto-updating
	$table_name = $wpdb->prefix . 'webling_forms';
	$sql = "CREATE TABLE $table_name (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`title` varchar(255) DEFAULT '',
		`group_id` int(11) NOT NULL,
		`notification_email` varchar(255) DEFAULT NULL,
		`confirmation_email_enabled` tinyint(1) NOT NULL DEFAULT '0',
		`confirmation_email_webling_field` int(11) DEFAULT NULL,
		`confirmation_email_subject` varchar(255) DEFAULT '',
		`confirmation_email_text` text,
		`confirmation_text` text,
		`submit_button_text` varchar(255) DEFAULT NULL,
		`class` varchar(255) DEFAULT NULL,
		`updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		`created_at` timestamp NULL DEFAULT NULL,
		PRIMARY KEY (`id`)
		) $charset_collate;";
	dbDelta( $sql );

	// change definitions on old installations
	$wpdb->query( "ALTER TABLE $table_name MODIFY COLUMN `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP" );
	$wpdb->query( "ALTER TABLE $table_name MODIFY COLUMN `created_at` timestamp NULL DEFAULT NULL" );

	// update memberlists table (or create if it failed till now)
	// only updated_at field is now auto-updating
	$table_name = $wpdb->prefix . 'webling_memberlists';
	$sql = "CREATE TABLE $table_name (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`title` varchar(255) DEFAULT NULL,
		`show_all_groups` tinyint(1) NOT NULL DEFAULT '0',
		`groups` text,
		`fields` text,
		`sortorder` enum('ASC','DESC') DEFAULT 'ASC',
		`sortfield` varchar(255) DEFAULT NULL,
		`class` varchar(255) DEFAULT NULL,
		`updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		`created_at` timestamp NULL DEFAULT NULL,
		PRIMARY KEY (`id`)
	) $charset_collate;";
	dbDelta( $sql );

	// change definitions on old installations
	$wpdb->query( "ALTER TABLE $table_name MODIFY COLUMN `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP" );
	$wpdb->query( "ALTER TABLE $table_name MODIFY COLUMN `created_at` timestamp NULL DEFAULT NULL" );

	// update form fields table (or create if it failed till now)
	// remove updated_at and created_at fields, there is no use for them
	$table_name = $wpdb->prefix . 'webling_form_fields';
	$sql = "CREATE TABLE $table_name (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`form_id` int(11) NOT NULL,
		`order` int(11) NOT NULL,
		`webling_field_id` int(11) NOT NULL,
		`type` varchar(50) NOT NULL DEFAULT 'text',
		`required` tinyint(1) NOT NULL DEFAULT '0',
		`field_name` varchar(255) NOT NULL DEFAULT '',
		`field_name_position` enum('TOP','LEFT','HIDDEN') NOT NULL DEFAULT 'TOP',
		`placeholder_text` varchar(255) DEFAULT NULL,
		`description_text` text,
		`class` varchar(255) DEFAULT NULL,
		PRIMARY KEY (`id`),
		KEY `form_id` (`form_id`)
		) $charset_collate;";
	dbDelta( $sql );

	// remove updated_at and created_at on existing installations
	$table_name = $wpdb->prefix . 'webling_form_fields';
	$wpdb->query( "ALTER TABLE $table_name DROP COLUMN `updated_at`" );
	$wpdb->query( "ALTER TABLE $table_name DROP COLUMN `created_at`" );

	update_option("webling-db-version", 4);
}


// upgrade to db version 3
// add confirmation email fields
function webling_plugin_setup_upgrade_v3() {
	global $wpdb;

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$charset_collate = $wpdb->get_charset_collate();

	// update forms table
	$table_name = $wpdb->prefix . 'webling_forms';
	$sql = "CREATE TABLE $table_name (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`title` varchar(255) DEFAULT '',
		`group_id` int(11) NOT NULL,
		`notification_email` varchar(255) DEFAULT NULL,
		`confirmation_email_enabled` tinyint(1) NOT NULL DEFAULT '0',
		`confirmation_email_webling_field` int(11) DEFAULT NULL,
		`confirmation_email_subject` varchar(255) DEFAULT '',
		`confirmation_email_text` text,
		`confirmation_text` text,
		`submit_button_text` varchar(255) DEFAULT NULL,
		`class` varchar(255) DEFAULT NULL,
		`updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
		`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`)
		) $charset_collate;";
	dbDelta( $sql );

	update_option("webling-db-version", 3);
}

// upgrade to db version 2
function webling_plugin_initial_setup() {
	global $wpdb;

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$charset_collate = $wpdb->get_charset_collate();

	// create cache table
	$table_name = $wpdb->prefix . 'webling_cache';
	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL,
		type VARCHAR(20) NOT NULL,
		data text NOT NULL,
		PRIMARY KEY (id)
	) $charset_collate;";
	dbDelta( $sql );

	// create memberlists table
	$table_name = $wpdb->prefix . 'webling_memberlists';
	$sql = "CREATE TABLE $table_name (
		id int(11) unsigned NOT NULL AUTO_INCREMENT,
		title varchar(255) DEFAULT NULL,
		show_all_groups tinyint(1) NOT NULL DEFAULT '0',
		groups text,
		fields text,
		sortorder enum('ASC','DESC') DEFAULT 'ASC',
		sortfield varchar(255) DEFAULT NULL,
		class varchar(255) DEFAULT NULL,
		updated_at timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
		created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`)
	) $charset_collate;";
	dbDelta( $sql );

	// create forms table
	$table_name = $wpdb->prefix . 'webling_forms';
	$sql = "CREATE TABLE $table_name (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`title` varchar(255) DEFAULT '',
		`group_id` int(11) NOT NULL,
		`notification_email` varchar(255) DEFAULT NULL,
		`confirmation_text` text,
		`submit_button_text` varchar(255) DEFAULT NULL,
		`class` varchar(255) DEFAULT NULL,
		`updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
		`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`)
		) $charset_collate;";
	dbDelta( $sql );

	// create form fields table
	$table_name = $wpdb->prefix . 'webling_form_fields';
	$sql = "CREATE TABLE $table_name (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`form_id` int(11) NOT NULL,
		`order` int(11) NOT NULL,
		`webling_field_id` int(11) NOT NULL,
		`type` varchar(50) NOT NULL DEFAULT 'text',
		`required` tinyint(1) NOT NULL DEFAULT '0',
		`field_name` varchar(255) NOT NULL DEFAULT '',
		`field_name_position` enum('TOP','LEFT','HIDDEN') NOT NULL DEFAULT 'TOP',
		`placeholder_text` varchar(255) DEFAULT NULL,
		`description_text` text,
		`class` varchar(255) DEFAULT NULL,
		`updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
		`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`),
		KEY `form_id` (`form_id`)
		) $charset_collate;";
	dbDelta( $sql );


	// migrate old shortcodes to new format
	$defaultOptions = array(
		'host' => '',
		'apikey' => '',
		'css' => ''
	);
	$options = get_option('webling-options', $defaultOptions);
	if (isset($options['fields'])) {
		$old_fields = explode(',', $options['fields']);
	} else {
		$old_fields = ['Vorname'];
	}
	$firstfield = $old_fields[0];


	$sql = "SELECT * FROM {$wpdb->prefix}posts WHERE post_content LIKE '%[webling_memberlist%' 
		and (
			post_status = 'publish' or 
			post_status = 'draft' or 
			post_status = 'pending' or
			post_status = 'private' or
			post_status = 'future'
		)";
	$posts = $wpdb->get_results($sql, 'ARRAY_A');
	foreach ($posts as $post) {
		$original_content = $post['post_content'];
		$hasError = false;

		preg_match_all("/\[webling_memberlist(.*)\]/", $post['post_content'], $matches);
		foreach ($matches[0] as $index => $match) {
			$shortcode = $match;
			$arguments = shortcode_parse_atts($matches[1][$index]);

			// abort if there is already an id argument (migration probably done aready)
			if (isset($arguments['id'])) {
				$hasError = true;
				continue;
			}

			$groups = array();
			if (isset($arguments['groups'])) {
				$groupIds = explode(',', $arguments['groups']);
				if(is_array($groupIds)) {
					foreach ($groupIds as $groupId) {
						if (intval($groupId) > 0) {
							$groups[] = intval($groupId);
						}
					}
				}
			}

			// create new list
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
						`sortorder`
					) VALUES (
						%s,
						%s,
						%s,
						%s,
						%s,
						%s,
						%s
					)",
					'Mitgliederliste',
					(count($groups) > 0 ? 0 : 1),
					serialize($groups),
					json_encode($old_fields),
					'',
					$firstfield,
					'ASC'
				)
			);

			$list_id = $wpdb->insert_id;
			$new_shortcode = '[webling_memberlist id="'.$list_id.'"]';

			$pos = strpos($original_content, $shortcode);
			if ($pos !== false) {
				$original_content = substr_replace($original_content, $new_shortcode, $pos, strlen($shortcode));
			}
		}

		if (!$hasError) {
			wp_update_post(
				[
					'ID' => $post['ID'],
					'post_content' => $original_content,
				]
			);

			// add a new revision as a backup if anything goes wrong
			wp_save_post_revision($post['ID']);
		}
	}

	// unset "fields" in webling-options as it is not used anymore
	unset($options['fields']);
	update_option('webling-options', $options);

	// update version
	add_option("webling-db-version", 2);
	update_option("webling-db-version", 2);
}
