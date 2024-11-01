<?php
/**
 * Main Mobile template
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit; ?>

<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=480" />
	<link rel="stylesheet" type="text/css" href="<?php echo plugins_url('assets/css/bootstrap.css',__DIR__) ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo plugins_url('assets/css/mobile.css',__DIR__) ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo plugins_url('assets/css/bootstrap-theme.css',__DIR__) ?>" />
	<link href="<?php echo plugins_url('assets/css/fontawesome.min.css',__DIR__) ?>" rel="stylesheet" />
</head>
<body>
	<?php
	//$mobile_url=$_SERVER['REQUEST_URI'];
	$base_url = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https' : 'http' ) . '://' .  $_SERVER['HTTP_HOST'];
	$mainURL = $base_url . $_SERVER["REQUEST_URI"];

	if( $action == 'login' ) {
		require_once( WOOSCL_PLUGIN_DIR . 'templates/mobile/login.php' );
	} elseif( $action =='view' ) {
		require_once( WOOSCL_PLUGIN_DIR . 'templates/mobile/view.php' );
	} elseif( $action =='invoice' ) {
		require_once( WOOSCL_PLUGIN_DIR . 'templates/mobile/invoice.php' );
	} ?>
</body>
</html>