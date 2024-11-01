<?php
/**
 * Plugin Name: Scanventory Light
 * Plugin URI: http://scanventory.net
 * Description: [Light] Manage and administrate inventory with a suite of inventory control software.
 * Version: 1.1.3
 * Author: Scanventory.net
 * Author URI: http://scanventory.net
 *
 * @package Scanventory Light
 * @category Core
 * @author Scanventory.net
 *
 * Copyright 2018 Scanventory.net | All Rights Reserved
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
 
global $wp_version;

// Get PHP version
if( !defined('PHP_VERSION_ID') ) {
	$version = explode( '.', PHP_VERSION );
	define( 'PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]) );
}

// Kill if PHP version is less then 5.1.2
if( PHP_VERSION_ID < 50102 ) {
	die( 'Scanventory requires PHP Version 5.1.2 or greater.' );
}

// Check for wordpress version
if( version_compare($wp_version, "3.1", "<") ) {
	$exit_msg_ver = 'Sorry, this plugin is not supported on pre-3.1 WordPress installs.';
	exit( $exit_msg_ver );
}

/**
 * Basic plugin definitions
 * 
 * @package Scanventory Pro
 * @since 1.0.8
 */
if( !defined( 'WOOSCL_VERSION' ) ) {
	define( 'WOOSCL_VERSION', '1.0.0' ); // plugin version
}
if( !defined( 'WOOSCL_PLUGIN_DIR' ) ) {
	define( 'WOOSCL_PLUGIN_DIR', plugin_dir_path(__FILE__) ); // plugin dir path
}
if( !defined( 'WOOSCL_PLUGIN_URL' ) ) {
	define( 'WOOSCL_PLUGIN_URL', plugin_dir_url( __FILE__ ) ); // plugin url path
}

/**
 * Activation Hook
 * 
 * Register plugin activation hook.
 */
register_activation_hook( __FILE__, 'wooscl_plugin_install' );

/**
 * Plugin Setup (On Activation)
 * 
 * Does the initial setup,
 * sets default values for the plugin options.
 */
function wooscl_plugin_install() {
	if( is_plugin_active('woocommerce-scanventory/woocommerce-scanventory.php') )  {
		deactivate_plugins( plugin_basename(__FILE__) );
		wp_die( 'Scanventory Pro plugin is activated. please deactivate that plugin to activate this.' );
	}
}

// Misc functions file
require_once( WOOSCL_PLUGIN_DIR . '/functions.php' );

// Load control class
$key = '5';
$scanventory = Scanventory_Control::instance( $key );

// Check if admin
if( is_admin() ) {
    $scanventoryAdmin = Scanventory_Admin::instance( $key );

    add_action( 'admin_init', array($scanventoryAdmin, 'register') );
    add_action( 'admin_menu', array($scanventoryAdmin, 'menu') );
    add_action( 'load-edit.php', array($scanventoryAdmin, 'custom') );
}