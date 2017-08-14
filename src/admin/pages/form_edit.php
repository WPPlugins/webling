<?php

class webling_page_form_edit {

	public static function html() {
		global $wpdb;

		if (isset($_GET['form_id'])) {
			$id = intval($_GET['form_id']);
		} else {
			$id = 0;
		}

		$formfields = [];
		$active_formfields = [];

		try {
			$member_properties = WeblingApiHelper::Instance()->getMemberProperties();
			$member_fields = WeblingApiHelper::Instance()->getMutableMemberFields();
		} catch (Exception $e) {
			include_once(__DIR__.'/../errors/no_connection.html.php');
			return;
		}

		if (!WeblingApiHelper::Instance()->hasMemberWriteAccess()) {
			include_once(__DIR__.'/../errors/no_write_access.html.php');
			WeblingApiHelper::Instance()->clearCache();
			return;
		}

		if ($id) {
			$sql = "SELECT * FROM {$wpdb->prefix}webling_forms WHERE id = '".$id."'";
			$data = $wpdb->get_row($sql, 'ARRAY_A');
			if (!$data) {
				echo 'Form with ID '.$id.' not found!';
				return;
			}
			$sql = "SELECT * FROM {$wpdb->prefix}webling_form_fields WHERE form_id = '".$id."' ORDER BY `order` ASC";
			$formfields = $wpdb->get_results($sql, 'ARRAY_A');
			foreach ($formfields as $formfield) {
				$active_formfields[] = $formfield['webling_field_id'];
			}
		} else {
			$data = array(
				'id' => 0,
				'title' => __('Neues Anmeldeformular', 'webling'),
				'group_id' => 0,
				'notification_email' => '',
				'confirmation_text' => __('<h2>Vielen Dank</h2>Wir haben Ihre Anmeldung erhalten.', 'webling'),
				'submit_button_text' => __('Absenden', 'webling'),
				'confirmation_email_enabled' => 0,
				'confirmation_email_webling_field' => 0,
				'confirmation_email_subject' => __("Ihre Anmeldung", 'webling'),
				'confirmation_email_text' => __("Vielen Dank!\n\nWir haben Ihre Anmeldung erhalten.\n\nFreundliche Grüsse", 'webling'),
				'class' => '',
			);
		}

		echo '<div class="wrap webling-admin">';
		if ($id) {
			echo '<h2>'.__('Formular bearbeiten', 'webling').' (ID: '.$id.')</h2>';
		} else {
			echo '<h2>'.__('Formular erstellen', 'webling').'</h2>';
		}
		?>
			<form action="admin-post.php" method="post">
				<input type="hidden" name="action" value="save_form">
				<input type="hidden" name="form_id" value="<?php echo $data['id']; ?>">

				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-2">
						<div id="post-body-content">

							<div id="titlediv">
								<div id="titlewrap">
									<input type="text" name="title" size="30" id="title" placeholder="<?php echo __('Formularname', 'webling') ?>" spellcheck="true" autocomplete="off" value="<?php echo esc_attr($data['title']); ?>">
								</div>
							</div>

							<br>
							<div class="bodywrap">

								<h2 class="nav-tab-wrapper wp-clearfix webling-tabs">
									<a id="webling_form_tab_fields" href="#" onclick="webling_change_form_tab('fields')" class="nav-tab nav-tab-active"><?php echo __('Formularfelder', 'webling') ?></a>
									<a id="webling_form_tab_settings" href="#" onclick="webling_change_form_tab('settings')" class="nav-tab"><?php echo __('Einstellungen', 'webling') ?></a>
									<a id="webling_form_tab_emails" href="#" onclick="webling_change_form_tab('emails')" class="nav-tab"><?php echo __('E-Mails', 'webling') ?></a>
								</h2>

								<div class="webling-tabs-body">

									<div id="webling_form_fields">
										<br>
										<div class="widgets-holder-wrap">
											<p class="webling-fields-hint"><?php echo __('Ziehe Felder aus der Seitenleiste in diesen Bereich um sie zum Formular hinzuzufügen.', 'webling') ?></p>
											<ul id="sortable_main" class="connectedSortable webling-fields-holder">
												<?php
												foreach ($formfields as $formfield) {
													if (isset($member_fields[$formfield['webling_field_id']])) {
														$fieldname = $member_fields[$formfield['webling_field_id']];
														self::field_markup($fieldname, $member_properties[$fieldname], $formfield);
													}
												}
												?>
											</ul>
										</div>
									</div>

									<div id="webling_form_settings" style="display: none">
										<table class="form-table">
											<tr>
												<th scope="row"><?php echo __('Mitglied in folgender Webling Gruppe erfassen', 'webling') ?></th>
												<td>
													<?php echo self::group_select($data['group_id']); ?>
													<p class="description"><?php echo __('Nach dem Absenden des Formulars wird in dieser Gruppe in Webling ein Mitglied mit den angegebenen Daten erstellt.', 'webling') ?></p>
												</td>
											</tr>
											<tr>
												<th scope="row"><?php echo __('Text "Absenden"-Button', 'webling') ?></th>
												<td>
													<input type="text" placeholder="<?php echo __('Absenden', 'webling') ?>" name="submit_button_text" value="<?php echo esc_attr($data['submit_button_text']); ?>" class="regular-text">
													<p class="description"><?php echo __('Text der auf dem Formular Absenden Button steht', 'webling') ?></p>
												</td>
											</tr>
											<tr>
												<th scope="row"><?php echo __('CSS Klasse', 'webling') ?></th>
												<td>
													<input type="text" placeholder="<?php echo __('CSS Klasse', 'webling') ?>" name="class" value="<?php echo esc_attr($data['class']); ?>" class="regular-text">
													<p class="description"><?php echo __('CSS Klasse für dieses Formular, kann für eigene Designanpassungen verwendet werden.', 'webling') ?></p>
												</td>
											</tr>
											<tr>
												<th scope="row"><?php echo __('Bestätigungstext nach Absenden', 'webling') ?></th>
												<td>
													<?php wp_editor($data['confirmation_text'], 'confirmation_text'); ?>
													<p class="description"><?php echo __('Dieser Text wird dem Besucher angezeigt, nachdem das Formular abgesendet wurde.', 'webling') ?></p>
												</td>
											</tr>
										</table>
									</div>

									<div id="webling_form_emails" style="display: none">
										<h2 class="title"><?php echo __('E-Mail Benachrichtigung an Admin', 'webling') ?></h2>
										<p><?php echo __('Bei jeder Anmeldung über das Formular können die übermittelten Daten an eine E-Mail Adresse zur Info gesendet werden.', 'webling') ?></p>
										<table class="form-table">
											<tr>
												<th scope="row"><?php echo __('Benachrichtigung senden an', 'webling') ?></th>
												<td>
													<input type="text" placeholder="<?php echo __('E-Mail Adresse', 'webling') ?>" name="notification_email" value="<?php echo esc_attr($data['notification_email']); ?>" class="regular-text">
													<p class="description"><?php echo __('Eine E-Mail mit den übermittelten Formulardaten wird an diese Adresse versandt.', 'webling') ?></p>
												</td>
											</tr>
										</table>
										<h2 class="title"><?php echo __('E-Mail Bestätigung an Besucher', 'webling') ?></h2>
										<p></p>
										<table class="form-table">
											<tr>
												<th scope="row"><?php echo __('E-Mail Bestätgung an Besucher senden', 'webling') ?></th>
												<td>
													<fieldset>
														<label for="confirmation_email_enabled">
															<input name="confirmation_email_enabled" onclick="webling_toggle_confirmation_settings()" type="checkbox" id="confirmation_email_enabled" <?php echo ($data['confirmation_email_enabled'] ? 'checked' : ''); ?>>
															<?php echo __('Eine Bestätigung an den Besucher senden', 'webling') ?>
														</label>
													</fieldset>
													<p class="description"><?php echo __('Eine Bestätigung per E-Mail an den Besucher senden wenn er das Formular abgeschickt hat.', 'webling') ?></p>
												</td>
											</tr>
											<tr class="webling_confirmation_settings">
												<th scope="row"><?php echo __('E-Mail Formular Feld', 'webling') ?></th>
												<td>
													<script type="text/javascript">
														var preselected_confirmation_email_webling_field = <?php echo intval($data['confirmation_email_webling_field']); ?>;
													</script>
													<select id="confirmation_email_webling_field" name="confirmation_email_webling_field" class="regular-text"></select>
													<p class="description" id="confirmation_email_webling_field_error" style="color: red;"><?php echo __('Kein E-Mail Feld im Formular gefunden. Füge ein E-Mail Feld zum Formular hinzu um diese Funktion zu nutzen.', 'webling') ?></p>
													<p class="description"><?php echo __('Wähle das E-Mail Feld in deinem Formular. Der Besucher erhält eine Bestätigung an die Adresse die in das hier ausgewählte Feld eingegeben wurde. Das E-Mail wird nur versandt, wenn die Adresse ausgefüllt wurde und gültig ist.', 'webling') ?></p>
												</td>
											</tr>
											<tr class="webling_confirmation_settings">
												<th scope="row"><?php echo __('E-Mail Betreff', 'webling') ?></th>
												<td>
													<input type="text" placeholder="<?php echo __('E-Mail Betreff', 'webling') ?>" name="confirmation_email_subject" value="<?php echo esc_attr($data['confirmation_email_subject']); ?>" class="regular-text">
													<p class="description"><?php echo __('Der Betreff der E-Mail an den Besucher.', 'webling') ?></p>
												</td>
											</tr>
											<tr class="webling_confirmation_settings">
												<th scope="row"><?php echo __('E-Mail Text', 'webling') ?></th>
												<td>
													<textarea placeholder="<?php echo __('E-Mail Bestätigungstext', 'webling') ?>" name="confirmation_email_text" style="width: 100%; height: 300px"><?php echo esc_html($data['confirmation_email_text']); ?></textarea>
													<p class="description"><?php echo __('Der E-Mail-Text der an den Besucher gesendet wird. Du kannst in diesem Text Platzhalter im Format <code>[[Feldname]]</code> verwenden. Achtung: Verwende den Feldnamen in Webling und nicht den Feldnamen aus dem Formular!', 'webling') ?></p>
												</td>
											</tr>
										</table>
									</div>

								</div>

								<?php
								if ($id) {
									submit_button(__('Formular speichern', 'webling'), 'primary', 'submit');
								} else {
									submit_button(__('Formular erstellen', 'webling'), 'primary', 'submit');
								}
								?>
							</div>
						</div>
						<div id="postbox-container-1" class="postbox-container">
							<div id="side-sortables" class="meta-box-sortables">
								<div id="submitdiv" class="postbox">
									<h2 class="hndle"><span><?php echo __('Veröffentlichen', 'webling') ?></span></h2>
									<div class="inside">
										<div class="submitbox" id="submitpost">
											<div id="misc-publishing-actions">
												<div class="misc-pub-section">
													<?php if ($id) { ?>
													<p><?php echo __('Füge diesen Shortcode in eine Seite oder einen Artikel ein um dieses Formular anzuzeigen:', 'webling') ?></p>
													<p>
														<input title="Shortcode" class="shortcode" type="text" value='[webling_form id="<?php echo $id; ?>"]'>
													</p>
													<?php } else { ?>
														<p><?php echo __('Nach dem Erstellen wird ein Shortcode erstellt, mit welchem du das Formular auf einer Seite oder in einem Artikel anzeigen kannst.', 'webling') ?></p>
													<?php } ?>
												</div>
											</div>
											<div id="major-publishing-actions">
												<!--div id="delete-action">
													<a class="submitdelete deletion" href="http://localhost/webling/wordpress-4.7.0/wp-admin/post.php?post=1&amp;action=trash&amp;_wpnonce=970d6cb90f">Move to Trash</a>
												</div-->
												<div id="publishing-action">
													<input name="save" type="submit" class="button button-primary button-large" id="publish" value="<?php echo __('Speichern', 'webling') ?>">
												</div>
												<div class="clear"></div>
											</div>
										</div>
									</div>
								</div>
								<div id="webling_fields_div" class="postbox">
									<h2 class="hndle"><span><?php echo __('Felder', 'webling') ?></span></h2>
									<div class="inside">
										<p><?php echo __('Ziehe diese Felder in den Hauptbereich um sie zum Formular hinzuzufügen:', 'webling') ?></p>
										<ul id="sortable_side" class="connectedSortable">
										<?php
											foreach ($member_properties as $name => $config) {
												if (!in_array($config['id'], $active_formfields)) {
													if (!in_array($member_properties[$name]['datatype'], WeblingApiHelper::$immutableFields)) {
														self::field_markup($name, $config);
													}
												}
											}
											?>
										</ul>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>

		</div>

		<script type="text/javascript">
			jQuery(function() {
				jQuery("#sortable_main, #sortable_side").sortable({
					connectWith: ".connectedSortable",
					handle: ".widget-top",
					start: function (event, ui) {
						if (jQuery(event.currentTarget).attr('id') == 'sortable_side') {
							jQuery("#sortable_main").addClass('dragging');
						}
					},
					stop: function (event, ui) {
						jQuery("#sortable_main").removeClass('dragging');
						webling_update_sorted_form_fields();
					}
				}).disableSelection();

				jQuery("#confirmation_email_webling_field").change(function() {
					preselected_confirmation_email_webling_field = parseInt(jQuery("#confirmation_email_webling_field").val());
					console.log(preselected_confirmation_email_webling_field);
				});

				webling_update_sorted_form_fields();
				webling_toggle_confirmation_settings();
			});
		</script>

		<?php
	}

