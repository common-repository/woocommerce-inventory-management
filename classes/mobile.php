<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Manage Mobile functionality
 */

class Scanventory_Mobile {

	private $key;
	private $id;
	static $instance;

	public static function instance( $kv = NULL ) {
		if( !isset(self::$instance) ) {
			self::$instance = new Scanventory_Mobile($kv);
		}
		return self::$instance;
	}

	public function __construct( $kv = NULL ) {

	}

	/**
	 * Validate Request
	 */
	public function valid_request($path = NULL) {

		if( is_null($path) ) return false;

		if( strstr($path, "&") ) {
			$path = substr( $path, 0, strlen($path) - strlen(strstr($path, "&")) );
		}

		$cur = 0;
		$buffer = '';
		for( $i = 13; $i <= strlen($path) - 1; $i++ ) {
			if( $path[$i] == 'g' ) {
				$cur = $buffer;
				$buffer = '';
			} else {
				$buffer .= $path[$i];
			}
		}

		$vo = $buffer;
		$id = hexdec($cur);
		//$en = sha1( $id + get_option('scanventory_secret') );
		$en = sha1($id);

		if( $en === false || strlen($en) < 4 ) return false;

		$ch = substr( $en, (strlen($en) - 4), 4 );
		if( strstr($vo, $ch) ) {
			$this->id = $id;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check authentication
	 */
	private function checkAuth() {

		global $wpdb;
		if( isset($_REQUEST["logout"]) ) {
			wp_clear_auth_cookie();
			echo "<h2>You have been logged out</h2>";
			die();
		}

		if( (!current_user_can('edit_products')) or ( !is_user_logged_in() ) ) {
			if( $_SERVER["REQUEST_METHOD"] == "POST" ) {

				$suser = $wpdb->escape( $_POST["username"] );
				$spass = $wpdb->escape( $_POST["password"] );

				wp_clear_auth_cookie();
				$return = wp_authenticate($suser, $spass);

				if( is_wp_error($return) ) {

					$ec = $return->get_error_code();
					if( $ec == "empty_username" ) {
						$message = "Empty Username";
					} elseif( $ec == "invalid_username" ) {
						$message = "Incorrect Username";
					} elseif( $ec == "incorrect_password" ) {
						$message = "Incorrect Password";
					} elseif( $ec == "empty_username" ) {
						$message = "Account not found";
					} elseif( $ec == "empty_password" ){
						$message = "Empty Password";
					}

				} else {
					$user_id = $return->ID;
					wp_clear_auth_cookie();
					wp_set_auth_cookie($user_id, false);
					wp_set_current_user($user_id);
					header("Location: " . $_SERVER["REQUEST_URI"]);
					die();
				}
			}

			if( is_user_logged_in() ) {
				$message = "You do not have rights to edit products";
			}

			$action = "login";
			include_once( plugin_dir_path(__DIR__) . 'templates/mobile.php' );
			die();
		}
	}

	/**
	 * Manage stock inventory
	 */
	public function post( WC_Product $p ) {

		$message = '';
		if( $p === FALSE ) {
			echo 'Unable to locate product.';
			die();
		}

		$product_id = $p->get_id();

		if( isset($_POST["main_set"]) ) {
			wc_update_product_stock( $p, $_POST["main_stock"] );
			$message .= "Stock set to {$_POST["main_stock"]}\n<BR>";
		}

		if( isset($_POST["main_byone"]) ) {
			if( $_POST['main_byone'] == '-' ) {
				wc_update_product_stock( $p, 1, 'decrease' );
				$message = "Stock reduced by 1";
			} elseif( $_POST['main_byone'] == '+' ) {
				wc_update_product_stock( $p, 1, 'increase' );
				$message = "Stock increased by 1";
			}
		}

		// In crease or decrease stock
		if( isset($_POST['byone']) && is_array($_POST['byone']) ) {
			foreach( $_POST['byone'] as $aid => $action ) {

				if( function_exists('wc_get_product') ) {
					$ap = wc_get_product( $aid );
				} else {
					$ap = new WC_Product( $aid );
				}

				if( $action == '-' ) {
					$ap->reduce_stock();
					$message = "Stock #{$aid} reduced by 1";

				} elseif( $action == '+' ) {
					$ap->increase_stock();
					$message = "Stock #{$aid} increased by 1";
				}
			}
		}

		// Set the stock
		if( isset($_POST["set"]) and is_array($_POST["set"]) ) {
			foreach ($_POST["set"] as $vid => $set) {

				if( function_exists('wc_get_product') ) {
					$ap = wc_get_product( $vid );
				} else {
					$ap = new WC_Product( $vid );
				}

				wc_update_product_stock( $ap, $_POST["stock"][$vid] );
				$message .= "#{$vid} Stock set to {$_POST["stock"][$vid]}\n<br />";
			}
		}

		$command = isset( $_POST["cmd"] ) ? $_POST["cmd"] : '';
		if( $command == "Activate" ) {
			if( is_a($p, 'WC_Product_Variation') ) {
				update_post_meta( $this->id, '_manage_stock', true );
			} else {
				update_post_meta( $product_id, '_manage_stock', true );
			}
		} elseif( $command == "Deactivate" ) {
			if( is_a($p, 'WC_Product_Variation') ) {
				update_post_meta( $this->id, '_manage_stock', false );
			} else {
				update_post_meta( $product_id, '_manage_stock', false );
			}
		} elseif( $command == "instock" ) {
			update_post_meta( $product_id, '_stock_status', 'outofstock' );
		} elseif( $command == "outstock" ) {
			update_post_meta( $product_id, '_stock_status', 'instock' );
		}

		return $message;
	}

	/**
	 * Display labels
	 */
	public function display($id = NULL) {

		global $wpdb, $woocommerce;

		if( is_null($id) and isset($this->id) )	$id = $this->id;

		$message = "";
		$current = parse_url( $_SERVER['REQUEST_URI'] );
		$mobile_url = "/?" . $current["query"];

		$this->checkAuth();

		$action = 'view';
		if( isset($_GET['invoice']) && $_GET['invoice'] == '1' )  {
			$action = 'invoice';
		}

		if( function_exists('wc_get_product') ){
			$p = wc_get_product( $id );
		} else {
			$p = new WC_Product( $id );
		}

		if( $p === FALSE ) {
			echo 'Unable to locate product.';
			die();
		}

		if( $_SERVER["REQUEST_METHOD"] == "POST" ) {
			$message = $this->post($p);
			if( function_exists('wc_get_product') ) {
				$p = wc_get_product( $id );
			} else {
				$p = new WC_Product( $id );
			}
		}

        // Get Image
		/*$args = array(
			'post_type' => 'attachment',
			'numberposts' => -1,
			'post_parent' => $id,
			'orderby' => 'name',
			'order' => 'ASC'
		);*/

		/*$thumbIDs = array();
		$attachments = get_posts( $args );
		if( $attachments ) {
			foreach( $attachments as $attachment ) {
				$thumbIDs[$attachment->post_name] = $attachment->ID;
			}
		} else {
			$thumbIDs = array();
		}*/

		$product_id = $p->get_id();

		$thumbIDs = array();
		if( is_a($p, 'WC_Product_Variation') && has_post_thumbnail($this->id) ) {
			$thumbIDs[] = get_post_thumbnail_id( $this->id );
		} elseif( has_post_thumbnail($product_id) ) {
			$thumbIDs[] = get_post_thumbnail_id( $product_id );
		}

        //Load variations
        $variations = array();
		if( !is_a($p, 'WC_Product_Variation') && $p->get_type() == 'variable' ) {

        	$variations = array( $p->get_id() => $p );
			$res = $wpdb->get_results("SELECT ID from {$wpdb->posts} p WHERE post_parent = {$p->get_id()} and post_type = 'product_variation'");

			foreach( $res as $v ) {
				if( function_exists('wc_get_product') ) {
					$variations[$v->ID] = wc_get_product( $v->ID );
				} else {
					$variations[$v->ID] = new WC_Product( $v->ID );
				}
			}
		}

		if( count($thumbIDs) > 0 ) {
			$img_dat = wp_get_attachment_image_src( array_shift($thumbIDs) );
			$image = "<img id=\"pi-{$p->get_id()}\" src=\"" . $img_dat[0] . "\">";
		} else {
			$image = "<img id=\"pi-{$p->get_id()}\" src=\"http://www.placehold.it/120x120&text=No+Image\">";
		}

		$vstock = get_post_meta( $this->id, '_manage_stock', true );
		if( ! is_a($p, 'WC_Product_Variation') && $p->managing_stock() ) {
			$stock = $p->get_stock_quantity();
		} elseif( is_a($p, 'WC_Product_Variation') && $vstock == '1' ) {
			//$stock = get_post_meta( $this->id, '_stock', true );
			$stock = $p->get_stock_quantity();
		} else {
			$stock = false;
		}

		$name = $p->get_name();
		$sku = $p->get_sku();
		$instock = $p->is_in_stock() ? 'instock' : 'outofstock';
		$backorders = $p->get_backorders();
		$individually = $p->get_sold_individually();

        //Load variation data
		$vars = array();
		if( $stock !== FALSE && count($variations) > 0 ) {
			foreach( $variations as $variant ) {

				$var = array();
				$var["stock"] = $variant->get_stock_quantity();

				if( $variant->variation_has_sku ) {
					$var["label"] = 'SKU: ' . $variant->get_sku();
				} else {
					$var["label"] = 'Variation #' . $variant->variation_id;
				}

				$var["vid"] = $variant->variation_id;
				$var["manage_stock"] = get_post_meta( $variant->variation_id, '_manage_stock', true );
				$var["has_stock"] = $variant->variation_has_stock;

				if( !$var["vid"] ) {
					$var["label"] = 'All variants';
					continue;
				}

				if( $variant->get_variation_attributes() != "" ) {

					$terms = wc_get_attribute_taxonomies();
					foreach( $terms as $term ) {
						$termMap['attribute_pa_' . $term->attribute_name] = $term->attribute_label;
					}

					foreach( $variant->get_variation_attributes() as $attributeKey => $value ) {
						$attributes[] = array( $termMap[$attributeKey], $value );
					}

					$var["attributes"] = $attributes;
					unset($attributes);
				}

				$vars[] = $var;
			}
		}

		include_once( plugin_dir_path(__DIR__) . 'templates/mobile.php' );
		die();
	}
}