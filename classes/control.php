<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

ob_start();
class Scanventory_Control {

    private static $instance;

    public static function instance( $kv = NULL ) {
        if( !isset(self::$instance) ) {
            self::$instance = new Scanventory_Control( $kv );
        }
        return self::$instance;
    }

	public function __construct( $kv = NULL ) {
		$this->init();
	}

	function init() {
		add_action( 'wp_loaded', array($this, 'check') );
	}

	/**
	 * Generate QR code
	 */
	public function qr( $txt ) {
		include_once( WOOSCL_PLUGIN_DIR . 'classes/qr.php');
		status_header(200);
		nocache_headers();
		header("Content-type: image/png");
		echo QRcode::png($txt);
		die();
	}

	/**
	 * Check URL on load
	 * start / init labels
	 */
	public function check() {
		
		global $wp;

		$current = parse_url( $_SERVER['REQUEST_URI'] );
		$current_query = isset( $current['query'] ) ? $current['query'] : '';

		if( strstr($current_query, 'scanventory-qr') ) {
			$this->qr( 'http://' . urldecode($_REQUEST["q"]) );
		}

		if( ($current_query == 'scanventory-labels') || (isset($_GET['sp']) && $_GET['sp'] > 0) ) {
			$this->labels();
		}

		if( $current_query == 'scanventory-report' ) {
			$this->report();
			die();
		}

		// Mobile
		if( strstr($current_query, 'scanventory-S') ) {
			//$mobile = Scanventory_Mobile::instance( $this->key );
			$mobile = Scanventory_Mobile::instance();
			if( $mobile->valid_request($current_query) ) {
				$mobile->display();
			} else {
				die( 'Invalid request.' );
			}
		}

		add_action( 'woocommerce_order_status_processing_to_cancelled', array($this, 'restore_order_stock' ), 10, 1);
		add_action( 'woocommerce_order_status_completed_to_cancelled', array($this, 'restore_order_stock' ), 10, 1);
		add_action( 'woocommerce_order_status_on-hold_to_cancelled', array($this, 'restore_order_stock' ), 10, 1);
		add_action( 'woocommerce_order_status_processing_to_refunded', array($this, 'restore_order_stock' ), 10, 1);
		add_action( 'woocommerce_order_status_completed_to_refunded', array($this, 'restore_order_stock' ), 10, 1);
		add_action( 'woocommerce_order_status_on-hold_to_refunded', array($this, 'restore_order_stock' ), 10, 1);
	}

	/**
	 * Manage store restore
	 */
	public function restore_order_stock( $order_id ) {

		$restoreStock = get_option( 'scanventory_restoreStock' );

		$order = new WC_Order($order_id);
		if( (!get_option('woocommerce_manage_stock') == 'yes') or ( !sizeof($order->get_items()) > 0 ) or ( $restoreStock === FALSE ) ) {
			return;
		}

		foreach( $order->get_items() as $item ) {
			if( $item['product_id'] > 0 ) {

				$_product = $order->get_product_from_item( $item );
				if( $_product && $_product->exists() && $_product->managing_stock() ) {

					$old_stock = $_product->get_stock_quantity();
					$qty = apply_filters( 'woocommerce_order_item_quantity', $item['qty'], $this, $item );
					$new_quantity = wc_update_product_stock( $_product, $qty, 'increase' );
					
					$order->add_order_note( sprintf(__('Item #%s stock incremented from %s to %s.', 'woocommerce'), $item['product_id'], $old_stock, $new_quantity) );
					
					$order->send_stock_notifications( $_product, $new_quantity, $item['qty'] );
				}
			}
		}
	}

	/**
	 * Report Page
	 */
	public function report() {

		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}

		status_header( 200 );
		nocache_headers();

