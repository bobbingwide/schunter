<?php // (C) Copyright Bobbing Wide 2015

/**
 * Implement "admin_menu" for Schunter
 */
function schunter_admin() {
	$hook = add_options_page( __( 'schunter', 'schunter' ), __( 'schunter', 'schunter' ), 'manage_options', 'schunter_options', 'schunter_options_do_page');
	
  add_action( "load-$hook", "schunter_add_options" );
  add_action( "admin_head-$hook", "schunter_admin_head" );
	
}

function schunter_options_do_page() {
	$tab = "codes";
  add_filter( "bw_nav_tabs_schunter", "schunter_nav_tabs", 10, 2);
  oik_menu_header( "Schunter admin", "w100pc" );
  oik_require( "includes/bw-nav-tab.php" );  
  //$tab = bw_nav_tabs( "self", "Self" );
  $tab = bw_nav_tabs( "codes", "Codes" );
  //oik_clone_reset_request_uri(); 
  do_action( "schunter_nav_tab_$tab" );
	bw_flush();
}


/**
 * 
 * Implement "admin_head-schunter-options_page_schunter" for oik-clone
 * 
 * When we're trying to display a List Table then hooking into 
 * nav-tabs is too late to load the classes since 
 * WordPress's get_column_headers() function invokes the hook to find the columns to be displayed.
 * and we need to have instantiated the class in order for this hook to have been registered.
 * Therefore, we need to hook into "admin_head" and determine what's actually happening.
 * Actually, we can hook into the specific action for the page.
 * 
 */
function schunter_admin_head() {
  $tab = bw_array_get( $_REQUEST, "tab", null );
	global $list_table;
  switch ( $tab ) {
        
    default:
			bw_trace2(); 
      oik_require_lib( "admin/schunter-list.php", "schunter" );
			
			oik_require( "includes/oik-list-table.php" );
			$list_table = bw_get_list_table( "Schunter_List_Table", array( "plugin" => "schunter", "tab" => $tab, "page" => "schunter" ) );
			add_action( "schunter_nav_tab_codes", "schunter_nav_tab_codes" );
  }
	
}


function schunter_add_options() {
  $option = 'per_page';
  $args = array( 'label' => __( 'Codes', 'schunter' )
               , 'default' => 20
               , 'option' => 'codes_per_page'
               );
  add_screen_option( $option, $args );

}


/**
 * Implement "schunter_nav_tab_codes" action for schunter
 * 
 * 
 * 
 */
function schunter_nav_tab_codes() {
	p( "schunter" );
  bw_flush();
	$schunter_list_table = Schunter_List_Table::instance();
  $schunter_list_table->prepare_items();
  $schunter_list_table->display();
	//	gob();
}

function schunter_nav_tabs( $tabs ) {
	return( $tabs );

}
