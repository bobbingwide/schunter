<?php // (C) Copyright Bobbing Wide 2015

/**
 * Schunter code
 *
 * Implements a single instance of a shortcode and all its references
 *
 * Notes: 
 * - Each shortcode shortcode may or may not be registered. 
 * - It may not even be a shortcode.
 * - But if it were to become a shortcode, and then be expanded as a shortcode, then you might get unexpected different results to how it was before the shortcode was registered.
 * - The total_references figure is a snapshot when the information was built
 * - Ditto for the _refs prefixed variables.
 */
class Schunter_code {

	/**
	 * Code 
	 * 
	 * The shortcode name
   */
	public $code = null;

	/*
	 * Function
	 *
	 * The registered shortcode function
	 * From this we might be able to determine the implementing plugin / theme file
	 * using Reflection function logic 
	 */
	public $function = null;
	
	/**
	 * Status
	 *
	 * Shortcode status can be one of the following
	 *
	 * Status | Meaning
	 * ------ | ----------
	 * Active | Registered by add_shortcode() with an implementing function
	 * Inactive | Registered by add_shortcode() to produce no output - __return_null() 
	 * 
	 * Unknown | Not known as a valid shortcode
	 * Known | Known to be a valid shortcode
	 * 
	 * Invalid | Appears to be a shortcode but has an invalid name
	 * False | Wasn't a shortcode after all
	 * 
	 */
	public $status = null;
	
	/**
	 * Total number of references to this shortcode
	 *
	 */
	public $total_references = 0;
	
	/** 
	 * See class Schunter_code_refs for some brief documentation about these fields in a table
	 */
	public $comments_refs;
	public $commentmeta_refs;
	public $links_refs;
	public $options_refs;
	public $postmeta_refs;
	public $posts_refs;
	public $sitemeta_refs; 	 // wp_sitemeta      | Not stored in the meta_key but can be in any meta_value. May be in serialized data
	public $term_meta_refs;
	public $term_taxonomy_refs;
	public $terms_refs;
	public $usermeta_refs;
	public $users_refs;
	public $widget_refs;
	
	/**
	 * Additional fields - to be populated by extensions?
	 */
	public $table_refs;
	public $php_refs;
	
	function __construct( $code ) {
		$this->code = $code;
		$this->function = null;
		$this->status = null;
		$this->total_references = 0;
		// Are all the other fields null?
		// print_r( $this );
	}
	
	function add_ref( $id, $class ) {
		$property_name = "${class}_refs";
		echo "Adding {$this->code} $id $class $property_name" . PHP_EOL;
		$referenced = bw_array_get( $this->{$property_name}, $id, null );
		if ( !$referenced ) {
			$this->{$property_name}[ $id ] = $id;
			$this->total_references++;
		}
	}
	
	function report() {
		echo $this->code;
		echo " ";
		echo $this->total_references;
		echo PHP_EOL;
	}
	
	/**
	 * Populate the code field
	 * 
	 * 
	 */
	function code() {
		if ( !$this->code ) {
			$this->code = " - blank code";
		}
	}
	
	/**
	 * Populate the status field
	 *
	 * Since this is an orderable field we need to ensure it's not null
	 * Therefore we have to set a value when this is invoked
	 * 
	 Table copied from above
	 *
	 * Shortcode status can be one of the following
	 *
	 * Status | Meaning
	 * ------ | ----------
	 * Active | Registered by add_shortcode() with an implementing function
	 * Inactive | Registered by add_shortcode() to produce no output - __return_null() 
	 * 
	 * Unknown | Not known as a valid shortcode
	 * Known | Known to be a valid shortcode
	 * 
	 * Invalid | Appears to be a shortcode but has an invalid name
	 * False | Wasn't a shortcode after all
	 */
	
	function status() {
		if ( shortcode_exists( $this->code ) ) {
			$this->status = "Active";
		} else { 
			$this->status = "Unknown";
		}
		
	}


} 
 
