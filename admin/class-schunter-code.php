<?php // (C) Copyright Bobbing Wide 2015

/**
 * Schunter code
 *
 * Implements a single instance of a shortcode
 *
 * 
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
		// Are all tother fields null?
		//print_r( $this );
	}
	
	function add_ref( $id, $class ) {
		echo "Adding ref $id $class" . PHP_EOL;
		//$class_name = "Schunter_code_ref_$class";
		//$ref_obj = new $class_name( $id );
		
		$property_name = "${class}_refs";
		echo "Property: $property_name!" . PHP_EOL;
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


} 
 
