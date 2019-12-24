<?php

// Do string replacement when presenting the net terms as an available gateway
add_filter( 'woocommerce_available_payment_gateways', 'net_terms_available_payment_gateways');
function net_terms_available_payment_gateways( $_available_gateways ) {
	if (!isset($_available_gateways["net_terms"])) return $_available_gateways;

	if (net_terms_enabled_for_user()) {
		$days = net_terms_days_for_user();
		$_available_gateways["net_terms"]->title = str_replace("{days}", $days, $_available_gateways["net_terms"]->title);
		$_available_gateways["net_terms"]->description = str_replace("{days}", $days, $_available_gateways["net_terms"]->description);
		$_available_gateways["net_terms"]->instruction = str_replace("{days}", $days, $_available_gateways["net_terms"]->instruction);
	} else {
		// Remove the payment option for this user if they can't use it
		unset($_available_gateways["net_terms"]);
	}

	return $_available_gateways;
}
