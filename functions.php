<?php
/**
 * Functions file
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Auto load classes,
 * register function
 */
spl_autoload_register( function ( $className ) {
	if( substr($className, 0, strlen("Scanventory")) === "Scanventory" ) {

		$classNameShort = str_replace( "\\", "/", substr($className, strlen("Scanventory\\")) );
		$file = WOOSCL_PLUGIN_DIR . 'classes/' . strtolower($classNameShort) . ".php";

		if( file_exists($file) ) {
			include_once $file;
		}
	}
});

/**
 * Add Plugin row action help links
 */
add_filter( 'plugin_action_links', 'wpscl_manage_plugin_row_action_urls', 10, 2 );
function wpscl_manage_plugin_row_action_urls( $actions, $file ) {
	if( strpos( $file, 'woocommerce-scanventory-light.php' ) !== false ) {
		$url = add_query_arg( ['post_type' => 'product', 'page' => 'scanventory' ], admin_url('edit.php') );
		$new_actions = array( 'settings' => '<a href="'.$url.'">'.esc_html( 'Settings' ).'</a>' );

		$actions = array_merge( $new_actions, $actions );
	}
	return $actions;
}