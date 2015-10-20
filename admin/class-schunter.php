<?php // (C) Copyright Bobbing Wide 2015

/**
 *
 * Class Schunter
 * 
 * Implements the Short Code Hunter 
 * 
 */
class Schunter {

	/**
	 * Options 
	 *
	 * Class to manage the WordPress options for the Schunter
	 */
	public $options;

	/**
	 * Codes
	 * 
	 * Container for the known and unknown shortcodes
	 *
	 */
	public $codes;

	/**
	 * @var Schunter - the true instance
	 */
	private static $instance;

	/**
	 * Return a single instance of this class
	 *
	 * @return object 
	 */
	public static function instance() {
		if ( !isset( self::$instance ) && !( self::$instance instanceof Schunter ) ) {
			self::$instance = new Schunter;
		}
		return self::$instance;
	}
	
	public $done = 0;
	
	/**
	 * Contruct the Schunter
	 */
	function __construct() {
	
		oik_require( "admin/class-schunter-options.php", "schunter" );
		oik_require( "admin/class-schunter-codes.php", "schunter" );
		oik_require( "admin/class-schunter-code.php", "schunter" );
		oik_require( "admin/class-schunter-code-refs.php", "schunter" );
		//oik_require( "admin/class-schunter-code-ref.php", "schunter" );
		$this->options = new Schunter_options();
		$this->codes = new Schunter_codes();
	}
	
	/**
	 * Run the Shortcode hunter
	 *
	 * There are multiple stages to building the information that maps shortcode usage 
	 * First and foremost is understanding the usage of shortcodes in posts and postmeta
	 */
	function run() {
		echo "Schunting for shortcodes";
		$this->codes->fetch();
		$this->options->fetch();
		$this->schunt_posts();
		
		// $this->schunt_
		$this->end_process();
	}
	
	/**
	 * 	Search posts and postmeta for possible shortcodes
	 *
	 */
	function schunt_posts() {
		oik_require( "includes/bw_posts.inc" );
		$posts_per_page = $this->options->posts_per_page;
		$page = 0;
		
		$date_query = array( "after" => $this->options->last_date
											, "column" => "post_modified_gmt"
											);
		
		$args = array( "post_type" => "any"
					, "orderby" => "modified"
					, "order" => "ASC"
					, "post_status" => "any"
					, "posts_per_page" => $posts_per_page
					, "numberposts" => -1
					, "cache_results" => false
					, "date_query" => $date_query
					);
				
		$posts = 	bw_get_posts( $args );
		
		while ( count( $posts ) > 0 ) {							 
			foreach ( $posts as $post ) {
				$this->schunt_post( $post );
				$this->schunt_postmeta( $post );
				$this->done( $post );
				unset( $post );
			}
			$page++;
			$args['paged'] = $page;
			unset( $posts );
			$posts = bw_get_posts( $args );
		}
		
	
	}
	
	/**
	 * End the processing for this instance
	 *
	 * Report what we've done, update then die
	 *
	 *
	 */
	function end_process() {
		$this->report();
	 	$this->update();
		die();
	}
	
	/**
	 * 
	 */
	function done( $post ) {
		$this->done++;
		$this->options->last_date( $post->post_modified_gmt );
		echo "{$this->done} of {$this->options->limit} " . PHP_EOL;
		if ( $this->done >= $this->options->limit ) {
			echo "Stopping now" . PHP_EOL;
			$this->end_process();
		}
		
	}
	
	function update() {	
		$this->options->update();
		$this->codes->update();
	}
		
	/**
	 * Hunt for shortcodes in the post
	 *
	 * @TODO - Do we see revisions here? If no, does it matter?
	 *
	 */		
	function schunt_post( $post ) {
		echo "{$post->ID} {$post->post_type} {$post->post_modified}" . PHP_EOL;
		if ( $post->post_type == "revision" ) gob();
		$codes = $this->schunt_codes( $post->post_title );
		$codes += $this->schunt_codes( $post->post_content );
    $codes += $this->schunt_codes( $post->post_excerpt );
		$this->codes->add_codes( $codes, $post->ID, "posts" );
	}	
	
	/**
	 * Hunt for shortcodes in the post meta data
	 *
	 * Search every post meta field regardless of its use
	 */
	function schunt_postmeta( $post ) {
		$post_meta = get_post_meta( $post->ID );
		$codes = array();
		foreach ( $post_meta as $meta_data ) {
			foreach ( $meta_data as $index => $value ) {
				$codes += $this->schunt_codes( $value );
			}
		}
		$this->codes->add_codes( $codes, $post->ID, "postmeta" );
	}
	
	/**
	 * Hunt for anything that looks like a shortcode
	 *
	 * We don't care about the context here  
	 * All we need to find is shortcodes
	 * So we can simply break the content down by exploding on the '['s
	 * Here we find both start and ending shortcodes...
	 * There is a possibility that the ending shortcode is not the same as the starting one
	 * 
	 * @param string $text which may contain a shortcode
	 * @return array $codes the things we think are shortcodes
	 *
	 */
	function schunt_codes( $text ) {
		$codes = array();
		if ( false !== strpos( $text, "[" ) ) {
			$bits = explode( "[", $text ); 
			//print_r( $bits );
			array_shift( $bits );
			foreach ( $bits as $bit ) {
				if ( strlen( $bit ) > 0 ) {
					$bit = strtr( $bit, "/]", "  " );
					$bit = rtrim( $bit );
					$sc = explode( " ", $bit ); 
					$code = $sc[0];
					//echo "Code: $code" . PHP_EOL;
					$invalid = preg_match( '@[<>&/\[\]\x00-\x20=]@', $code );
			 
					if ( $invalid ) {
						echo "Invalid shortcode detected? $code" . PHP_EOL;
						echo $bit . PHP_EOL;
						echo PHP_EOL;
						//echo $text . PHP_EOL;
						
						//gobang();
						//do_continue();
						//oikb_get_response();
				 } else {
            //echo "Code: $code looks like a good un" . PHP_EOL;
						$codes[ $code ] = $code;
					} 
				} 
			}
		}
		//print_r( $codes );
		//gobang();
		return( $codes );			
			
	}
	
	function report() {
		print_r( $this->options );
		//print_r( $this->codes );
		print_r( $this->done );
		$this->codes->report();
	
	}
	
	/** #34090 
	 
	
	 if ( '' == $tag || 0 !== preg_match( '@[<>&/\[\]\x00-\x20]@', $tag ) ) { 
 	93	                _doing_it_wrong( __FUNCTION__, __( 'Invalid shortcode name. Do not use reserved chars: & / < > [ ]' ), '4.4' ); 
 	94	                return; 
 	95	        } 
	*/
}
