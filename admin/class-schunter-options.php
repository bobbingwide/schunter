<?php // (C) Copyright Bobbing Wide 2015

/**
 * Schunter options
 *
 * Contains the options data for Schunter
 *
 * This is used to control the scanning of the WordPress database for content which appears to contain shortcodes
 * 
 * 
 */

class Schunter_options {

	/**
	 * Total number of posts to process in an invocation
	 */
	public $limit;
	
	/**
	 * Posts to load per block
	 */ 
	public $posts_per_page;
	
	/**
	 * Last date processed
	 *
	 * Reset this to 0 to restart the processing
	 */
	public $last_date;
	
	/**
	 * Not sure we need this
	 */
	public $max_id;
	
	function __construct() {
		$this->limit = 1000;
		$this->posts_per_page = 100;
		$this->last_date = 0;
		$this->max_id = 0;
	}
	
	function fetch() {
		$options = get_option( "schunter" );
		$this->limit = bw_array_get( $options, "limit", $this->limit );
		$this->posts_per_page = bw_array_get( $options, "posts_per_page", $this->posts_per_page );
		$this->last_date = bw_array_get( $options, "last_date", $this->last_date );
		$this->max_id = bw_array_get( $options, "max_id", $this->max_id );
	}
	
	function last_date( $last_date ) {
		$this->last_date = $last_date;
		
	
	}
	
	function update() {
		//$options = get_option( "schunter" );
		$updated = array( "limit" => $this->limit
									, "post_per_page" => $this->posts_per_page
									, "last_date" => $this->last_date
									, "max_id" => $this->max_id
									);
		update_option( "schunter", $updated );
		
	}


}
