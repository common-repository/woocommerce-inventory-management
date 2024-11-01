<?php
    class scanventory_license {

        var $options;
        var $lic_valid;
        var $lic_message;

        static $instance;

        public static function instance() {
            if ( ! isset( self::$instance )) {
                self::$instance = new scanventory_license();
            }
            return self::$instance;
        }

    	public function __construct() {
            $this->options = get_option('scanventory_options',array(
                'lkey'=>''
            ));
            $this->keyvalid();
    	}

		function valid() {
			return $this->lic_valid;
		}

		function last() {
			return substr($this->lkey(),-5);
		}

		function status() {
			return $this->lic_message;
		}

		function validate($license_key) {
			$api_params = array(
				'edd_action' => 'check_license',
				'license' => $license_key,
				'item_name' => urlencode( scanventory_control::SV_SL_ITEM_NAME )
			);
			$curl = add_query_arg( $api_params, scanventory_control::SV_SL_STORE_URL );
			$response = wp_remote_get( $curl , array( 'timeout' => 15, 'sslverify' => false ) );

			if ( is_wp_error( $response ) )
				return false;

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			switch ($license_data->license) {
				case 'valid' :
					$ret = true;
					$lmessage = 'Valid until '.$license_data->expires;
					$this->options["lkey"] = $license_key;
					update_option('scanventory_options',$this->options); //Save key to db
					break;
				case 'inactive' : //Lets Activate then
					$api_params = array(
						'edd_action' => 'activate_license',
						'license' => $_POST["lkey"],
						'item_name' => urlencode( scanventory_control::SV_SL_ITEM_NAME )
					);
					$aurl = add_query_arg( $api_params, scanventory_control::SV_SL_STORE_URL );
					$activate = wp_remote_get( $aurl , array( 'timeout' => 15, 'sslverify' => false ) );
					$body = wp_remote_retrieve_body($activate);
					if ( is_wp_error( $activate ) )
						return false;

					$ret = json_decode( $body );

					if ( is_object( $ret )) {
						if (isset($ret->license) and ($ret->license=="valid")) {
							$this->options["lkey"] = $license_key;
							update_option('scanventory_options',$this->options); //Save key to db
							$lmessage = "Valid";
							$ret = true;
							$message = '';
						} else {
							$message = "Unable to activate invalid key : {$license_key}";
							$lmessage = "Invalid license";
							$ret = false;
						}
					} else {
						$message = "Unable to activate key.";
						$lmessage = "Invalid license";
						$ret = false;
					}
					break;
				case 'expired' :
					$ret = false;
					$lmessage = $message = 'Expired license';
					break;
				default :
					$ret = false;
					$lmessage = $message = 'Invalid license';
					break;
			}
			$this->lic_valid = $ret;
	 		$this->lic_message = $lmessage;
		}

        function keyvalid($new_key=NULL) {
			$lkey = $this->lkey();
			$lic = get_transient("scanventory_keyvalid");
			if ($lic === FALSE) {
				if (! is_null($new_key)) {
					$this->validate($new_key);
				} elseif (strlen($lkey) > 1) {
					$this->validate($lkey);
				} else {
					$this->lic_valid = false;
					$this->lic_message = 'Demonstration mode. <A HREF="http://scanventory.net/features" TARGET="_BLANK"> Read More </A>';
				}
				set_transient("scanventory_keyvalid",array(
					$this->lic_valid,
					$this->lic_message
				), (60*60*24));
			} else {
				$this->lic_valid = $lic[0];
				$this->lic_message = $lic[1];
			}
       	}

		function lkey() {
			$lkey = (isset($this->options['lkey']) ? $this->options['lkey'] : '');
			return $lkey;
		}

        function export() {
            return array($this->options,$this->lic_valid,$this->lic_message);
        }
    }
?>