	protected static function group_select($selected = 0) {
		$tree = WeblingApiHelper::Instance()->getMembergroupTree();

		// select first root group if no selection given
		if (!$selected) {
			$keys = array_keys($tree);
			$selected = $keys[0];
		}

		$html = '';
		foreach ($tree as $nodeId => $node) {
			$html .= self::group_select_recursive($nodeId, $node, $selected);
		}
		return '<select name="group_id">'.$html.'</select>';
	}

	protected static function group_select_recursive($nodeId, $tree, $selected, $indent = 0) {
		$is_selected = ($nodeId == $selected && $tree['writeable'] ? ' selected' : '');
		$is_writeable = ($tree['writeable'] ? '' : ' disabled');
		$html = '<option value="'.$nodeId.'" '.$is_selected.$is_writeable.'>'.str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $indent).$tree['title'] .'</option>';
		foreach ($tree['childs'] as $nodeId => $node) {
			$html .= self::group_select_recursive($nodeId, $node, $selected, $indent+1);
		}
		return $html;
	}

	protected static function translated_datatype($config) {
		switch($config['datatype']) {
			case 'text':
				if (isset($config['type']) && $config['type'] == 'email') {
					return __('Text, E-Mail', 'webling');
				} else {
					return __('Text', 'webling');
				}
				break;
			case 'longtext':
				return __('Mehrzeiliger Text', 'webling');
				break;
			case 'int':
				return __('Zahl', 'webling');
				break;
			case 'numeric':
				return __('Betrag', 'webling');
				break;
			case 'bool':
				return __('Checkbox', 'webling');
				break;
			case 'enum':
				return __('Auswahlfeld', 'webling');
				break;
			case 'multienum':
				return __('Mehrfachauswahlfeld', 'webling');
				break;
			case 'date':
				return __('Datum', 'webling');
				break;
			case 'image':
			case 'file':
			case 'autoincrement':
			default:
				return ucfirst($config['datatype']);
		}
	}

	protected static function field_markup($name, $config, $data = null) {
		if (!is_array($data)) {
			$data = [
				'required' => 0,
				'field_name' => $name,
				'field_name_position' => 'TOP',
				'placeholder_text' => '',
				'description_text' => '',
				'class' => ''
			];
		}
		$fieldid = $config['id'];
		$type = $config['datatype'];
		?>
			<li class="widget" id="webling-field-<?php echo $fieldid; ?>" <?php echo (isset($config['type']) ? ' data-webling-type="'.$config['type'].'"' : ''); ?>>
				<div class="widget-top" onclick="webling_toggle_field('<?php echo $fieldid; ?>')">
					<div class="widget-title-action">
						<a class="widget-action" href="#"></a>
					</div>
					<div class="widget-title ui-sortable-handle">
						<h3><?php echo esc_html($name); ?></h3>
					</div>
				</div>
				<div class="widget-inside">
					<div class="widget-content">
						<input type="hidden" name="fields[<?php echo $fieldid; ?>][webling_field_id]" value="<?php echo $config['id']; ?>" class="fieldid">
						<input type="hidden" name="fields[<?php echo $fieldid; ?>][order]" class="order" value="0">
						<table class="form-table">
							<tr>
								<th scope="row"><?php echo __('Feld in Webling', 'webling') ?></th>
								<td><?php echo esc_html($name); ?> <span class="description">(<?php echo self::translated_datatype($config) ?>)</span></td>
							</tr>
							<tr <?php if($type == 'multienum') echo 'style="display: none;"'; ?>>
								<th scope="row"><?php echo __('Pflichtfeld', 'webling') ?></th>
								<td>
									<label>
										<input name="fields[<?php echo $fieldid; ?>][required]" type="checkbox" value="1" <?php echo ($data['required'] ? 'checked' : ''); ?>>
										<?php echo __('Feld muss ausgefüllt werden', 'webling') ?>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php echo __('Feldname', 'webling') ?></th>
								<td>
									<input type="text" name="fields[<?php echo $fieldid; ?>][field_name]" value="<?php echo esc_attr($data['field_name']); ?>" placeholder="<?php echo __('Name des Formularfeldes', 'webling') ?>" class="regular-text fieldname">
								</td>
							</tr>
							<tr>
								<th scope="row"><?php echo __('Position des Feldnamens', 'webling') ?></th>
								<td>
									<select name="fields[<?php echo $fieldid; ?>][field_name_position]">
										<option value="TOP" <?php echo ($data['field_name_position'] == 'TOP' ? 'selected' : ''); ?>><?php echo __('Oberhalb', 'webling') ?></option>
										<option value="LEFT" <?php echo ($data['field_name_position'] == 'LEFT' ? 'selected' : ''); ?>><?php echo __('Links', 'webling') ?></option>
										<option value="HIDDEN" <?php echo ($data['field_name_position'] == 'HIDDEN' ? 'selected' : ''); ?>><?php echo __('Feldnamen nicht anzeigen', 'webling') ?></option>
									</select>
								</td>
							</tr>
							<tr <?php if(in_array($type, array('date','multienum'))) echo 'style="display: none;"'; ?>>
								<th scope="row">
									<?php
										if($type == 'bool') echo __('Beschriftung', 'webling');
										else echo __('Platzhalter', 'webling');
									?>
								</th>
								<td>
									<input type="text" name="fields[<?php echo $fieldid; ?>][placeholder_text]" value="<?php echo esc_attr($data['placeholder_text']); ?>" placeholder="<?php
										if($type == 'bool') echo __('Beschriftung der Checkbox', 'webling');
										else echo __('Platzhalter text', 'webling');
									?>" class="regular-text">
								</td>
							</tr>
							<tr>
								<th scope="row"><?php echo __('Beschreibungstext', 'webling') ?></th>
								<td>
									<input type="text" name="fields[<?php echo $fieldid; ?>][description_text]" value="<?php echo esc_attr($data['description_text']); ?>" placeholder="<?php echo __('Kurzer Hilfe- oder Beschreibungstext', 'webling') ?>" class="regular-text">
								</td>
							</tr>
							<tr>
								<th scope="row"><?php echo __('CSS Klasse', 'webling') ?></th>
								<td>
									<input type="text" name="fields[<?php echo $fieldid; ?>][class]" value="<?php echo esc_attr($data['class']); ?>" placeholder="<?php echo __('CSS Klasse', 'webling') ?>" class="regular-text">
								</td>
							</tr>
						</table>
						<div class="widget-control-actions">
							<div class="alignleft">
								<a class="widget-control-remove" href="#remove" onclick="return webling_remove_field('<?php echo $fieldid; ?>')"><?php echo __('Feld entfernen', 'webling') ?></a> |
								<a class="widget-control-close" href="#close" onclick="return webling_toggle_field('<?php echo $fieldid; ?>')"><?php echo __('Schliessen', 'webling') ?></a>
							</div>
							<br class="clear">
						</div>

					</div>
				</div>
			</li>
		<?php
	}
}
