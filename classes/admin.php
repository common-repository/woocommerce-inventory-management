<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Admin class,
 * handle admin side functionality
 */
class Scanventory_Admin {

	private $options;
	private $key;
	private static $instance;

	public function __construct( $kv = NULL ) {
		add_action( 'admin_init', array($this, 'save_plugin_settings') );

		$this->options = get_option( 'scanventory_options', array() );
	}

	public static function instance( $kv = NULL ) {
		if( !isset(self::$instance) ) {
			self::$instance = new Scanventory_Admin( $kv );
		}
		return self::$instance;
	}

	/**
	 * Label Definitions
	 */
	public function labelfac() {
		return array(
			'2-sheet' => '8 1/2" x 5 1/2", 2 per Sheet ( Avery ®: 5126 / 5526 / 8126 )',
		);
	}

	/**
	 * Generate Log
	 */
	static function genlog() {

		$g = get_option( 'scanventory_log' );
		$log = array();

		if( $g == false ) {
			
			$tn = time();
			$log = array( array( $tn, get_current_user_id(), 'Scanventory installed.' ) );

			update_option( 'scanventory_log', $log );
			$g = $log;
		}
		return $log;
	}

	/**
	 * Log
	 */
	static function log( $event ) {

		$g = self::genlog();
		$tn = time();
		$g[] = array( $tn, get_current_user_id(), $event );

		if( count($g) > 250 ) {
			array_pop( $g );
		}
		update_option( 'scanventory_log', $g );
	}

	/**
	 * Generate Secret
	 */
	static function gensecret() {

		$g = get_option( 'scanventory_secret' );

		if( $g === false ) {
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$randstring = '';

			for( $i = 0; $i < 10; $i++ ) {
				$randstring .= substr( $characters, rand(0, strlen($characters)), 1 );
			}

			update_option( 'scanventory_secret', $randstring );
			$g = $randstring;
		}
		return $g;
	}

	/**
	 * Plugin scanventory / settings page
	 * Sub page of Woo Product Page
	 */
	public function main() {

		global $scanventory;

		if( !current_user_can('manage_options') ) {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}

		if( isset($_REQUEST["labels"]) ) {
			$this->allcodes();
			die();
		}

		$tabActive = ( isset($_GET["tab"]) ? $_GET["tab"] : 'labels'); ?>

		<div class="wrap">
			<style>.scan_logo_img{max-width:275px;}</style>
			<img src="<?php echo WOOSCL_PLUGIN_URL; ?>/images/scanventory-logo-no-slogan.png" class="scan_logo_img" />
			<h2 class="screen-reader-text">Scanventory</h2>

			<?php settings_errors( 'scanventory_settings' ); ?>

			<h2 class="nav-tab-wrapper">
				<a href="?post_type=product&page=scanventory&tab=labels" class="nav-tab<?php echo ($tabActive == "labels" ? ' nav-tab-active' : '' ) ?>">Labels</a>
				<a href="?post_type=product&page=scanventory&tab=main" class="nav-tab<?php echo ($tabActive == "main" ? ' nav-tab-active' : '' ) ?>">Main</a>
				<a href="?post_type=product&page=scanventory&tab=log" class="nav-tab<?php echo ($tabActive == "log" ? ' nav-tab-active' : '' ) ?>">Log</a>
				<a href="?post_type=product&page=scanventory&tab=report" class="nav-tab<?php echo ($tabActive == "report" ? ' nav-tab-active' : '' ) ?>">Reports</a>
			</h2>

			<?php
			if( $tabActive == "labels" ) {
				require_once( WOOSCL_PLUGIN_DIR . 'classes/admin-tabs/labels.php' );
			} elseif( $tabActive == "main" ) {
				require_once( WOOSCL_PLUGIN_DIR . 'classes/admin-tabs/main.php' );
			} elseif( $tabActive == "log" ) {
				require_once( WOOSCL_PLUGIN_DIR . 'classes/admin-tabs/log.php' );
			} elseif( $tabActive == "report" ) {
				// this is in tempalte directory because it also called at frontend by ?scanventory-report query
				require_once( WOOSCL_PLUGIN_DIR . 'templates/report.php' );
			} ?>
		</div>
	<?php
	}

	/**
	 * Register settings
	 */
	public function register() {

		self::gensecret();
		self::genlog();
		register_setting( 'scanventory_options', 'scanventory_secret' );
	}

	/**
	 * Get products all variables / attributes
	 */
	public function variables() {

		global $woocommerce;

		$vars = array(
			"none" => array(
				'type' => 'none',
				'name' => 'None'
			),
			"price" => array(
				'type' => 'static',
				'name' => 'Price'
			)
		);

		return $vars;
	}

	/**
	 * Manahe admin side menu
	 */
	public function menu() {
		$scanpage = add_submenu_page('edit.php?post_type=product', 'Scanventory', 'Scanventory', 'manage_woocommerce', 'scanventory', array($this, 'main'));
	}

	/**
	 * Redirect to the lables pages when buil labels,
	 * click from product listing page.
	 */
	public function custom() {
		if( (isset($_REQUEST["post_type"]) && $_REQUEST["post_type"] == 'product') && 
			(isset($_REQUEST["action"]) && $_REQUEST["action"] == 'label') ) {

			$target = urlencode( join(",", $_REQUEST["post"]) );
			header( "Location: ?post_type=product&page=scanventory&target={$target}&tab=labels" );
			die();
		}
	}

	public function save_plugin_settings() {

		$nounce = isset( $_POST['scanventory_settings'] ) ? $_POST['scanventory_settings'] : '';
		if( ! wp_verify_nonce($nounce, 'scanventory_plugin_settings') ) {
			return;
		}
		
		$settings = get_option( 'scanventory_options', array() );
		if( isset($_POST['restorestock']) ) {
			$settings['restorestock'] = $_POST['restorestock'];
		}

		$this->options = $settings;

		// update options
		update_option( 'scanventory_options', $settings );

		add_settings_error(
			'scanventory_settings',
			esc_attr( 'settings_saved' ),
			esc_attr( 'Settings has been saved successfully!' ),
			'updated'
		);
	}
}