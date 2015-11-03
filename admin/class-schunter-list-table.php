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
	
	
	public $total_items;
	
	
	/** 
	 * Sort order column
	 * 
	 * code or total_references 
	 */
  public $orderby = null;	 	
	
	/** 
	 * Sort order - ASC or DESC
	 */
	public $order = null;

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
		$columns['function'] = __( 'Function', 'schunter' );
		$columns['total_references'] = __( 'References', 'schunter' );
		bw_backtrace();
		bw_trace2( $columns, "columns", false );
		return( $columns );	
	}
	
	/**
	 * Get sortable columns 
	 */
	public function get_sortable_columns() {
		$sortable_columns = array();
		$sortable_columns['code'] = array( "code", false );
		$sortable_columns['status'] = array( "status", false );
		$sortable_columns['total_references'] = array( "total_references", false );
		bw_trace2();
		return( $sortable_columns );
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
		//$columns = get_column_headers( $this->screen );
		//$hidden = array();
		//$sortable = $this->get_sortable_columns();
		//$this->_column_headers = array( $columns, $hidden, $sortable );  
		//
		$this->reset_request_uri();
		$this->load_items(); 
		$this->populate_fields();            
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
	 *
	 * So when do we remove_query_arg() for orderby and order?
	 *
	 * Note: Don't call esc_url() when setting REQUEST_URI since this prevents
	 * add_query_arg() and remove_query_arg() from working properly.
	 */
	function reset_request_uri() {
		//$request_uri = $_SERVER['REQUEST_URI'];
		$request_uri = remove_query_arg( array( "action" ) );
		//bw_trace2( $request_uri, 'REQUEST_URI', false );
		$_SERVER['REQUEST_URI'] = $request_uri;	 // Don't do esc_url() here!
		//bw_trace2( $_SERVER['REQUEST_URI'], 'server REQUEST_URI', false );
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
	
	
	$data is a Schunter_code object
	
	
	    [code] => 
    [function] => 
    [status] => 
    [total_references] => 52
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
            [28299] => 28299
            [258] => 258
            [307] => 307
            [315] => 315
            [327] => 327
            [339] => 339
            [860] => 860
            [28727] => 28727
            [362] => 362
            [18262] => 18262
            [387] => 387
            [397] => 397
            [423] => 423
            [453] => 453
            [29119] => 29119
            [29128] => 29128
            [468] => 468
            [471] => 471
            [483] => 483
            [506] => 506
            [528] => 528
            [532] => 532
            [593] => 593
            [29251] => 29251
            [653] => 653
            [656] => 656
            [673] => 673
            [772] => 772
            [1014] => 1014
            [663] => 663
            [4698] => 4698
            [29343] => 29343
            [29410] => 29410
            [29424] => 29424
            [8351] => 8351
            [29585] => 29585
            [29744] => 29744
            [29771] => 29771
            [874] => 874
            [911] => 911
            [30018] => 30018
            [29916] => 29916
            [30057] => 30057
            [28051] => 28051
            [30089] => 30089
            [804] => 804
            [30118] => 30118
            [31678] => 31678
            [31653] => 31653
            [31679] => 31679
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
	
	function populate_fields() {
		foreach ( $this->items as $item => $code ) {
			bw_trace2( $item, "item", false );
			bw_trace2( $code, "code", false );
			$code->status();
			
		}
	} 
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
	
	/**
	 * Sort the full list of items
	 * 
	 * When the items are not loaded by WP_Query() then we need to put them in the required order manually
	 * using the defined sort sequence. 
	 *
	 * Note: WP_List_Table doesn't cater for sorting on multiple columns, so we don't either
	 */
	function sort_items() {
		$this->orderby();
		$this->order();
		$this->populate_orderby_field(); 
		usort( $this->items, array( $this, "sort_objects_by_code" ) );
	}
	
	/**
	 * Set the orderby value for sorting
	 *
	 * @TODO - Determine the default sort sequence from the sortable part of get_sortable_columns()
	 * AND or validate the sort column from this information
	 *
	 */
	function orderby() {
		$this->orderby = bw_array_get( $_GET, "orderby", "code" );
	}
	
	/**
	 * Set the sort sequence - 'asc' or 'desc'
	 *
	 * @TODO Validate the order as 'asc' or 'desc'
	 *
	 */
	function order() {
		$this->order = bw_array_get( $_GET, "order", "asc" );
	}
	
	function populate_orderby_field() {
		$orderby = $this->orderby;
		foreach ( $this->items as $item => $code ) {
			if ( !$code->{$orderby} ) {
				$code->{$orderby}();
			}
		}
	}
	
	/**
	 * Sort objects
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
	 * @param object $a - first item to be sorted
	 * @param object $b - second item to be sorted
	 * @return integer -1 if a to be before b, 0 if equal, 1 if a to be after b
	 */
	function sort_objects_by_code( $a, $b ) {
		$property_name = $this->orderby;
		if ( $a->{$property_name} == $b->{$property_name} )  { 
			$result = 0; 
		} elseif ( $a->{$property_name} < $b->{$property_name} ) {
			$result = -1;
		} else {
			$result = 1;
		}
		if ( $this->order == "desc" ) {
			$result = -$result;
		}
		return( $result );
	}





}
