<?php

/**
 *
 * Plugin Name:       Net Terms for WooCommerce
 * Plugin URI:        https://www.dropseed.io
 * Description:       A payment gateway for WooCommerce to allow and track order payment with Net terms.
 * Version:           0.1
 * Author:            Dropseed
 * Author URI:        https://www.dropseed.io
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (!defined( 'WPINC')) {
    die;
}

define('NET_TERMS_CURRENT_VERSION', '0.1');

function net_terms_woocommerce_missing_notice() {
	echo '<div class="error"><p><strong>' . sprintf( 'Net Terms for WooCommerce requires WooCommerce to be installed and active. You can download %s here.', '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
}

add_action( 'plugins_loaded', 'net_terms_woocommerce_init' );

function net_terms_woocommerce_init() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'net_terms_woocommerce_missing_notice' );
		return;
    }

    require_once dirname( __FILE__ ) . "/includes/users.php";
    require_once dirname( __FILE__ ) . "/includes/gateway.php";
    require_once dirname( __FILE__ ) . "/includes/admin.php";
    require_once dirname( __FILE__ ) . "/includes/checkout.php";
    require_once dirname( __FILE__ ) . "/includes/update.php";

    // Add to available gateway options
    add_filter( 'woocommerce_payment_gateways', 'net_terms_payment_gateways' );
    function net_terms_payment_gateways( $gateways ) {
        $gateways[] = 'Net_Terms_Gateway';
        return $gateways;
    }
}
