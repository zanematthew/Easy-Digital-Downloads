<?php
/**
 * Webhooks API
 *
 * @package     EDD
 * @subpackage  Classes/Webhooks
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.9
*/


/**
 * EDD_Webhooks Class
 *
 * An API that allows webhooks to be registered in order to send remote requests anytime an action occurs
 *
 * @since 1.9
 */
class EDD_Webhooks {

	public function __construct() {
		// Create the log post type
		add_action( 'init', array( $this, 'register_post_type' ), 1 );
	}

	public function register_post_type() {

		/* Webhooks post type */
		$args = array(
			'labels'			  => array( 'name' => __( 'Webhooks', 'edd' ) ),
			'public'			  => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_ui'             => false,
			'query_var'			  => false,
			'rewrite'			  => false,
			'capability_type'	  => 'post',
			'supports'			  => array( 'title', 'editor' ),
			'can_export'		  => true
		);

		register_post_type( 'edd_webhook', $args );
	}

	public function add_hook( $args = array() ) {

	}

	public function delete_hook( $name = '' ) {

	}

	public function update_hook( $args = array() ) {

	}

	public function activate_hook( $name = '' ) {
		return $this->update_hook( array( 'name' => $name, 'status' => 'active' ) );
	}

	public function dectivate_hook( $name = '' ) {
		return $this->update_hook( array( 'name' => $name, 'status' => 'inactive' ) );
	}

	public function send_hook( $name = '', $data = array() ) {

		$hook = $this->get_hook( $name );
		if( ! $hook )
			return false;

		$uri  = $hook['uri'];
		$args = array(
			'method'      => 'POST',
			'timeout'     => 15,
			'redirection' => 5,
			'user-agent'  => 'Easy Digital Downloads/' . EDD_VERSION . '; ' . home_url(),
			'blocking'    => false,
			'body'        => $data,
    	);
		$args = apply_filters( 'edd_webhook_send_args', $args, $name, $data );

		// Send the request
		$request = wp_remote_post( $uri, $args );

		if( edd_is_test_mode() && is_wp_error( $request ) ) {
			// Log the request here for debugging purposes
		}
	}

	public function get_hooks( $args = array() ) {

		$defaults = array(
			'post_type'      => 'edd_webhook',
			'posts_per_page' => 30,
			'paged'          => $args['paged']
		);

		$args  = wp_parse_args( $args, $defaults );
		$args  = apply_filters( 'edd_get_webhooks_args', $args );
		$hooks = get_posts( $args );

		return apply_filters( 'edd_get_webhooks', $hooks );
	}


	private function fire_hooks() {

	}

}