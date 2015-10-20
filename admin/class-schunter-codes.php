<?php // (C) Copyright Bobbing Wide 2015

/**
 * Schunter codes
 *
 * Class implementing the set of shortcodes
 *
 * 
 */
class Schunter_codes {

	/**
	 * Array of Schunter code objects
	 * Keyed by $code
	 */
	public $codes;
	
	/**
	 * 
	 * Array of Schunter code ref objects 
	 * Keyed by $code
	 * 
	 */
	public $code_refs;
	
 
	/**
	 * Construct the codes
	 */
	function __construct() {
		$this->codes = array();
	}

	/**
	 * Display the codes
	 */
	function report() {
		foreach ( $this->codes as $code ) {
			$code->report();
		}
	}

	/**
	 * Add the codes referencing the $id for the given class
	 *
	 */
	function add_codes( $codes, $id, $class ) {
		if ( count( $codes ) ) {
			foreach ( $codes as $code ) {
				$code_obj = $this->add_code( $code );
				$code_obj->add_ref( $id, $class );
				
			}
		}
	}
 
	/**
	 * Add a shortcode
	 *
	 */ 
	function add_code( $code ) {
		$code_obj = bw_array_get( $this->codes, $code, null );
		if ( !$code_obj ) {
			$code_obj = new Schunter_code( $code );
			$this->codes[ $code ] = $code_obj; 
		}
		return( $code_obj );
	}
	
	/**
	 * Fetch the currently detected shortcodes
	 */
	function fetch() {
		$codes = get_option( "schunter_codes" );
		//print_r( $codes );
	}

	function update() {
		update_option( "schunter_codes", $this->codes );
	
	}

 
}
