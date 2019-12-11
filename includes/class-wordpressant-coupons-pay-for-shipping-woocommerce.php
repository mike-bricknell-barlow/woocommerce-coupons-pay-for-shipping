<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WordpressAnt_Coupons_Pay_for_Shipping_Woocommerce {

	/**
	 * The single instance of Woocommerce_Coupons_Pay_for_Shipping_WordpressAnt.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;


	public function __construct( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'woocommerce_coupons_pay_for_shipping_wordpressant';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Handle localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );

		//add_action('woocommerce_calculate_totals', array($this, 'woocommerce_coupons_pay_for_shipping') );
		add_action('woocommerce_after_calculate_totals', array($this, 'woocommerce_coupons_pay_for_shipping') );

	} // End __construct ()


	/**
	 *
	 * Main function which will allow coupons to pay for shipping
	 * @param $cart
	 */
	function woocommerce_coupons_pay_for_shipping($cart){
		$discount_cart = $cart->discount_cart;
		$total_coupons_amount = $this->get_total_coupons_amount($cart->get_coupons());
		$copouns_credit_remain = $total_coupons_amount - $discount_cart;
		$shipping_including_tax = $cart->shipping_total + $cart->shipping_tax_total;

		if($copouns_credit_remain > 0 && !empty($cart->shipping_total) ){

			if($copouns_credit_remain >= $shipping_including_tax){

				$cart->discount_cart += $shipping_including_tax;
				$this->add_extra_charge_on_coupons($shipping_including_tax);

				/** reset cart total **/
				$cart->total -= $shipping_including_tax;
			}
			elseif($copouns_credit_remain < $shipping_including_tax ){

				$cart->discount_cart += $copouns_credit_remain;
				$this->add_extra_charge_on_coupons($copouns_credit_remain);

				/** reset cart total **/
				$cart->total -= $copouns_credit_remain;
			}

		}

		//$this->debug_to_console($cart);

	}


	/**
	 *
	 * Distribute extra coupon amount
	 * @param $extra_amount
	 */
	function add_extra_charge_on_coupons($extra_amount){
		$coupons = WC()->cart->get_coupons();
		foreach($coupons as $coupon){
			$cart_discount_added_by_this_coupon = isset(WC()->cart->coupon_discount_amounts[$coupon->get_code()]) ? round( WC()->cart->coupon_discount_amounts[$coupon->get_code()] ) : 0;
			if( $cart_discount_added_by_this_coupon < $coupon->get_amount() ){
				$unused_coupon_amount = $coupon->get_amount() - $cart_discount_added_by_this_coupon;
				if($extra_amount <= $unused_coupon_amount){
					WC()->cart->coupon_discount_amounts[$coupon->get_code()] = (isset(WC()->cart->coupon_discount_amounts[$coupon->get_code()]) ? WC()->cart->coupon_discount_amounts[$coupon->get_code()] : 0 ) + $extra_amount;
					$extra_amount = 0;
				}elseif($extra_amount > $unused_coupon_amount){
					$extra_amount = $extra_amount - $unused_coupon_amount;
					WC()->cart->coupon_discount_amounts[$coupon->get_code()] += $unused_coupon_amount;
				}
			}
			if($extra_amount == 0){
				break;
			}
		}
	}

	/**
	 *
	 * Return sumetion of all coupons applied in a cart
	 * @param $coupons
	 * @return int
	 */
    function get_total_coupons_amount($coupons){
        $total_coupons_amount = 0;
           foreach($coupons as $coupon){
               $total_coupons_amount += $coupon->get_amount();
           }
        return $total_coupons_amount;
    }


    /**
     *
     * Print php variable in console. Used for test purpose.
     * @param $obj
                                                                                                                                                                                                                                                                                                                                                      */
    function debug_to_console($obj)
    {
        $jsonprd = json_encode($obj);
        print_r('<script>console.log('.$jsonprd.')</script>');
    }


	/**
	 * Making shipping zero including tax
	 * @param $cart
	 */
	function make_shipping_zero($cart){
		$cart->shipping_tax_total = 0;
		$cart->shipping_total = 0;
	}

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation() {
		load_plugin_textdomain( 'woocommerce-coupons-pay-for-shipping-wordpressant', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()


	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain() {
	    $domain = 'woocommerce-coupons-pay-for-shipping-wordpressant';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()


	/**
	 * Main Woocommerce_Coupons_Pay_for_Shipping_WordpressAnt Instance
	 *
	 * Ensures only one instance of Woocommerce_Coupons_Pay_for_Shipping_WordpressAnt is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see WordpressAnt_Coupons_Pay_for_Shipping_Woocommerce()
	 * @return Main Woocommerce_Coupons_Pay_for_Shipping_WordpressAnt instance
	 */
	public static function instance( $file = '', $version = '1.0.0' ) {
 		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install() {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number() {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

}
