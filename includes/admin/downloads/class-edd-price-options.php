<?php

class EDD_Price_Options {
	
	private $post_id = 0;
	private $options = array();

	public function __construct() {

		$this->options[] = array(
			'id'          => 'name',
			'type'        => 'text',
			'label'       => __( 'Name', 'edd' ),
			'placeholder' => __( 'Option Name', 'edd' ),
			'class'       => 'edd-price-field large-text'
		);

		$this->options[] = array(
			'id'          => 'amount',
			'type'        => 'text',
			'label'       => __( 'Amount', 'edd' ),
			'placeholder' => '0.00',
			'class'       => 'edd-price-field'
		);

		$this->options[] = array(
			'id'          => '_edd_default_price_id',
			'name'        => '_edd_default_price_id',
			'type'        => 'radio',
			'label'       => __( 'Default', 'edd' ),
			'value'       => edd_get_default_variable_price( $this->post_id )
		);

	}

	public function get_options( $_post_id = 0 ) {

		$defaults = array(
			'id'          => 'option',
			'type'        => 'text',
			'placeholder' => '',
			'size'        => '',
			'class'       => ''
		);

		$options = (array) apply_filters( 'edd_get_price_options', $this->options, $this->post_id );

		foreach( $options as $key => $option ) {

			$this->options[ $key ] = wp_parse_args( $option, $defaults );

			if( empty( $option['type'] ) || ! $this->is_valid_type( $option['type'] ) ) {
				$this->options[ $key ]['type'] = 'text';
			}

		}

		return $this->options;
	}

	private function is_valid_type( $type = '' ) {

		$valid = apply_filters( 'edd_variable_price_option_types', array(
			'text',
			'checkbox',
			'radio',
			'textarea',
			'select',
			'hidden'
		) );

		return in_array( $type, $valid );

	}

}