<?php
/**
 * Plugin Name: Sidetrack Protected
 * Plugin URI: https://github.com/SidetrackStudio/sidetrack-protected
 * Description: A forked copy of Password Protected, which hides your WordPress site behind a login screen with a single password. Please note: This plugin does not restrict access to uploaded files and images and does not work with some caching setups.
 * Version: 2.2.3
 * Author: Ben Huson & pbrocks
 * Text Domain: sidetrack-protected
 * Author URI: http://github.com/pbrocks/
 * License: GPLv2
 */


/*
Copyright 2012 Ben Huson (email : ben@thewhiteroom.net)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * @todo Use wp_hash_password() ?
 * @todo Remember me
 */

define( 'SIDETRACK_PROTECTED_SUBDIR', '/' . str_replace( basename( __FILE__ ), '', plugin_basename( __FILE__ ) ) );
define( 'SIDETRACK_PROTECTED_URL', plugins_url( SIDETRACK_PROTECTED_SUBDIR ) );
define( 'SIDETRACK_PROTECTED_DIR', plugin_dir_path( __FILE__ ) );

define( 'SIDETRACK_PROTECTED_BASENAME', plugin_basename( __FILE__ ) );

global $Sidetrack_Protected;

if ( is_admin() ) {
	include_once( 'classes/class-sidetrack-protected-admin.php' );
}

require 'classes/class-sidetrack-customizer.php';
require 'classes/class-sidetrack-protected.php';
$Sidetrack_Protected = new Sidetrack_Protected();

