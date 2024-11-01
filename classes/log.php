<?
class scanventory_log {

	CONST SV_SL_LOGSIZE = 250;

	private static $instance;
	private $log;

	public static function instance( $event=NULL ) {
		if ( ! isset( self::$instance )) {
			self::$instance = new scanventory_log( $event );
		}
		return self::$instance;
	}

	public function __construct( $event=NULL ) {
		$this->loadLog();
		if (! is_null($event)) {
			$this->log($event);
		}
	}

	function log($event) {
		$tn = time();
		$this->log[] = array( $tn, get_current_user_id() , $event );
		if (count($this->log) > static::SV_SL_LOGSIZE) {
			$g = array_slice($this->log, (count($this->log) - static::SV_SL_LOGSIZE) , static::SV_SL_LOGSIZE, true);
		}
		$this->saveLog();
	}

	protected function loadLog() {
		$this->log = get_option('scanventory_log');
	}

	protected function saveLog() {
		update_option('scanventory_log',$this->log);
	}
}
?>