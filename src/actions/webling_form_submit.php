<?php
/**
 * save form submissions and redirect
 *
 * @return void
 * @throws Exception
 */
function webling_form_submit(){
	global $wpdb;

	if(isset($_POST['webling-form-id'])) {
		try {
			$_POST = stripslashes_deep($_POST);
			$id = intval($_POST['webling-form-id']);

			if (isset($_POST['webling-form-field'][0]) && strlen($_POST['webling-form-field'][0]) > 0) {
				// Honeypot data was submitted
				throw new Exception('Sweet, you just discovered the honeypot');
			}

			// Load form config
			$formconfig = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}webling_forms WHERE id = " . esc_sql($id), 'ARRAY_A');
			if (!$formconfig) {
				throw new Exception('No form with ID '.$id.' found');
			}

			$sql = "SELECT * FROM {$wpdb->prefix}webling_form_fields WHERE form_id = " . esc_sql($formconfig['id']) . " ORDER BY `order` ASC";
			$formfields = $wpdb->get_results($sql, 'ARRAY_A');

			$memberfields = WeblingApiHelper::Instance()->getMemberFields();
			$definitions = WeblingApiHelper::Instance()->getMemberFieldDefinitionsById();
			$client = WeblingApiHelper::Instance()->client();

			$newdata = [];
			$emaildata = [];

			foreach ($formfields as $field) {
				$fieldId = $field['id'];
				$weblingFieldId = $field['webling_field_id'];
				if (isset($_POST['webling-form-field'][$fieldId])) {
					if (isset($definitions[$weblingFieldId])) {
						$fieldDefinition = $definitions[$weblingFieldId];
						$fieldName = $memberfields[$weblingFieldId];

						$value = $_POST['webling-form-field'][$fieldId];
						switch ($fieldDefinition['datatype']){
							case 'text':
								$value = substr($value, 0, 255);
								break;
							case 'longtext':
								// leave as it is
								break;
							case 'bool':
								$value = true;
								break;
							case 'enum':
								if (!in_array($value, $fieldDefinition['values'])) {
									$value = '';
								}
								break;
							case 'multienum':
								$options = [];
								foreach ($value as $opt => $isOn) {
									$option = base64_decode($opt);
									if (in_array($option, $fieldDefinition['values'])) {
										$options[] = $option;
									}
								}
								$value = $options;
								break;
							case 'date':
								if ($value) {
									$dateparts = date_parse($value);
									if ($dateparts['year'] !== false && $dateparts['month'] !== false && $dateparts['day'] !== false ) {
										if ($dateparts['year'] < 100) {
											$dateparts['year'] += 2000;
										}
										$value = sprintf("%04d", $dateparts['year']).'-'.sprintf("%02d", $dateparts['month']).'-'.sprintf("%02d", $dateparts['day']);
									} else {
										$value = '';
									}
								}
								break;
							case 'int':
								$value = intval($value);
								break;
							case 'numeric':
								$value = floatval($value);
								break;
							case 'autoincrement':
							case 'image':
							case 'file':
							default:
								// autoincrement, images and files are currently not supported
								$value = '';
								break;
						}

						// do not send empty data to allow defaults
						if ($value) {
							$newdata[$fieldName] = $value;
						}

						// also send empty data via email
						$emaildata[$fieldName] = $value;
					}
				}
			}

			// create new member
			$newmember = [
				'properties' => $newdata,
				'parents' => [$formconfig['group_id']]
			];
			$response = $client->post('member', json_encode($newmember));
			if ($response->getStatusCode() != 201) {
				throw new Exception($response->getRawData(), $response->getStatusCode());
			}

			// send email notification if the email address is valid
			$to = sanitize_email($formconfig['notification_email']);
			if (is_email($to)) {
				$subject = __('Neue Anmeldung via Formular:', 'webling') . ' ' . $formconfig['title'];
				$body  = __("Guten Tag,\n\nDein WordPress Formular wurde mit folgenden Daten abgeschickt:", 'webling').PHP_EOL.PHP_EOL;

				foreach ($emaildata as $field => $val) {
					if (is_array($val)) {
						$body .= $field . ': ' . implode(', ', $val) . PHP_EOL;
					} else if(is_bool($val)) {
						$body .= $field . ': ' . ($val ? __('Ja', 'webling') : __('Nein', 'webling')) . PHP_EOL;
					} else {
						$body .= $field.': '.$val.PHP_EOL;
					}
				}

				$body .= PHP_EOL.PHP_EOL.__("Ein Mitglied mit diesen Daten wurde in deinem Webling erfasst.\n\nDein WordPress", 'webling');
				$body .= PHP_EOL.get_site_url();

				wp_mail($to, $subject, $body);
			}

			// send email confirmation to user
			if ($formconfig['confirmation_email_enabled']) {
				// validate email field
				$fields = WeblingApiHelper::Instance()->getVisibleMemberFields();
				if ($formconfig['confirmation_email_webling_field'] && isset($fields[$formconfig['confirmation_email_webling_field']])) {
					// fetch member data to replace placeholders in email text
					$apiCache = new \Webling\Cache\WordpressCache($client);
					$newmember = $apiCache->getObject('member', $response->getData());
					$emailfield = $fields[$formconfig['confirmation_email_webling_field']];
					$email = sanitize_email($newmember['properties'][$emailfield]);

					// only send if email address is valid
					if (is_email($email)) {
						$subject = trim($formconfig['confirmation_email_subject']);
						if(!$subject) {
							// if subject is empty, add default subject
							$subject = __("Ihre Anmeldung", 'webling');
						}
						$body = trim($formconfig['confirmation_email_text']);

						// only send if mail body is not empty
						if ($body) {
							foreach ($fields as $fieldname) {
								$val = $newmember['properties'][$fieldname];
								if (is_array($val)) {
									$val = implode(', ', $val);
								} else if(is_bool($val)) {
									$val = ($val ? __('Ja', 'webling') : __('Nein', 'webling'));
								}
								$subject = str_replace('[['.$fieldname.']]', $val, $subject);
								$body = str_replace('[['.$fieldname.']]', $val, $body);
							}
							wp_mail($email, $subject, $body);
						}
					}
				}
			}

		} catch (Exception $e) {
			if ( WP_DEBUG ) {
				trigger_error($e->getMessage());
			}
			wp_redirect(add_query_arg('webling_form_submitted', 'false', $_POST['webling-form-redirect']));
			exit;
		}

		wp_redirect(add_query_arg('webling_form_submitted', 'true', $_POST['webling-form-redirect']));
		exit;
	}
}
