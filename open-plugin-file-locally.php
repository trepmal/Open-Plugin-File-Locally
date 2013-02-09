<?php
/*
Plugin Name: Open Plugin File Locally
Plugin URI: http://trepmal.com/
Description: Adds link to plugin list to open plugin files in their default desktop application. http://cl.ly/HFXW Requiments: Mac OSX, WP on localhost
Version: 0.1
Author: Kailey Lampert
Author URI: http://kaileylampert.com

Copyright (C) 2012  Kailey Lampert

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

new Open_Plugin_File_Locally();

class Open_Plugin_File_Locally {

	function __construct() {
		//register ajax callback function. The part after "wp_ajax_" (here 'do_local_shell') will be used in the js
		//the '_x' is just to differentiate, to reduce confusion
		add_action( 'wp_ajax_do_local_shell', array( &$this, 'do_x_local_shell_cb' ) );
		//add my link to plugins actions
		add_filter( 'plugin_action_links', array( &$this, 'plugin_link' ), 10, 4 );
		add_filter( 'network_admin_plugin_action_links', array( &$this, 'plugin_link' ), 10, 4 );
		//load script in footer (promises jquery will already be loaded)
		add_action( 'admin_footer-'.'plugins.php', array( &$this, 'script' ) );
	}

	function do_x_local_shell_cb() {
		$local_path = esc_attr( $_POST['path'] );
		$local_dir = dirname($local_path);

		shell_exec( "open '$local_dir'" ); //open parent directory (launches finder)
		//shell_exec( "open '$local_path'" ); //open file (launches default editor)
		shell_exec( "open '$local_path' -a Sublime\ Text\ 2" ); //open file (launches specific editor)
		die();
	}

	function plugin_link( $actions, $plugin_file, $plugin_data, $context ) {
		$dir = ( 'mustuse' == $context ) ? WPMU_PLUGIN_DIR : WP_PLUGIN_DIR;
		$plugin_path =  "$dir/$plugin_file";
		$actions['plugin_path'] = "<a href='$plugin_path'>Open in Desktop Editor</a>";
		return $actions;
	}

	function script() {
		?><script>
			jQuery(document).ready( function($) {
				$('.plugin_path a').click( function( ev ) {
					$.post( ajaxurl, {
						action: 'do_local_shell', // this was registered in the 'wp_ajax_' action hook
						path: $(this).attr('href') //this will be sent to the ajax callback function
					}, function( response ) { //response is the output from the function
						//do something to let the user know we did something
						//console.log( response )
					});
					ev.preventDefault();
				});
			});
		</script><?php
	}

}
