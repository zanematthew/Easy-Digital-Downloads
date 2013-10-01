<?php
/**
 * Webhooks Table Class
 *
 * @package     EDD
 * @subpackage  Admin/Tools
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.9
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * EDD_Webhooks_Table Class
 *
 * Renders the webhook Codes table on the webhook Tools page
 *
 * @since 1.9
 */
class EDD_Webhooks_Table extends WP_List_Table {

	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 1.9
	 */
	public $per_page = 30;

	/**
	 *
	 * Total number of webhooks
	 * @var string
	 * @since 1.9
	 */
	public $total_count;

	/**
	 * Active number of webhooks
	 *
	 * @var string
	 * @since 1.9
	 */
	public $active_count;

	/**
	 * Inactive number of webhooks
	 *
	 * @var string
	 * @since 1.9
	 */
	public $inactive_count;

	/**
	 * Get things started
	 *
	 * @since 1.9
	 * @uses EDD_Webhooks_Table::get_webhook_counts()
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;

		parent::__construct( array(
			'singular'  => edd_get_label_singular(),    // Singular name of the listed records
			'plural'    => edd_get_label_plural(),    	// Plural name of the listed records
			'ajax'      => false             			// Does this table support ajax?
		) );

		$this->get_webhook_counts();
	}

	/**
	 * Retrieve the view types
	 *
	 * @access public
	 * @since 1.9
	 * @return array $views All the views available
	 */
	public function get_views() {
		$base           = admin_url('edit.php?post_type=download&page=edd-tools&tab=webhooks');

		$current        = isset( $_GET['status'] ) ? $_GET['status'] : '';
		$total_count    = '&nbsp;<span class="count">(' . $this->total_count    . ')</span>';
		$active_count   = '&nbsp;<span class="count">(' . $this->active_count . ')</span>';
		$inactive_count = '&nbsp;<span class="count">(' . $this->inactive_count  . ')</span>';

		$views = array(
			'all'		=> sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( 'status', $base ), $current === 'all' || $current == '' ? ' class="current"' : '', __('All', 'edd') . $total_count ),
			'active'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'active', $base ), $current === 'active' ? ' class="current"' : '', __('Active', 'edd') . $active_count ),
			'inactive'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'inactive', $base ), $current === 'inactive' ? ' class="current"' : '', __('Inactive', 'edd') . $inactive_count ),
		);

		return $views;
	}

	/**
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since 1.9
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'cb'        => '<input type="checkbox" />',
			'name'  	=> __( 'Name', 'edd' ),
			'url'     	=> __( 'URL', 'edd' ),
			'status'  	=> __( 'Status', 'edd' ),
		);

		return $columns;
	}

	/**
	 * Retrieve the table's sortable columns
	 *
	 * @access public
	 * @since 1.9
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'name'   => array( 'name', false )
		);
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since 1.9
	 *
	 * @param array $item Contains all the data of the webhook code
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	function column_default( $item, $column_name ) {
		switch( $column_name ) {

			case 'url' :
				$value = $item->guid;
				break;
			case 'status' :
				$value = get_post_status( $item->ID );
			default:
				$value = $item->$column_name;
				break;
		}
		return $value;
	}

	/**
	 * Render the Name Column
	 *
	 * @access public
	 * @since 1.9
	 * @param array $item Contains all the data of the webhook code
	 * @return string Data shown in the Name column
	 */
	function column_name( $item ) {
		$webhook     = get_post( $item->ID );
		$base         = admin_url( 'edit.php?post_type=download&page=edd-tools&view=edit_webhook&webhook=' . $item->ID );
		$row_actions  = array();

		$row_actions['edit'] = '<a href="' . add_query_arg( array( 'view' => 'edit_webhook', 'webhook' => $webhook->ID ) ) . '">' . __( 'Edit', 'edd' ) . '</a>';

		if( strtolower( $item->status ) == 'active' )
			$row_actions['deactivate'] = '<a href="' . add_query_arg( array( 'view' => 'deactivate_webhook', 'webhook' => $webhook->ID ) ) . '">' . __( 'Deactivate', 'edd' ) . '</a>';
		else
			$row_actions['activate'] = '<a href="' . add_query_arg( array( 'view' => 'activate_webhook', 'webhook' => $webhook->ID ) ) . '">' . __( 'Activate', 'edd' ) . '</a>';

		$row_actions['delete'] = '<a href="' . wp_nonce_url( add_query_arg( array( 'view' => 'delete_webhook', 'webhook' => $webhook->ID ) ), 'edd_webhook_nonce' ) . '">' . __( 'Delete', 'edd' ) . '</a>';

		$row_actions = apply_filters( 'edd_webhook_row_actions', $row_actions, $webhook );

		return stripslashes( $item->post_title ) . $this->row_actions( $row_actions );
	}

	/**
	 * Render the checkbox column
	 *
	 * @access public
	 * @since 1.9
	 * @param array $item Contains all the data for the checkbox column
	 * @return string Displays a checkbox
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ 'webhook',
			/*$2%s*/ $item->ID
		);
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 1.7.2
	 * @access public
	 */
	function no_items() {
		_e( 'No webhooks found.', 'edd' );
	}

	/**
	 * Retrieve the bulk actions
	 *
	 * @access public
	 * @since 1.9
	 * @return array $actions Array of the bulk actions
	 */
	public function get_bulk_actions() {
		$actions = array(
			'activate'   => __( 'Activate', 'edd' ),
			'deactivate' => __( 'Deactivate', 'edd' ),
			'delete'     => __( 'Delete', 'edd' )
		);

		return $actions;
	}

	/**
	 * Process the bulk actions
	 *
	 * @access public
	 * @since 1.9
	 * @return void
	 */
	public function process_bulk_action() {
		$ids = isset( $_GET['webhook'] ) ? $_GET['webhook'] : false;

		if ( ! is_array( $ids ) )
			$ids = array( $ids );

		foreach ( $ids as $id ) {
			if ( 'delete' === $this->current_action() ) {
			}
			if ( 'activate' === $this->current_action() ) {
			}
			if ( 'deactivate' === $this->current_action() ) {
			}
		}

	}

	/**
	 * Retrieve the webhook code counts
	 *
	 * @access public
	 * @since 1.9
	 * @return void
	 */
	public function get_webhook_counts() {
		$webhook_count   = wp_count_posts( 'edd_webhook' );
		$this->active_count   = $webhook_count->active;
		$this->inactive_count = $webhook_count->inactive;
		$this->total_count    = $webhook_count->active + $webhook_count->inactive;
	}

	/**
	 * Retrieve all the data for all the webhook codes
	 *
	 * @access public
	 * @since 1.9
	 * @return array $webhook_data Array of all the data for the webhook codes
	 */
	public function webhook_data() {

		$per_page = $this->per_page;

		$orderby 		= isset( $_GET['orderby'] )  ? $_GET['orderby']                  : 'ID';
		$order 			= isset( $_GET['order'] )    ? $_GET['order']                    : 'DESC';
		$order_inverse 	= $order == 'DESC'           ? 'ASC'                             : 'DESC';
		$status 		= isset( $_GET['status'] )   ? $_GET['status']                   : array( 'active', 'inactive' );
		$order_class 	= strtolower( $order_inverse );

		return EDD()->webhooks->get_hooks( array(
			'posts_per_page' => $per_page,
			'paged'          => isset( $_GET['paged'] ) ? $_GET['paged'] : 1,
			'orderby'        => $orderby,
			'order'          => $order,
			'post_status'    => $status
		) );
	}

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since 1.9
	 * @uses EDD_Webhooks_Table::get_columns()
	 * @uses EDD_Webhooks_Table::get_sortable_columns()
	 * @uses EDD_Webhooks_Table::process_bulk_action()
	 * @uses EDD_Webhooks_Table::webhook_data()
	 * @uses WP_List_Table::get_pagenum()
	 * @uses WP_List_Table::set_pagination_args()
	 * @return void
	 */
	public function prepare_items() {
		$per_page = $this->per_page;

		$columns = $this->get_columns();

		$hidden = array();

		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();

		$data = $this->webhook_data();

		$current_page = $this->get_pagenum();

		$status = isset( $_GET['status'] ) ? $_GET['status'] : 'any';

		switch( $status ) {
			case 'active':
				$total_items = $this->active_count;
				break;
			case 'inactive':
				$total_items = $this->inactive_count;
				break;
			case 'any':
				$total_items = $this->total_count;
				break;
		}

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page )
			)
		);
	}
}