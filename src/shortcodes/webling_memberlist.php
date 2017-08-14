<?php

class webling_memberlist_shortcode {

	/**
	 * Shortcode Handler for [webling_memberlist]
	 *
	 * @param $atts array shortcode attributes
	 * @return string HTML code for the memberlist
	 */
	public static function handler($atts) {

		global $wpdb;
		try {
			// filter shortcode attributes
			$attributes = shortcode_atts( array(
				'id' => null
			), $atts );

			if (!$attributes['id']) {
				throw new Exception('No ID in shortcode');
			}

			// Load memberlist config
			$id = intval($attributes['id']);
			$config = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}webling_memberlists WHERE id = " . esc_sql($id), 'ARRAY_A');
			if (!$config) {
				throw new Exception('No memberlist with ID '.$id.' found');
			}

			// Load memberlist data
			try {
				return '<div class="webling_memberlist '.esc_attr($config['class']).'">'
					. self::get_html($config)
					. '</div>';

			} catch (\Webling\API\ClientException $exception) {
				throw new Exception('Connection Error: '.$exception->getMessage());
			}
		} catch (Exception $exception) {
			if ( WP_DEBUG ) {
				trigger_error($exception->getMessage());
			}
			return '<p class="webling_memberlist-error">[webling_memberlist] Fehler: '.$exception->getMessage().'</p>';
		}

	}

	/**
	 * @param $listconfig array - list configuration from database
	 * @return string HTML Markup
	 * @throws Exception
	 */
	protected static function get_html($listconfig) {

		$options = get_option('webling-options');
		$fields = json_decode($listconfig['fields']);

		if (!isset($options['host']) || !isset($options['apikey'])) {
			throw new Exception('Zugangsdaten überprüfen');
		}

		$client = new \Webling\API\Client($options['host'], $options['apikey'], array('useragent' => WeblingApiHelper::Instance()->getUserAgent()));
		$apiCache = new \Webling\Cache\WordpressCache($client);

		// load field definitions
		$definitions = $apiCache->getRoot('definition');
		$memberfields = $definitions['member']['properties'];

		if(count($fields) == 0){
			throw new Exception('Keine Felder angegeben');
		}

		// collect memberIds
		$memberIds = array();
		if ($listconfig['show_all_groups']) {
			$data = $apiCache->getRoot("member");
			if (isset($data['objects']) && is_array($data['objects'])) {
				$memberIds = $data['objects'];
			}
		} else {
			if($listconfig['groups']){
				// filter groups
				$groupIds = unserialize($listconfig['groups']);
				if(is_array($groupIds) && count($groupIds)){
					foreach ($groupIds as $groupId){
						if(trim($groupId) != ''){
							$data = $apiCache->getObject("membergroup", intval($groupId));
							if($data) {
								if (isset($data["children"]['member']) && is_array($data["children"]['member'])){
									$memberIds = array_merge($memberIds, $data["children"]['member']);
								}
							} else {
								throw new Exception('Gruppe nicht gefunden: '.$groupId);
							}
						}
					}
					$memberIds = array_unique($memberIds);
				}
			}
		}

		// fetch member data
		$memberdata = array();
		if(count($memberIds) == 0){
			$output = '<p class="webling_memberlist-empty">Keine Mitglieder.</p>';
		} else {
			foreach ($memberIds as $memberId) {
				$memberdata[] = $apiCache->getObject("member", $memberId);

			}
		}

		// sort member
		usort($memberdata, function ($a, $b) use ($listconfig) {
			$field = $listconfig['sortfield'];
			if ($a["properties"][$field] == $b["properties"][$field]) {
				return 0;
			}
			return ($a["properties"][$field] < $b["properties"][$field]) ? -1 : 1;
		});
		if ($listconfig['sortorder'] == 'DESC') {
			$memberdata = array_reverse($memberdata);
		}


		if ($listconfig['design'] == 'CUSTOM') {
			return self::render_html_custom($memberdata, $memberfields, $listconfig['custom_template']);
		} else {
			return self::render_html_list($memberdata, $fields, $memberfields);
		}
	}

	/**
	 * Returns a memberlist in default list style
	 * @param $memberdata - member data
	 * @param $fields - fields to show in current list
	 * @param $memberfields - member field definitions
	 * @return string html
	 */
	protected static function render_html_list($memberdata, $fields, $memberfields) {
		$output  = '<table class="webling_memberlist-table">';
		$output .= '<tr>';
		foreach ($fields as $field) {
			$output .= '<th>' . $field . '</th>';
		}
		$output .= '</tr>';

		// display member data
		foreach ($memberdata as $member) {
			$output .= '<tr>';
			foreach ($fields as $field) {
				$output .= "<td>";
				$type = (isset($memberfields[$field]['type']) ? $memberfields[$field]['type'] : null);
				$output .= self::field_formatter($member["properties"][$field], $memberfields[$field]['datatype'], $type);
				$output .= "</td>";
			}
			$output .= '</tr>';
		}
		$output .= '</table>';

		return $output;
	}

	/**
	 * Returns a memberlist rendered with a custom html template
	 * @param $memberdata - member data
	 * @param $memberfields - member field definitions
	 * @param $template - custom html template
	 * @return string html
	 */
	protected static function render_html_custom($memberdata, $memberfields, $template) {
		// display member data
		$output = '';
		foreach ($memberdata as $member) {
			$tpl = $template;
			if (is_array($member['properties'])) {
				// replace placeholders with formatted values
				foreach ($member['properties'] as $field => $value) {
					$type = (isset($memberfields[$field]['type']) ? $memberfields[$field]['type'] : null);
					$formatted_value = self::field_formatter($value, $memberfields[$field]['datatype'], $type);
					$tpl = str_replace('[['.$field.']]', $formatted_value, $tpl);
				}
			}
			$output .= $tpl;
		}
		return $output;
	}

	/**
	 * Formats a value according to it's type
	 *
	 * @param $value mixed raw value
	 * @param $datatype string the datatype of the value
	 * @param $label string label of the value
	 * @return string formatted string
	 */
	protected static function field_formatter($value, $datatype, $label){
		switch ($datatype){
			case 'numeric':
				return number_format((float)$value, 2);
				break;
			case 'bool':
				return ($value == true ? 'Ja' : 'Nein');
				break;
			case 'date':
				$time = strtotime($value);
				if($time !== false){
					return date('d.m.Y', $time);
				} else {
					return '';
				}
				break;
			case 'multienum':
				if(is_array($value)){
					return implode(', ', $value);
				}
				return '';
				break;
			case 'image':
			case 'file':
				// images and files are currently not supported
				return '';
				break;
			case 'text':
				switch ($label){
					case 'url':
						if($value){
							$prefixed_url = $value;
							if ($ret = parse_url($prefixed_url)) {
								if (!isset($ret["scheme"])) {
									$prefixed_url = 'http://'.$prefixed_url;
								}
							}
							return '<a href="'.$prefixed_url.'" target="_blank">'.$value.'</a>';
						} else {
							return $value;
						}
						break;
					default:
						return $value;
				}
				break;
			case 'longtext':
			case 'int':
			case 'autoincrement':
			case 'enum':
			default:
				if(is_array($value)) {
					return "&nbsp;";
				}
				return $value;
		}
	}
}
