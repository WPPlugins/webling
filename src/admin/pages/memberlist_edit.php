<?php

class webling_page_memberlist_edit {
	public static function html () {
		global $wpdb;

		if (isset($_GET['list_id'])) {
			$id = intval($_GET['list_id']);
		} else {
			$id = 0;
		}

		try {
			$titlefields = WeblingApiHelper::Instance()->getMemberTitleFields();
		} catch (Exception $e) {
			include_once(__DIR__.'/../errors/no_connection.html.php');
			return;
		}

		if (!WeblingApiHelper::Instance()->hasMemberReadAccess()) {
			include_once(__DIR__.'/../errors/no_read_access.html.php');
			WeblingApiHelper::Instance()->clearCache();
			return;
		}

		$memberfields = WeblingApiHelper::Instance()->getVisibleMemberFields();

		$html_template = "<div style=\"background: #f5f5f5; margin: 0 10px 10px 0; padding: 10px; max-width: 240px; width: 100%; float: left\">
    <div><b>[[Vorname]] [[Name]]</b></div>
    <div>[[Geburtstag]]</div>
</div>";

		if ($id) {
			$sql = "SELECT * FROM {$wpdb->prefix}webling_memberlists WHERE id = '".$id."'";
			$data = $wpdb->get_row($sql, 'ARRAY_A');
			if (!$data) {
				echo 'List with ID '.$id.' not found!';
				return;
			}
			// if template is empty, restore default
			if (!$data['custom_template']) {
				$data['custom_template'] = $html_template;
			}
		} else {
			$data = array(
				'id' => 0,
				'title' => __('Neue Liste', 'webling'),
				'show_all_groups' => 1,
				'groups' => '',
				'fields' => '',
				'sortorder' => 'ASC',
				'sortfield' => '',
				'class' => '',
				'design' => 'LIST',
				'custom_template' => $html_template
			);

			$data['fields'] = json_encode($titlefields);
		}


		echo '<div class="wrap webling-admin">';
		if ($id) {
			echo '<h2>'.__('Mitgliederliste bearbeiten', 'webling').' (ID: '.$id.')</h2>';
		} else {
			echo '<h2>'.__('Mitgliederliste erstellen', 'webling').'</h2>';
		}
		?>


			<form action="admin-post.php" method="post">
				<input type="hidden" name="action" value="save_memberlist">
				<input type="hidden" name="list_id" value="<?php echo $data['id']; ?>">

				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-2">
						<div id="post-body-content">

							<div id="titlediv">
								<div id="titlewrap">
									<input type="text" name="title" size="30" id="title" placeholder="<?php echo __('Listentitel', 'webling') ?>" spellcheck="true" autocomplete="off" value="<?php echo esc_attr($data['title']); ?>">
								</div>
							</div>

							<div class="bodywrap">

								<h3><?php echo __('Mitglieder anzeigen', 'webling') ?></h3>

								<div>
									<label>
										<input id="show_all_groups" onclick="webling_toggle_groupselector()" type="checkbox" name="show_all_groups" <?php echo ($data['show_all_groups'] == 1 ? 'checked' : '') ?>> <?php echo __('Alle Mitglieder', 'webling') ?>
									</label>
								</div>

								<div id="groupselector">
									<h4><?php echo __('Mitglieder aus folgenden Gruppen anzeigen:', 'webling') ?></h4>
									<?php echo self::groupselector(unserialize($data['groups'])) ?>
								</div>

								<h3><?php echo __('Darstellung', 'webling') ?></h3>
								<div>
									<fieldset>
										<p>
											<label onclick="webling_memberlist_design_updated()">
												<input name="design" type="radio" value="LIST" <?php echo ($data['design'] == 'LIST' ? 'checked="checked"' : '') ?>>
												Einfache Liste
											</label><br>
											<label onclick="webling_memberlist_design_updated()">
												<input name="design" type="radio" value="CUSTOM" <?php echo ($data['design'] == 'CUSTOM' ? 'checked="checked"' : '') ?>>
												Eigenes Design (HTML)
											</label>
										</p>
									</fieldset>
								</div>

								<div id="webling_design_custom" style="display: none">
									<h3><?php echo __('Eigene Designvorlage', 'webling') ?></h3>
									<textarea placeholder="<?php echo __('HTML Template', 'webling') ?>" name="custom_template" style="width: 100%; height: 300px"><?php echo esc_html($data['custom_template']); ?></textarea>
									<a name="placeholders"></a>
									<p class="description">
										<?php echo __('Dieses HTML-Template wird für jedes Mitglied auf der Seite einmal angezeigt. Du kannst in diesem Code Platzhalter im Format <code>[[Feldname]]</code> verwenden.', 'webling') ?>
										<a href="#placeholders" onclick="jQuery('#webling_design_placeholders').toggle()"><?php echo __('Mögliche Platzhalter anzeigen', 'webling') ?></a>
									</p>

									<ul class="webling_design_placeholders" id="webling_design_placeholders" style="display: none">
										<?php
										foreach ($memberfields as $field) {
											echo '<li>[[' . esc_html($field) . ']]</li>';
										}
										?>
									</ul>
								</div>

								<div id="webling_design_list">
									<h3><?php echo __('Angezeigte Felder und Reihenfolge festlegen', 'webling') ?></h3>
									<input type="hidden" id="fields" name="fields" value="<?php echo $data['fields']; ?>">
									<ul id="sortable">
									<?php
									$selectedFields = json_decode($data['fields']);
									if (!$selectedFields) {
										$selectedFields = [];
									}
									foreach ($selectedFields as $field) {
										if (in_array($field, $memberfields)) {
											echo '<li class="selected sortable_field" data-field="'.esc_attr($field).'">'
												. '<span class="dashicons dashicons-move"></span> '
												. '<input type="checkbox" checked="checked"/> '
												. esc_html($field)
												. '</li>';
										}
									}

									foreach ($memberfields as $field) {
										if (!in_array($field, $selectedFields)) {
											echo '<li class="sortable_field" data-field="' . esc_attr($field) . '">'
												. '<span class="dashicons dashicons-move"></span> '
												. '<input type="checkbox" /> '
												. esc_html($field)
												. '</li>';
										}
									}
									?>
									</ul>
								</div>

								<?php
								if ($id) {
									submit_button(__('Liste speichern', 'webling'), 'primary', 'submit');
								} else {
									submit_button(__('Liste erstellen', 'webling'), 'primary', 'submit');
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
													<p>
														<b><?php echo __('Shortcode', 'webling') ?></b>
													</p>
													<p><?php echo __('Füge diesen Shortcode in eine Seite oder einen Artikel ein um diese Liste anzuzeigen:', 'webling') ?></p>
													<p>
														<input title="Shortcode" class="shortcode" type="text" value='[webling_memberlist id="<?php echo $id; ?>"]'>
													</p>
													<?php } else { ?>
														<p><?php echo __('Nach dem Erstellen wird ein Shortcode erstellt, mit welchem du die Liste auf einer Seite oder in einem Artikel anzeigen kannst.', 'webling') ?></p>
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
								<div id="optionsdiv" class="postbox">
									<h2 class="hndle"><span><?php echo __('Optionen', 'webling') ?></span></h2>
									<div class="inside">
										<p class="post-attributes-label-wrapper"><label class="post-attributes-label" for="sortfield">
											<?php echo __('Sortieren nach', 'webling') ?>
										</label></p>
										<?php echo self::fieldselector($data['sortfield'], 'sortfield'); ?>
										<select title="sort order" name="sortorder">
											<option value="ASC" <?php echo ($data['sortorder'] == 'ASC' ? 'selected' : ''); ?>><?php echo __('aufsteigend [A-Z]', 'webling') ?></option>
											<option value="DESC" <?php echo ($data['sortorder'] == 'DESC' ? 'selected' : ''); ?>><?php echo __('absteigend [Z-A]', 'webling') ?></option>
										</select>

										<p class="post-attributes-label-wrapper"><label class="post-attributes-label" for="sortfield">
											<?php echo __('Eigene CSS Klasse', 'webling') ?>
										</label></p>
										<input type="text" placeholder="<?php echo __('CSS Klasse', 'webling') ?>" name="class" value="<?php echo esc_attr($data['class']); ?>">
										<p><?php echo __('Für eigene Designanpassungen', 'webling') ?></p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>

		</div>

		<script>
		jQuery(function() {
			jQuery( "#sortable" ).sortable({
				stop: function (event, ui) {
					webling_update_sorted_member_fields();
				}
			});
			jQuery( "#sortable" ).disableSelection();

			jQuery( "#sortable input" ).change(function() {
				webling_update_sorted_member_fields();
			});

			webling_update_sorted_member_fields();
			webling_toggle_groupselector();
			webling_memberlist_design_updated();
		});
		</script>

		<?php
	}

	protected static function fieldselector($selected, $inputname) {
		$fields = WeblingApiHelper::Instance()->getVisibleMemberFields();
		$html = '<select name="'.$inputname.'" id="'.$inputname.'">';
		foreach ($fields as $field) {
			$isSelected = ($field == $selected ? 'selected' : '');
			$html .= '<option value="'.esc_attr($field).'" '.$isSelected.'>'.esc_html($field).'</option>';
		}
		$html .= "</select>";
		return $html;
	}

	protected static function groupselector($selected = []) {
		if (!$selected) {
			$selected = [];
		}
		$tree = WeblingApiHelper::Instance()->getMembergroupTree();
		$html = '';
		foreach ($tree as $nodeId => $node) {
			$html .= self::groupselectorRecursive($nodeId, $node, $selected);
		}
		return $html;
	}

	protected static function groupselectorRecursive($nodeId, $tree, $selected, $indent = 0) {
		$checked = (in_array($nodeId, $selected) ? 'checked' : '');
		$html = '<div style="padding-left: '.($indent * 20).'px">
			<label><input type="checkbox" name="groups['.$nodeId.']" '.$checked.'> '.esc_html($tree['title']) .'</label>
			</div>';
		foreach ($tree['childs'] as $nodeId => $node) {
			$html .= self::groupselectorRecursive($nodeId, $node, $selected, $indent+1);
		}
		return $html;
	}
}


