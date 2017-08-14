<?php

class webling_page_settings {

	public static function html () {

		add_settings_section(
			'webling_page_settings_section',
			__( 'Webling Plugin Einstellungen', 'webling' ),
			['webling_page_settings','section_callback'],
			'webling-options-group'
		);

		add_settings_field(
			'webling_field_host',
			__( 'Webling-URL', 'webling' ),
			['webling_page_settings','host_render'],
			'webling-options-group',
			'webling_page_settings_section'
		);

		add_settings_field(
			'webling_field_apikey',
			__( 'API Key', 'webling' ),
			['webling_page_settings','apikey_render'],
			'webling-options-group',
			'webling_page_settings_section'
		);

		add_settings_field(
			'webling_field_css',
			__( 'Eigenes CSS', 'webling' ),
			['webling_page_settings','css_render'],
			'webling-options-group',
			'webling_page_settings_section'
		);

		// display form
		echo '<div class="wrap"><form method="post" action="options.php" id="webling-form">';
		settings_fields('webling-options-group');
		do_settings_sections('webling-options-group');
		submit_button();
		self::print_versions();
		echo '</form></div>';
	}

	public static function host_render() {

		$options = get_option('webling-options');
		echo '<input type="text" name="webling-options[host]" value="'.$options['host'].'" style="width: 400px;">';
		echo '<p class="description">'.__('Die Adresse deines Weblings (z.B. demo1.webling.ch)', 'webling').'</p>';
	}


	public static function apikey_render() {
		$options = get_option('webling-options');
		echo '<input type="text" name="webling-options[apikey]" value="'.$options['apikey'].'" style="width: 400px;">';
		echo '<p class="description">'.__('Einen API-Key kannst du dir in deinem Webling unter "Administration" &raquo; "API" erstellen.', 'webling').'</p>';
	}


	public static function css_render() {
		$options = get_option('webling-options');
		echo '<textarea rows="5" cols="60" name="webling-options[css]">'.$options['css'].'</textarea>';
		echo '<p class="description">'.__('Eigenes CSS für Designanpassungen.', 'webling').'</p>';
	}


	public static function section_callback() {
		echo __( 'Mit diesem Plugin kannst du Mitgliederdaten aus deiner <a href="https://www.webling.eu" target="_blank">Webling Vereinsverwaltung</a> auf einer Seite anzeigen lassen oder via Anmeldeformular automatisch erfassen.', 'webling' );
		echo '<br>';
		echo __( 'Es wird mindestens ein "Webling Plus" Abo benötigt, damit dieses Plugin verwendet werden kann.', 'webling' );
		echo '<br><br>';
		echo '<b>'.__( 'Verbindungsstatus: ', 'webling' ) . '</b> '. self::connection_status();
	}

	public static function print_versions() {
		global $wpdb, $wp_version;
		echo '<div style="font-size: 90%; color: rgba(0,0,0,0.5);">';
		$plugin = get_plugin_data(WEBLING_PLUGIN_DIR . '/webling.php', false);
		echo 'Versionsinfo: PHP ' . phpversion() . '; MySQL ' . $wpdb->db_version() . '; WordPress ' . $wp_version . '; Webling Plugin ' . $plugin['Version']. '; Webling DB v' . WEBLING_DB_VERSION . ';';
		echo '</div>';
	}

	public static function connection_status() {
		$options = get_option('webling-options');

		if (!$options['host'] || !$options['apikey']) {
			return '<span style="color: grey;">Keine Zugangsdaten. Bitte Webling-URL und API Key angeben.</span>';
		}
		try {
			$connection = new \Webling\API\Client($options['host'], $options['apikey']);
			$response = $connection->get('config');
			if ($response->getStatusCode() == '200') {
				return '<span style="color: #79c700;">Verbindung OK</span>';
			} else {
				if ($response->getStatusCode() == '401') {
					return '<span style="color: red;">Ungültige Zugangsdaten.</span>';
				} else {
					$data = $response->getData();
					$error = '';
					if (isset($data['error'])) {
						$error = ' - ' . $data['error'];
					}
					return '<span style="color: red;">Verbindungsfehler: '.$response->getStatusCode().' '.$error.'</span>';
				}
			}
		} catch (\Webling\API\ClientException $exception) {
			return '<span style="color: red;">Verbindungsfehler: '.$exception->getMessage().'</span>';
		}
	}
}
