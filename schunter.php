<?php // (C) Copyright Bobbing Wide 2015

/*
Plugin Name: schunter
Plugin URI: http://www.oik-plugins.com/oik-plugins/schunter
Description: short code hunter
Version: 0.0.2
Author: bobbingwide
Author URI: http://www.oik-plugins.com/author/bobbingwide
Text Domain: schunter
Domain Path: /languages/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

    Copyright 2015 Bobbing Wide (email : herb@bobbingwide.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/

/**
 * Process schunter depending on how invoked
 *
 * short code hunter ( schunter ) is initially designed to be run in a batch mode
 * as we first want to find out how many shortcodes are being used and where they're being used.
 * This will help us to determine whether or not it makes sense to register a subset of shortcodes.
 * Of course, most plugins and themes register all their shortcodes willy-nilly.
 * Some re-education may be needed.
 * 
 * - This code is not yet designed to be run under WP-cli
 * - This code does not have a WordPress front-end or admin back end
 * - It runs under oik-wp.php
 * - When invoked from "oik-wp.php" ( part of oik-batch ) then it will run schunter_run()
 * - The code is dependent upon oik APIs
 * - It has not been tested without the oik base plugin being active
 */
function schunter_loaded() {
	if ( PHP_SAPI == "cli" ) {
		if ( $_SERVER['argv'][0] == "boot-fs.php" )   {
			// This is WP-CLI
		} else {
			//oik_require_lib( "oik-cli" );
			oik_batch_load_cli_functions();
			if ( oik_batch_run_me( __FILE__ ) ) {
				//schunter_run();
				echo "End cli:" . __FUNCTION__ . PHP_EOL; 
			}
		}
	} else {
		//echo PHP_SAPI;
		//echo PHP_EOL;
		if ( function_exists( "bw_trace2" ) ) {
			bw_trace2( PHP_SAPI, "schunter loaded in WordPress environment?" );
		}
	}
	if ( function_exists( "add_action" ) ) {
		// if ( bw_is_wordpress() ) {
		//add_action( "admin_notices", "oik_batch_activation" );
		add_action( "admin_menu", "schunter_admin_menu" );
		add_filter( 'set-screen-option', "schunter_set_screen_option", 10, 3 );
		add_action( "run_schunter.php", "schunter_run" );
	}
}

/**
 * Run the shortcode hunter
 */
function schunter_run() {
	$schunter = schunter();
	$schunter->run();
}

/**
 * Return the singular instance of the Schunter class
 *
 * @return object the Schunter class
 */
function schunter() {
	if ( !class_exists( "Schunter" ) ) {
		oik_require( "admin/class-schunter.php", "schunter" );
	}
	if ( class_exists( "Schunter" ) ) {
		$schunter = Schunter::instance();
	} else {
		die();
	}
	return( $schunter );
}

function schunter_admin_menu() {

	//	if ( is_admin() ) {   
			oik_require( "admin/schunter.php", "schunter" );
			schunter_admin();
	//	}
	
}

/**
 * Implement "set-screen-option" for schunter
 * 
 * Note: set-screen-option is called before 'admin_init'
 * so this filter has to be added early on, but probably only when WP_ADMIN is true.
 *
 * @param bool $setit originally false 
 * @param string $option option name to set
 * @param string $value value to be set
 * @return $value if the option is to be set, false otherwise  
 */
function schunter_set_screen_option( $setit, $option, $value ) {
  $isay = $setit;
  if ( $option == 'codes_per_page' ) {
		$value = (int) $value;
    if ( $value > 0 && $value <= 999 ) {
			$isay = $value;
		}
  } else {
    bw_backtrace();
  }
  return( $isay );
}

schunter_loaded();


