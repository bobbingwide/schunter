<?php // (C) Copyright Bobbing Wide 2015

/*
Plugin Name: schunter
Plugin URI: http://www.oik-plugins.com/oik-plugins/schunter
Description: short code hunter
Version: 0.0.1
Author: bobbingwide
Author URI: http://www.oik-plugins.com/author/bobbingwide
License: GPL2

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
			echo PHP_SAPI;
			echo PHP_EOL;
			$included_files = get_included_files();
			// print_r( $included_files[0] );
			if ( $included_files[0] == __FILE__) {
				schunter_run();
			} else {
				//  has been loaded by another PHP routine so that routine is in charge. e.g. boot-fs.php for WP-CLI
				$basename = basename( $included_files[0] );
				if ( $basename == "oik-wp.php" ) {
					schunter_run();
				}
			}	
			echo "End cli:" . __FUNCTION__ . PHP_EOL; 
		}
	} else {
		//echo PHP_SAPI;
		//echo PHP_EOL;
		if ( function_exists( "bw_trace2" ) ) {
			bw_trace2( PHP_SAPI, "schunter loaded in WordPress environment?" );
		}
		if ( function_exists( "add_action" ) ) {
			// if ( bw_is_wordpress() ) {
			//add_action( "admin_notices", "oik_batch_activation" );
			//add_action( "oik_admin_menu", "oik_batch_admin_menu" );
		}
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

schunter_loaded();


