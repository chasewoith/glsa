<?php
/**
 * @package   	      WP E-Signature - Contact Form 7 Digital Signature Addon
 * @contributors      Kevin Michael Gray (Approve Me), Abu Shoaib (Approve Me), Arafat Rahman (Approve Me)
 * @wordpress-plugin
 * Plugin Name:       Contact Form 7 digital signature add-ons 
 * Plugin URI:
 * Description:       Contact Form 7 digital signature add-ons
 * Version:           1.5.3
 * Author:            Approve Me
 * Author URI:        http://aprv.me/2l9DMvA
 * License:           GPLv2+
 * Text Domain:       cf7-wpesignature
 * Domain Path:       /languages
 * Documentation:     #
 */

/**
 * Copyright (c)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


/**
 * Define constants
 */
define( 'CF7_WPESIGNATURE_VER', '1.5.3' );
define( 'CF7_WPESIGNATURE_URL',     plugin_dir_url( __FILE__ ) );
define( 'CF7_WPESIGNATURE_PATH',    dirname( __FILE__ ) . '/' );
define( 'CF7_WPESIGNATURE_CORE',    dirname( __FILE__ )  );

/**
 * Default initialization for the plugin:
 * - Registers the default textdomain.
 */


/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/
 require_once( plugin_dir_path( __FILE__ ) . 'admin/includes/esig-cf7-settings.php' );
require_once( plugin_dir_path( __FILE__ ) . 'admin/includes/esig-cf7.php' );
require_once( plugin_dir_path( __FILE__ ) . 'admin/esig-cf7-filters.php' );


/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
 
register_activation_hook( __FILE__, array( 'ESIG_CF7', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'ESIG_CF7', 'deactivate' ) );

/**
 * Disable Contact Form 7 JavaScript completely
 */

//add_filter( 'wpcf7_load_js', '__return_false' );

//if (is_admin()) {
         
	require_once( plugin_dir_path( __FILE__ ) . 'admin/esig-cf7-admin.php' );
        add_action( 'plugins_loaded', array( 'ESIG_CF7_Admin', 'get_instance' ) );
        add_action( 'plugins_loaded', array( 'esigCf7Filters', 'instance' ) );

    require_once( plugin_dir_path( __FILE__ ) . 'admin/includes/esig-cf7-document-view.php' );
    
    
