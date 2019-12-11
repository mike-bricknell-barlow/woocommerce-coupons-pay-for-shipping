<?php
/*
 * Plugin Name: Woo Coupons Pay for Shipping 
 * Version: 1.0.0
 * Plugin URI: http://wordpressant.com/
 * Description: Woocommerce by default does not allow coupons to pay for the shipping for their customers. For example, if cart subtotal is $200 and shipping cost is calculated to $10 and the buyer applies a coupon of total $210 of value. In woocommerce, only $200 coupon amount will count and the buyer still has to pay for his/her shipping. Few shop owners may like to allow coupons to pay for the buyers shipping cost too (if applicable). This plugin will allow shop owners to active that feature.
 * Author: Azharul Haque Lincoln
 * Author URI: http://wordpressant.com/
 * Requires at least: 4.0
 * Tested up to: 4.7
 *
 * Text Domain: woocommerce-coupons-pay-for-shipping-wordpressant
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Hugh Lashbrooke
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Checks if WooCommerce is active
 *
 * @since  1.0
 * @return bool true if WooCommerce is active, false otherwise
 */
function is_woocommerce_active_woo_coupon_pay_for_shipping()
{
	$active_plugins = (array)get_option('active_plugins', array());

	if (is_multisite()) {
		$active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
	}
	return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins);
}


// Check if WooCommerce is active and bail if it's not
if (!is_woocommerce_active_woo_coupon_pay_for_shipping()) {
	return;
}


// Load plugin class files
require_once( 'includes/class-wordpressant-coupons-pay-for-shipping-woocommerce.php' );

/**
 * Returns the main instance of Woocommerce_Coupons_Pay_for_Shipping_WordpressAnt to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Woocommerce_Coupons_Pay_for_Shipping_WordpressAnt
 */
function WordpressAnt_Coupons_Pay_for_Shipping_Woocommerce () {
	$instance = WordpressAnt_Coupons_Pay_for_Shipping_Woocommerce::instance( __FILE__, '1.0.0' );
	return $instance;
}

WordpressAnt_Coupons_Pay_for_Shipping_Woocommerce();
