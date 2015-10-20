<?php // (C) Copyright Bobbing Wide 2015

/**
 * Schunter code refs
 *
 * Implements references to shortcodes
 *
 * Assumptions:
 * 
 * - Shortcodes may be referenced in basically any content which can contain strings
 * - Shortcode names must follow the "new" naming convention
 * - Shortcodes in content are considered fair game
 * - Shortcode names are not split between "fields" - they don't get magically constructed
 * - Shortcode names in parameters do not need to be detected 
 * - We can't look outside of the current site
 * - We don't expect shortcodes in URLs
 * 
 * Field in Schunter_code      | WordPress table  |	Notes
 * --------------------------- | ---------------- | ---------------
	 public $commentmeta_refs;	 | wp_commentmeta   | probably not a good idea to expand shortcodes in comment meta data
	 public $comments_refs;		   | wp_comments			| probably not a good idea to expand shortcodes in comments
	 public $links_refs;				 | wp_links         | Deprecated since WordPress 3.5
	 public $options_refs;			 | wp_options       | Not stored in the option_name but can be in any option value. May be in serialized data
	 public $postmeta_refs;			 | wp_postmeta      | Not stored in the meta_key but can be in any meta_value. May be in serialized data
	 public $posts_refs;				 | wp_posts         | Check post_title, post_content, post_excerpt 
	 public $sitemeta_refs; 		 | wp_sitemeta      | Not stored in the meta_key but can be in any meta_value. May be in serialized data
	 public $term_meta_refs;		 | wp_term_meta     | Not expected prior to WordPress 4.4?
	 public $term_taxonomy_refs; | wp_term_taxonomy | Check description
	 public $terms_refs;				 | wp_terms         | In the name? Perhaps not! 
	 public $usermeta_refs;			 | wp_user_meta     | Not stored in meta_key but can be in any meta_value. May be in serialized data
	 public $users_refs;				 | wp_users         | In the display_name? Perhaps not!
	 public $widget_refs;				 | wp_options       | Definitely in serialized data: option_name: 'widget_text'
	 
	 public $table_refs;         | any other        |	Other plugins deliver their own tables: BuddyPress, WooCommerce, MailPoet, etc
	 
	 public $php_refs;           | n/a              | Source code may have do_shortcode('[shortcode]');
 * 
 * 
 *  
 * 
 */
class Schunter_code_refs {

	/** 
	 * Link back to the shortcode ( Schunter_code object ) to which this is a reference
	 */												
	public $code = null; 
	 
	/**
	 * Is this just the class name?
	 *
	 */
	public $reference_type = null;
	
	/**
	 * Array of post_ids 
	 */
	public $post_ids = array();
	
	/**
	 * Array of other IDs - unique ID for the particular reference type 
	 */
	public $ids = array();
	
	/** 
	 * Constructor for Schunter_code_refs
	 */
	function __construct( $code ) {
	
		$this->code = $code;
		$this->reference_type = get_class( $this );
		$this->post_ids = array();
		$this->ids = array();
	}
	 
	/**
	 * Add a post ID to the list of posts implementing the shortcode
	 */ 
	function add( $id ) {
		$this->post_ids[] = $id;
	}
	
	/**
	 * Add an ID to the list of these things implementing the shortcode
	 */
	function add_id( $id ) {
		$this->ids[] = $id;
	}
	
	/**
	 * Report on the references
	 *
	 * Could these be links? 
	 *
	 */
	function report() {
		echo $this->code;
		echo " ";
    echo $this->reference_type;
		echo PHP_EOL;
		goban();
		//print_r( $this->post_ids );
		//print_r( $this->ids );
	} 
	

} 
	 
