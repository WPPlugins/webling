
/**
 * Add/remove the selected class after selecting
 * and update the hidden config field
 */
function webling_update_sorted_member_fields() {
	var fieldConfig = [];
	// find and update selected fields
	jQuery("#sortable > li").each(function (index, element) {
		if (jQuery(element).children('input').attr('checked')) {
			fieldConfig.push(jQuery(element).data('field'));
			jQuery(element).addClass('selected');
		} else {
			jQuery(element).removeClass('selected');
		}
	});
	// update hidden config field
	jQuery("#fields").val(JSON.stringify(fieldConfig));
}

/**
 * Show/hide the group selector in the memberlist editor
 */
function webling_toggle_groupselector() {
	if (jQuery("#show_all_groups").attr('checked')) {
		jQuery("#groupselector").hide();
	} else {
		jQuery("#groupselector").show();
	}
}

/**
 * Change the design view after changing the radio input
 */
function webling_memberlist_design_updated() {
	var design = jQuery('input[name="design"]:checked').val();
	if (design === 'CUSTOM') {
		jQuery("#webling_design_custom").show();
		jQuery("#webling_design_list").hide();
	} else {
		jQuery("#webling_design_custom").hide();
		jQuery("#webling_design_list").show();
	}
}

/**
 * Show/hide the confirmation email settings form editor settings
 */
function webling_toggle_confirmation_settings() {
	if (jQuery("#confirmation_email_enabled").attr('checked')) {
		jQuery("#webling_form_emails .webling_confirmation_settings").show();
	} else {
		jQuery("#webling_form_emails .webling_confirmation_settings").hide();
	}
}

/**
 * Activate a form editor tab and toggle contents
 * @param tabname
 */
function webling_change_form_tab(tabname) {
	jQuery(".webling-tabs a").removeClass('nav-tab-active');
	jQuery(".webling-tabs #webling_form_tab_" + tabname).addClass('nav-tab-active');
	jQuery(".webling-tabs-body > div").hide();
	jQuery(".webling-tabs-body #webling_form_" + tabname).show();

	if (tabname == 'fields') {
		jQuery("#webling_fields_div").show();
	} else {
		jQuery("#webling_fields_div").hide();
	}
}

/**
 * Open and close detailed form field config
 * @param id string the id of the field
 */
function webling_toggle_field(id) {
	var node = jQuery('#webling-field-' + id);
	var is_in_sidebar = jQuery(node).parent().attr('id') == 'sortable_side';
	if (node.hasClass('open') || is_in_sidebar) {
		node.removeClass('open');
		jQuery('#webling-field-' + id + ' .widget-inside').hide();
	} else {
		node.addClass('open');
		jQuery('#webling-field-' + id + ' .widget-inside').show();
	}
	return false;
}

/**
 * updates the hidden order field
 */
function webling_update_sorted_form_fields() {

	// set order of active fields
	var order = 1;
	jQuery("#sortable_main > li input.order").each(function (index, element) {
		jQuery(element).val(order);
		order++;
	});

	// set order of items in sidebar to 0 (= not shown)
	jQuery("#sortable_side > li input.order").each(function (index, element) {
		jQuery(element).val(0);
	});

	webling_update_email_field_selector();
}

/**
 * updates the field selector to only show available fields.
 * The selected value is stored in `preselected_confirmation_email_webling_field`
 * because the options are completly redrawn and the selected state is lost
 */
function webling_update_email_field_selector() {
	var select = jQuery("#confirmation_email_webling_field");
	select.html('');
	var count = 0;
	jQuery("#sortable_main > li[data-webling-type='email']").each(function (index, element) {
		var id = jQuery(element).find('input.fieldid').val();
		select.append(jQuery('<option>', {
			value: id,
			text: jQuery(element).find('input.fieldname').val(),
			selected: (preselected_confirmation_email_webling_field == id ? true : false)
		}));
		count++;
	});

	if (count === 0) {
		select.append(jQuery('<option>', {
			value: 0,
			text: ' '
		}));
		jQuery("#confirmation_email_webling_field_error").show();
	} else {
		jQuery("#confirmation_email_webling_field_error").hide();
	}
}

/**
 * moves a field from the selected list to the sidebar
 * @param id Webling field Id
 * @returns {boolean}
 */
function webling_remove_field(id) {
	var node = jQuery('#webling-field-' + id).detach();
	jQuery('#sortable_side').prepend(node);
	jQuery("#sortable_side").sortable("refresh");
	jQuery("#sortable_main").sortable("refresh");
	webling_update_sorted_form_fields();
	return false;
}
