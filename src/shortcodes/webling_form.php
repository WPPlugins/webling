<?php

class webling_form_shortcode {

	/**
	 * Shortcode Handler for [webling_form]
	 *
	 * @param $atts array shortcode attributes
	 * @return string HTML code for the from
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

			// Load form config
			$id = intval($attributes['id']);
			$config = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}webling_forms WHERE id = " . esc_sql($id), 'ARRAY_A');
			if (!$config) {
				throw new Exception('No form with ID '.$id.' found');
			}

			// Load form data
			try {

				if (isset($_REQUEST['webling_form_submitted'])) {
					if ($_REQUEST['webling_form_submitted'] == 'true') {
						return '<div class="webling-form webling-form__submitted '.esc_attr($config['class']).'">'
							. $config['confirmation_text']
							. '</div>';
					} else {
						return '<div class="webling-form webling-form__error '.esc_attr($config['class']).'">'
							. __('Sorry, Das Formular konnte nicht gesendet werden, beim Verarbeiten der Daten ist ein Fehler aufgetreten. Versuche es später noch einmal.', 'webling')
							. '</div>';
					}

				} else {
					return '<div class="webling-form '.esc_attr($config['class']).'">'
						. self::get_html($config)
						. '</div>';
				}

			} catch (\Webling\API\ClientException $exception) {
				throw new Exception('Connection Error: '.$exception->getMessage());
			}
		} catch (Exception $exception) {
			if ( WP_DEBUG ) {
				trigger_error($exception->getMessage());
			}
			return '<p class="webling-form__error">[webling_form] Fehler: '.$exception->getMessage().'</p>';
		}

	}

	/**
	 * @param $formconfig array - form configuration from database
	 * @return string HTML Markup
	 * @throws Exception
	 */
	protected static function get_html($formconfig) {
		global $wpdb;
		global $wp;

		// load field definitions
		$definitions = WeblingApiHelper::Instance()->getMemberFieldDefinitionsById();
		$sql = "SELECT * FROM {$wpdb->prefix}webling_form_fields WHERE form_id = " . esc_sql($formconfig['id']) . " ORDER BY `order` ASC";
		$formfields = $wpdb->get_results($sql, 'ARRAY_A');

		if (!is_array($formfields) || count($formfields) == 0) {
			throw new Exception('Keine Formularfelder konfiguriert');
		}

		$html = '';

		foreach ($formfields as $field) {
			switch ($field['field_name_position']){
				case 'HIDDEN':
					$positionclass = 'webling-form__group--hidden';
					break;
				case 'LEFT':
					$positionclass = 'webling-form__group--left';
					break;
				case 'TOP':
				default:
					$positionclass = 'webling-form__group--top';
			}

			$html .= '<div class="webling-form__group '.$positionclass.'">';

			$html .= '<label for="webling-form-field-' . $field['id'] . '" class="webling-form__label">';
			$html .= esc_html($field['field_name']);
			if ($field['required']) {
				$html .= ' <span class="webling-form__required">*</span>';
			}
			$html .= '</label>';

			$html .= '<div class="webling-form__field">';
			$html .= self::getInputForType($field, $definitions);
			if ($field['description_text']) {
				$html .= '<small class="webling-form__description">'.esc_html($field['description_text']).'</small>';
			}
			$html .= '</div>';
			$html .= '</div>';
		}

		$form  = '<form action="?" method="post">';
		$form .= '<input type="hidden" name="webling-form-id" value="'.$formconfig['id'].'"/>';
		$form .= '<input type="hidden" name="webling-form-redirect" value="'.esc_attr(get_permalink()).'"/>';
		$form .= $html;

		// add honeypot field ("display: none" via css)
		$form .= '<input type="text" id="webling-form-field_0" autocomplete="off" name="webling-form-field[0]" value=""/>';

		$form .= '<p><input type="submit" value="'.esc_attr($formconfig['submit_button_text']).'" class="webling-form__submit"></p>';
		$form .= '</form>';

		return $form;
	}

	protected static function getInputForType($field, $definitions) {
		$datatype = $definitions[$field['webling_field_id']]['datatype'];
		$label = (isset($definitions[$field['webling_field_id']]['type']) ? $definitions[$field['webling_field_id']]['type'] : '');
		$required = ($field['required'] ? 'required' : '');
		$id = 'webling-form-field-'.$field['id'];
		$name = 'webling-form-field['.$field['id'].']';
		$class = 'webling-form__input';
		$placeholder = ($field['placeholder_text'] ? esc_attr($field['placeholder_text']) : '');
		switch ($datatype){
			case 'multienum':
				$options = [];
				if (isset($definitions[$field['webling_field_id']]['values'])) {
					foreach ($definitions[$field['webling_field_id']]['values'] as $value) {
						$options[] = '<div><label><input type="checkbox" class="webling-form__checkbox" name="'.$name.'['.base64_encode($value).']"/> '.esc_html($value).'</label></div>';
					}
				}
				return '<div class="webling-form__multiselect" >'.implode($options).'</div>';
			case 'enum':
				$options = [];
				if (isset($definitions[$field['webling_field_id']]['values'])) {

					if ($placeholder) {
						$options[] = '<option value="">'.esc_html($field['placeholder_text']).'</option>';
					}
					foreach ($definitions[$field['webling_field_id']]['values'] as $value) {
						$options[] = '<option value="'.esc_attr($value).'">'.esc_html($value).'</option>';
					}
				}
				return '<select name="' . $name . '" id="' . $id . '" class="webling-form__select" ' . $required . '>'.implode($options).'</select>';
			case 'bool':
				return '<span>'
					. '<label>'
					. '<input type="checkbox" class="webling-form__checkbox" name="' . $name . '" id="' . $id . '" ' . $required . '> '
					. esc_html($field['placeholder_text']).' '
					.'</label></span>';
			case 'date':
				return '<input type="date" name="' . $name . '" id="' . $id . '" placeholder="TT.MM.JJJJ" class="'.$class.'" ' . $required . '>';
			case 'longtext':
				return '<textarea rows="6" name="' . $name . '" id="' . $id . '" placeholder="'.$placeholder.'" class="'.$class.'" '.$required.'></textarea>';
			case 'int':
				return '<input type="number" step="1" name="'.$name.'" id="'.$id.'" placeholder="'.$placeholder.'" class="'.$class.'" '.$required.'>';
			case 'numeric':
				return '<input type="number" step="any" name="'.$name.'" id="'.$id.'" placeholder="'.$placeholder.'" class="'.$class.'" '.$required.'>';
			case 'text':
				switch ($label) {
					case 'phone':
					case 'mobile':
						return '<input type="tel" name="' . $name . '" id="' . $id . '" placeholder="' . $placeholder . '" class="'.$class.'" ' . $required . '>';
					case 'url':
						return '<input type="url" name="' . $name . '" id="' . $id . '" placeholder="' . $placeholder . '" class="'.$class.'" ' . $required . '>';
					case 'email':
						return '<input type="email" name="' . $name . '" id="' . $id . '" placeholder="' . $placeholder . '" class="'.$class.'" ' . $required . '>';
					default:
						return '<input type="text" name="' . $name . '" id="' . $id . '" placeholder="' . $placeholder . '" class="'.$class.'" ' . $required . '>';
				}
			case 'autoincrement':
			case 'image':
			case 'file':
			default:
				// autoincrement, images and files are currently not supported
				return '';
		}
	}
}
