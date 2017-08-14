<?php

class Form_List extends WP_List_Table
{

	public static $TABLE_NAME = 'webling_forms';

	/** Class constructor */
	public function __construct()
	{
		parent::__construct([
			'singular' => __('Anmeldeformular', 'webling'), //singular name of the listed records
			'plural' => __('Anmeldeformulare', 'webling'), //plural name of the listed records
			'ajax' => false //should this table support ajax?
		]);
	}

	/**
	 * Retrieve data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_records($per_page = 5, $page_number = 1)
	{

		global $wpdb;

		$sql = "SELECT * FROM {$wpdb->prefix}" . self::$TABLE_NAME;

		if (!empty($_REQUEST['orderby'])) {
			$sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
			$sql .= !empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
		}

		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ($page_number - 1) * $per_page;

		$result = $wpdb->get_results($sql, 'ARRAY_A');

		return $result;
	}

	/**
	 * Delete a record.
	 *
	 * @param int $id record ID
	 */
	public static function delete_record($id)
	{
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->prefix}" . self::$TABLE_NAME,
			['id' => $id],
			['%d']
		);
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count()
	{
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}" . self::$TABLE_NAME;

		return $wpdb->get_var($sql);
	}

	/** Text displayed when no data is available */
	public function no_items()
	{
		_e('No records avaliable.', 'webling');
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name($item)
	{
		// create a nonce
		$delete_nonce = wp_create_nonce('sp_delete_' . self::$TABLE_NAME);

		$title = '<strong>' . $item['name'] . '</strong>';

		$actions = [
			'delete' => sprintf('<a href="?page=%s&action=%s&record=%s&_wpnonce=%s">Delete</a>', esc_attr($_REQUEST['page']), 'delete', absint($item['id']), $delete_nonce)
		];

		return $title . $this->row_actions($actions);
	}

	/**
	 * Render a column when no column specific method exists.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default($item, $column_name)
	{
		switch ($column_name) {
			case 'title':
				return '<a href="'.admin_url('admin.php?page=webling_page_form_edit&form_id='.$item['id']).'">' . $item['title'] . '</a>';
			case 'shortcode':
				return '[webling_form id="'.$item['id'].'"]';
			default:
				return print_r($item, true); //Show the whole array for troubleshooting purposes
		}
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb($item)
	{
		return sprintf(
			'<input type="checkbox" name="webling_form_bulk_delete[]" value="%s" />', $item['id']
		);
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns()
	{
		$columns = [
			'cb' => '<input type="checkbox" />',
			'title' => __('Title', 'webling'),
			'shortcode' => __('Shortcode', 'webling'),
		];

		return $columns;
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns()
	{
		$sortable_columns = array(
			'title' => array('title', false)
		);

		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions()
	{
		$actions = [
			'webling_form_bulk_delete' => 'Delete'
		];

		return $actions;
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items()
	{

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		/** Process bulk action */
		//$this->process_bulk_action();

		$per_page = $this->get_items_per_page('records_per_page', 30);
		$current_page = $this->get_pagenum();
		$total_items = self::record_count();

		$this->set_pagination_args([
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page' => $per_page //WE have to determine how many items to show on a page
		]);

		$this->items = self::get_records($per_page, $current_page);
	}

	public function process_bulk_action()
	{

		//Detect when a bulk action is being triggered...
		if ('delete' === $this->current_action()) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr($_REQUEST['_wpnonce']);

			if (!wp_verify_nonce($nonce, 'sp_delete_'.self::$TABLE_NAME)) {
				die('Go get a life script kiddies');
			} else {
				self::delete_record(absint($_GET['record']));

				wp_redirect(esc_url(add_query_arg([])));
				exit;
			}

		}

		// If the delete bulk action is triggered
		if ((isset($_POST['action']) && $_POST['action'] == 'webling_form_bulk_delete')
			|| (isset($_POST['action2']) && $_POST['action2'] == 'webling_form_bulk_delete')
		) {

			$delete_ids = esc_sql($_POST['webling_form_bulk_delete']);

			// loop over the array of record IDs and delete them
			foreach ($delete_ids as $id) {
				self::delete_record($id);

			}

			wp_redirect(esc_url(add_query_arg([])));
			exit;
		}
	}
}
