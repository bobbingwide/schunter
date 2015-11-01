<?php // (C) Copyright Bobbing Wide 2015


class Schunter_List_Table extends BW_List_Table {

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
	 * @var Schunter_List_Table - the true instance
	 */
	private static $instance;

	/**
	 * Return a single instance of this class
	 *
	 * @return object 
	 */
	public static function instance() {
		if ( !isset( self::$instance ) && !( self::$instance instanceof Schunter_List_Table ) ) {
			self::$instance = new Schunter_List_Table;
		}
		return self::$instance;
	}
	
	public $total_items;


	/**
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'Title'
	 *
	 * @since 3.1.0
	 * @access public
	 * @abstract
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array();
		$columns['cb'] = '<input type="checkbox" />';
		$columns['code'] = __( "Code", "schunter" ); 
		$columns['status'] = __( "Status", "schunter" );
		$columns['total_references'] = __( 'References', 'schunter' );
		return( $columns );	
	}

	/**
	 * Prepare the contents to be displayed
	 * 
	 * 1. Decide which columns are going to be displayed
	 * 2. Work out what page we're on
	 * 3. Load the items for the page
	 * 
	 */
	public function prepare_items() {
		$columns = get_column_headers( $this->screen );
		$hidden = array();
		$sortable = array(); 
		$this->_column_headers = array( $columns, $hidden, $sortable );  
		//
		$this->reset_request_uri();
		$this->load_items(); 
		bw_trace2( $this, "prepared_items", false, BW_TRACE_DEBUG );
		}

	/** 
	 * Load the schunted codes
	 *
	 */
	function load_items() {
		oik_require( "admin/class-schunter-codes.php", "schunter" );
		oik_require( "admin/class-schunter-code.php", "schunter" );
		$this->codes = new Schunter_codes();
		$this->codes->fetch();
		$this->items = $this->codes->codes;
		$this->total_items = count( $this->items );
		$atts = array();
		$atts = $this->determine_pagination( $atts ); 
		$this->sort_items();
		$this->select_items( $atts );            
		$this->record_pagination( $atts );
	}

/**
	 * Ensure the pagination links don't attempt to perform any actions
	 * 
	 * REQUEST_URI is used by BW_List_Table::pagination() to build the paging links
	 * we need to ensure that only pagination is performed.
	 * So we need to remove the fields that can be set on the action links
	 * ie. action should not be set.
	 *  
	 */
	function reset_request_uri() {
		//$request_uri = $_SERVER['REQUEST_URI'];
		$request_uri = remove_query_arg( array( "action" ) );
		$_SERVER['REQUEST_URI'] = esc_url( $request_uri );
	} 

	
	/**
	  [codes] => Schunter_codes Object
        (
            [codes] => Array
                (
                    [bw_css] => Schunter_code Object
                        (
                            [code] => bw_css
                            [function] => 
                            [status] => 
                            [total_references] => 2
                            [comments_refs] => 
                            [commentmeta_refs] => 
                            [links_refs] => 
                            [options_refs] => Array
                                (
                                    [widget_text] => widget_text
                                )

                            [postmeta_refs] => 
                            [posts_refs] => Array
                                (
                                    [31740] => 31740
                                )

                            [sitemeta_refs] => 
                            [term_meta_refs] => 
                            [term_taxonomy_refs] => 
                            [terms_refs] => 
                            [usermeta_refs] => 
                            [users_refs] => 
                            [widget_refs] => 
                            [table_refs] => 
                            [php_refs] => 
                        )
	*/ 
		/**
	 * Determine pagination parameters
	 * 
	 * Before calling get_posts we need to know which page we're displaying so that we only return the relevant rows
	 * 
	 * We need to know the pagination stuff here so that we load the correct set of posts.
	 * We just need to know posts_per_page and the page number ( 'paged' )
	 * but in order to perform pagination we need to use WP_Query which enables us to find the total number of posts
	 * 
	 * @param array $atts - parameters to be passed to the routine that accesses the content
	 * @return array $atts - updated atts array
	 * 
	 */                                      
	function determine_pagination( $atts ) {
		$page = bw_array_get( $_REQUEST, "paged", 1 );
		$atts['paged'] = $page;
		$atts['posts_per_page'] = $this->get_items_per_page( "codes_per_page" );
		return( $atts );
	}
	  
	/**
	 * Set the pagination args based on what we found
	 * 
	 * @param array $atts - 
	 *
	 */
	function record_pagination( $atts ) {
		//$page = bw_array_get( $atts, "paged", 1 );
		$posts_per_page = bw_array_get( $atts, "posts_per_page", null );
		$count = $this->total_items;
		$pages = ceil( $count / $posts_per_page ); 
		$args = array( 'total_items' => $count
								 , 'total_pages' => $pages
								 , 'per_page' => $posts_per_page
								 );
		$this->set_pagination_args( $args );
	}
	
	/**
	 * Reduce items to just those that need to be displayed
	 */
	function select_items( $atts ) {
		$page = $atts['paged'];
		$posts_per_page = $atts['posts_per_page'];
		$pages = ceil( $this->total_items / $posts_per_page );
    $start = ( $page-1 ) * $posts_per_page;
    $end = min( $start + $posts_per_page, $this->total_items ) -1 ;  
		$this->items = array_slice( $this->items, $start, 1+ $end-$start );
		bw_trace2( $this->items, "selected items" );
	
	}
	
	function sort_items() {
		usort( $this->items, array( $this, "sort_objects_by_code" ) );
	}
	
	/**
	 * Sort code objects by 'code'
	 * 
	 * This sorts ASC. If we want DESC we can simply negate the $result
	 *
	 * @TODO Should we concern ourselves about case sensitivity?
	 * 
	 * See {link http://davidwalsh.name/sort-objects}
	 * See notes on usort() producing warnings - which will happen if we trace the parameters
	 *
	 * `
	  [code] => admin
            [function] => 
            [status] => 
            [total_references] => 1
            [comments_refs] => 
            [commentmeta_refs] => 
            [links_refs] => 
            [options_refs] => Array
                (
                    [widget_text] => widget_text
                )

            [postmeta_refs] => 
            [posts_refs] => 
            [sitemeta_refs] => 
            [term_meta_refs] => 
            [term_taxonomy_refs] => 
            [terms_refs] => 
            [usermeta_refs] => 
            [users_refs] => 
            [widget_refs] => 
            [table_refs] => 
            [php_refs] => 
			`
	 */
	
	function sort_objects_by_code( $a, $b ) {
		if ( $a->code == $b->code )  { 
			$result = 0; 
		} elseif ( $a->code < $b->code ) {
			$result = -1;
		} else {
			$result = 1;
		}
		return( $result );
	}





}
