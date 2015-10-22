<?php // (C) Copyright Bobbing Wide 2015

/**
 *
 * Class Schunter
 * 
 * Implements the Short Code Hunter ( schunter ) 
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
	 * Recently found shortcodes
	 * 
	 * Array of codes we're building up while schunting
	 *
	 */															
	public $found_codes = array();
	
	/**
	 * Skip to here.
	 * 
	 */
	public $skipping_to = null;
	
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
	 * then we look at options - particularly widget_text
	 * and then all the other places, including multisite tables
	 
	 	field                         table            | Notes
		--------------------------- | ---------------- | ---------------------------------------
	  public $commentmeta_refs;	  | wp_commentmeta   | probably not a good idea to expand shortcodes in comment meta data
	  public $comments_refs;	    | wp_comments			 | probably not a good idea to expand shortcodes in comments
	  public $links_refs;				  | wp_links         | Deprecated since WordPress 3.5
	y public $options_refs;			  | wp_options       | Not stored in the option_name but can be in any option value. May be in serialized data
	y public $postmeta_refs;		  | wp_postmeta      | Not stored in the meta_key but can be in any meta_value. May be in serialized data
	y public $posts_refs;				  | wp_posts         | Check post_title, post_content, post_excerpt 
	  public $sitemeta_refs; 		  | wp_sitemeta      | Not stored in the meta_key but can be in any meta_value. May be in serialized data
	  public $term_meta_refs;		  | wp_term_meta     | Not expected prior to WordPress 4.4?
	  public $term_taxonomy_refs; | wp_term_taxonomy | Check description
	  public $terms_refs;				  | wp_terms         | In the name? Perhaps not! 
	  public $usermeta_refs;		  | wp_user_meta     | Not stored in meta_key but can be in any meta_value. May be in serialized data
	  public $users_refs;				  | wp_users         | In the display_name? Perhaps not!
	  public $widget_refs;			  | wp_options       | Definitely in serialized data: option_name: 'widget_text'
	 
	 */
	function run() {
		echo "Schunting for shortcodes" . PHP_EOL;
		$this->codes->fetch();
		$this->options->fetch();
		$this->schunt_posts();
		
		//$this->schunt_comments();
		//$this->schunt_links();
		$this->schunt_options();
		//$this->schunt_terms();
		//$this->schunt_users();
		
		
		// $this->schunt_
		$this->end_process();
	}
	
	/**
	 * Search posts and postmeta for possible shortcodes
	 *
	 * Note: We don't see posts which are not publicly searchable, so we don't see "revisions".  
	 * This is not considered to be a problem right now. 
	 * To fix this we can temporarily hack with the registered post types so that the query finds more things
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
	 * Mark another post as completed
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
	 */		
	function schunt_post( $post ) {
		echo "{$post->ID} {$post->post_type} {$post->post_modified}" . PHP_EOL;
		if ( $post->post_type == "revision" ) gob();
		$codes = $this->schunt_codes( $post->post_title );
		$codes += $this->schunt_codes( $post->post_content );
    $codes += $this->schunt_codes( $post->post_excerpt );
		$this->add_codes( $post->ID, "posts" );
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
		$this->add_codes( $post->ID, "postmeta" );
	}
	
	/**
	 * Hunt for shortcodes in options
	 *
	 * Note: This includes transients and widget text.
	 *
	 * @TODO Filter out options we're not interested in. e.g. 
	 * - exploitscanner_results
	 * - rewrite_rules
	 * - _site_transient_update_plugins
	 *
	 */
	function schunt_options() {
		$options = $this->load_alloptions();
		if ( count( $options ) ) {
			//$codes = array();
			foreach ( $options as $option_key => $option_value ) {
				echo "Schunting option: $option_key" . PHP_EOL;
				if ( $option_key == "exploitscanner_results" ) {
					continue;
				}
				if ( $option_key == "rewrite_rules" ) {
					continue;
				}
				$codes = $this->schunt_codes( $option_value );    
				$this->add_codes( $option_key, "options" );
			}
		}
	}

	/**
	 * Get all options containing '[', including those with autoload='no'
	 *
	 * @TODO There's some belt and braces stuff going on here. Remove unnecessary test when satisfied with results.
	 * 
	 */
	function load_alloptions() {
		global $wpdb;

    //$suppress = $wpdb->suppress_errors();
    $alloptions_db = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options where option_value like '%[%'" );
    //$wpdb->suppress_errors($suppress);
		//print_r( $alloptions_db );
    $alloptions = array();
    foreach ( (array) $alloptions_db as $o ) {
			if ( false !== strpos( $o->option_value, "[" ) ) {
				$alloptions[$o->option_name] = $o->option_value;
				echo $o->option_name . PHP_EOL;
			}
    }
		return $alloptions;
	}

	/**
	 * Hunt for anything that looks like a shortcode
	 *
	 * We don't care about the context here  
	 * All we need to find is shortcodes
	 * So we can simply break the content down by exploding on the '['s
	 * Here we find both start and ending shortcodes...
	 *
	 * If the content has been constructed incorrectly, then there is a possibility that the ending shortcode is not the same as the starting one.
	 * But we don't really care about this. 
	 *
	 *
	 * We need to be able to detect serialized data and probably JSON or CSS and process it accordingly.
	 * use maybe_unserialize() for the serialized data?
	 * 
	 * 
	 * @param string $text which may contain a shortcode
	 * @return array $codes the things we think are shortcodes
	 *
	 */
	function schunt_codes( $text ) {
		$schunt = $this->schunt( $text );
		if ( $schunt ) {
			echo "Length: " . strlen( $text ) . PHP_EOL;
			//docontinue();
			$deser = maybe_unserialize( $text ); 
			if ( is_array( $deser ) ) {
				echo "Deser: " . count( $deser ) . PHP_EOL;
				$codes = $this->schunt_codes_from_array( $deser );
			} else {
				$codes = $this->schunt_codes_from_text( $text ); 
			}
		} else { 
			$codes = array();
		}
		return( $codes );
	}
	
	function schunt( $text ) {
		if ( false !== strpos( $text, "[" ) ) {
			$schunt = true;
		} else {
			$schunt = false;
		}
		return( $schunt );
	}
	
	/**
	 * Re-initialise found_codes
	 */
	function schunt_start() {	
		$this->found_codes = array();
	}
	
	/**
	 * Add the found codes
	 *
	 * Update the $codes with the found codes
	 * then reset for next time
	 *
	 * @param string|integer $id Identifier for the content
	 * @param string $class Content type
	 */
	function add_codes( $id, $class ) {
		$this->codes->add_codes( $this->found_codes, $id, $class );
		$this->schunt_start();
	}
		
	/**
	 * Extract shortcodes from a text string
	 *
	 * First we break the string down into chunks of HTML
	 * then we process the shortcodes within the HTML
	 * or the shortcodes in plain text
	 * Ignoring sections where we don't expect to see shortcodes
	 * 
	 *
	 * @param string $text a text field
	 * @return array of shortcodes, may be an empty array 
	 */
	function schunt_codes_from_text( $text ) {
		$codes = array();
		if ( $this->schunt( $text ) ) {
			$text_array = wp_html_split( $text );
			//print_r( $text_array );
			foreach ( $text_array as $text ) {
				if ( $this->skip( $text ) ) {
					continue;
				}	
			
				if ( $this->schunt( $text ) ) {
					$bits = explode( "[", $text ); 
					//print_r( $bits );
					array_shift( $bits );
					
					foreach ( $bits as $bit ) {
						if ( $this->skip( $bit ) ) {
							continue;
						}
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
								echo $text . PHP_EOL;
								
								//gobang();
								docontinue();
								//oikb_get_response();
						 } else {
							 //echo "Code: $code looks like a good un" . PHP_EOL;
								$codes[ $code ] = $code;
								$this->found_codes[ $code ] = $code;
								$this->check_code_skip( $code );
							} 
						} 
					}	
				}
			}
		}
		//print_r( $codes );
		//gobang();
		return( $codes );			
	}
	
	/**
	 * Determine if were skipping
	 *
	 * @TODO Decide if we should also ignore the tags returned from 'no_texturize_tags'
	 * $default_no_texturize_tags = array('pre', 'code', 'kbd', 'style', 'script', 'tt');
	 *
	 * @TODO Should these tests be case insensitive?
	 *
	 * @param string $text line to test
	 * @return string the current value of skipping_to
	 */
	function skip( $text ) {
		//print_r( $text );
		//docontinue( "test_skip: {$this->skipping_to}" );
    if ( $this->skipping_to ) {
			if ( 0 === strpos( $text, $this->skipping_to ) ) {
				$this->skipping_to = null;	
			}
		} else {
			if ( 0 === strpos( $text, "<script" ) ) {
				$this->skipping_to = "</script";
			}elseif ( 0 === strpos( $text, "<style" ) ) {
				$this->skipping_to = "</style";
			} 
		}	
		return( $this->skipping_to );
	} 
	
	/**
	 * Check if we should be skipping shortcodes in content
	 *
	 * @TODO This is just a prototype using a known shortcode where we don't expect shortcodes since the content is CSS.
	 * We could use the 'no_texturize_shortcodes' filter
	 *
	 * Other examples:
	 * - code - is in $default_no_texturize_shortcodes
	 * - bw_geshi
	 * - bw_graphviz 
	 * 
	 * 
	 * @param string $code - the code we're testing for
	 */
	function check_code_skip( $code ) {
		static $no_texturize_shortcodes = null;
		if ( !$no_texturize_shortcodes ) {
			$no_texturize_shortcodes = apply_filters( "no_texturize_shortcodes", array( "codes" ) );
			$no_texturize_shortcodes = bw_assoc( $no_texturize_shortcodes );
		} 
		if ( bw_array_get( $no_texturize_shortcodes, $code, null ) ) {
			$this->skipping_to = "/$code";
		}
	}
	
	/**
	 * Extract shortcodes from an array 
	 *
	 * We assume the array is deserialized data
	 * We need to process each child array, so we do it recursively
	 *
	 * @param array $deser array of fields which may themselves be array
	 */
	function schunt_codes_from_array( $deser ) {
		foreach ( $deser as $key => $value ) {
			if ( is_array( $value ) ) {
				$this->schunt_codes_from_array( $value );
			} else {
				if ( $this->schunt( $value ) ) {
					$this->schunt_codes_from_text( $value );
				}	
			}		
		}
	}
	
	/**
	 * Produce a simple report of the shortcodes
	 */
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