		include_once( WOOSCL_PLUGIN_DIR . 'templates/report.php' );
	}

	/**
	 * Short the string
	 */
	public function shorten( $in = '', $length = 15 ) {
		if( strlen($in) > $length ) {
			$out = substr( $in, 0, ($length - 3) ) . "...";
			return $out;
		} else {
			return $in;
		}
	}

	/**
	 * Generate Labels
	 */
	public function labels() {

		global $wpdb, $woocommerce;

		if( !current_user_can('manage_options') ) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}

		status_header( 200 );
		nocache_headers();

		if( !isset($_POST['variationed']) ) {
			$csql = "";
		}

		$start = 0;
		$limit = 10;
		$id = 1;
		if( isset($_GET['sp']) ) {
			$id = $_GET['sp'];
			$start = ($id - 1) * $limit;
		}

		$csql = (isset($_POST['variationed']) ? "" : " AND p.post_parent='0'");
		$query = "SELECT DISTINCT p.id , date_format(p.post_date,'%Y') as sort_year, date_format(p.post_date,'%m') as sort_month FROM {$wpdb->posts} p,	{$wpdb->term_relationships} tr,	{$wpdb->term_taxonomy} tt WHERE	p.post_type = 'product' {$csql}";

		//$rows_count = "SELECT DISTINCT p.id , date_format(p.post_date,'%Y') as sort_year, date_format(p.post_date,'%m') as sort_month FROM {$wpdb->posts} p,{$wpdb->term_relationships} tr,	{$wpdb->term_taxonomy} tt WHERE	p.post_type = 'product' {$csql}";

		$results = $wpdb->get_results($query);
		/*$pagi_total = count($wpdb->get_results($rows_count));
		if( !empty($pagi_total) ) {
			$total = ceil($pagi_total / $limit);
		}*/

		$labels = array();
		$counter = 0;
		foreach( $results as $pr ) {

			$product_id = $pr->id;

			$buffer = array();
			$secret = get_option( 'scanventory_secret' );

			//$en = sha1($pr->id + get_option('scanventory_secret'));
			$en = sha1( $product_id );
			$ch = ( substr($en, (strlen($en) - 4), 4) );
			$buffer['code'] = dechex( $product_id ) . "g" . $ch;

			if( function_exists('wc_get_product') ) {
				$p = wc_get_product( $product_id );
			} else {
				$p = new WC_Product( $product_id );
			}

			//$product_id = $p->id;
			if( $_REQUEST['variable'] == 'price' ) {
				$buffer['variable'] = "<div class='var-item'><label>Price: </label>  " . $p->get_price_html() . "</div><br />\n";
			}

			if( $p === FALSE ) {
				echo 'Unable to locate product.';
				die();
			}

			$buffer['sku'] = $p->get_sku();
			$buffer['price'] = $p->get_price();
			$buffer['name'] = $this->shorten( $p->get_name(), 25 );
			$buffer['image'] = get_the_post_thumbnail( $product_id, array(200, 200) );
			$labels[$counter++] = $buffer;
			$short[$counter] = $buffer['code'];
		}

        //Fetch shortURLs in batch
		foreach( $short as $k => $code ) {
			$url = get_bloginfo('url');
			//$qr_url = preg_replace("~^(?:f|ht)tps?://~i", '', $url);
			$qr_url = $url;
			$shortcodes[$code] = $qr_url . '/?scanventory-S' . $code;
		}

		$conv['s'] = get_bloginfo('url');

        //Include stylesheet
		echo "<html><head>";
		$thermal = false;

        //Find items per page :
		$mark = $_REQUEST["label"];
		$markp = explode("-", $mark);
		array_pop($markp);
		$perPage = array_pop( $markp );
		
		if( strstr($_REQUEST['label'], 'thermal') ) $thermal = true;

		if( !empty($_REQUEST['label']) ) {
			echo "<link rel='stylesheet' href='" . plugin_dir_url(WOOSCL_PLUGIN_DIR . "assets/css/label/") . 'label/' . $_REQUEST["label"] . ".css' type='text/css' media='all' />";
		}

		//echo "<link rel='stylesheet' href='" . plugin_dir_url(WOOSCL_PLUGIN_DIR . "assets/css/label/") . 'label/' . $_REQUEST["label"] . ".css' type='text/css' media='all' />";

		echo "<meta charset='utf-8'></head><body>"; ?>

			<script>
				function printContent(el) {
					var restorepage = document.body.innerHTML;
					var printcontent = document.getElementById(el).innerHTML;
					document.body.innerHTML = printcontent;
					window.print();
					document.body.innerHTML = restorepage;
				}
			</script>

			<style>
			.print_lable_button,.print_lable_button_bottom{background:url(<?php echo WOOSCL_PLUGIN_URL; ?>images/print.png) 24% center/22px auto no-repeat #9d5e91;color:#fff;font-family:arial;font-size:18px;padding:10px 60px 10px 80px}.print_button{margin-top:45px;text-align:center;clear:both;}.print_lable_button_bottom{border:none;border-radius:4px;margin-bottom:45px}.print_lable_button{border:none;border-radius:4px}.sc_pagi{margin-top:20px}.sc_pagi li{float:left;width:20px}.sc_pagi li.current{font-weight:700}.sc_pagi ul{list-style-type:none;padding-left:0}
			</style>

			<div class="print_button">
				<button onclick='printContent("div1")' class="print_lable_button">Print Labels</button>
			</div>

			<?php
			$count = 1;
			echo "<div class='label-wrap' id='div1'>";

			foreach( $labels as $label ) {

				$variable = isset($label['variable']) ? $label['variable'] : 'none';
				$short = urlencode($shortcodes[$label['code']]);

				echo "<div class='label'><div class='qrcode'><div class='image'>";
				if( !empty($label['image']) ) {
					echo $label['image'];
				}
				echo "</div>";

				if ($short == 'over_limit') {
					echo "<img class='qrcode' src=\"http://placehold.it/89/ffffff&text=Over+Limit\">";
				} else {
					$code = 'http://chart.googleapis.com/chart?cht=qr&chs=150x150&choe=UTF-8&chld=H&chl=' . $short . '';
					echo "<img class='qrcode' src=" . $code . ">";
				}

				echo "</div>";
				echo "<div class ='scan_in'>";
				echo "<div class='name'>";
				echo $label['name'];
				echo "</div>";
				echo "<div class='sku'>";
				echo 'SKU : &nbsp;' . $label['sku'];
				echo "</div>";
				echo "<div class='pro_attr'>";
				echo '<p>Product Attributes</p>';
				echo "</div>";

				if( $_REQUEST['variable'] !== 'none' ) {
					echo "<div class='variable'>";
					echo $variable;
					echo "</div>";
				}
				echo "</div>";
				echo "</div>\n\n";

				if( $count++ == $perPage ) {
					echo "<div class='page-break'></div>\n\n";
					$count = 1;
				}
			}
			//echo '<div class="sc_pagi">';
			/*if( $id > 1 ) {
				echo "<a href='?scanventory-labels&label=" . $_REQUEST["label"] . "&sp=" . ($id - 1) . "&variable=" . $_REQUEST["variable"] . "&vlabel=" . $vlabel . "&per_page=".$perPage."' class='button'>PREVIOUS</a>";
			}*/

			/*if( $id != $total ) {
				echo "<a href='?scanventory-labels&label=" . $_REQUEST["label"] . "&sp=" . ($id + 1) . "&variable=" . $_REQUEST["variable"] . "&vlabel=" . $vlabel . "&per_page=".$perPage."' class='button'>NEXT</a>";
			}*/

			/*echo "<ul class='page'>";
			for( $i = 1; $i <= $total; $i++ ) {
				if ($i == $id) {
					echo "<li class='current'>" . $i . "</li>";
				} else {
					echo "<li><a href='?scanventory-labels&label=" . $_REQUEST["label"] . "&sp=" . $i . "&variable=" . $_REQUEST["variable"] . "&vlabel=" . $vlabel . "&per_page=".$perPage."'>" . $i . "</a></li>";
				}
			}*/

		// echo "</ul></div>";
		echo "</div>"; //label-wrap ?>

		<div class="print_button">
			<button onclick='printContent("div1")' class="print_lable_button_bottom">Print Labels</button>
		</div>

		<?php
		echo "</body></html>";
		die();
	}

}