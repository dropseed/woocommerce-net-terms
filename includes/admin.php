<?php

function net_terms_for_order($order) {
	return $order->get_meta("_net_terms", true);
}

function net_terms_order_status($order) {
	if (!$order->is_paid()) {
		$due = $order->get_date_created();
		$days = net_terms_for_order($order);
		$due->modify("+$days days");
		return "Due {$due->format('Y-m-d')}";
	}
	return "Paid";
}

// Replace the {days} in the order admin (above billing details, etc.)
add_filter( 'woocommerce_gateway_title', 'net_terms_order_gateway_title', 10, 2);
function net_terms_order_gateway_title($title, $id) {
	global $theorder;

	if ($theorder && $id === "net_terms") {
		$title = str_replace("{days}", net_terms_for_order($theorder), $title);
	}

	return $title;
}

// Add a "Net terms" field to the billing section of an admin order
add_filter( 'woocommerce_admin_billing_fields', 'net_terms_admin_billing_fields');
function net_terms_admin_billing_fields($fields) {
	global $theorder;

	if ($theorder && $theorder->get_payment_method() === "net_terms") {
		$fields["net_terms"] = array(
			"label" => "Net terms",
			"value" => net_terms_for_order($theorder) . " days, " . net_terms_order_status($theorder),
		);
	}

	return $fields;
}

// Add actions to the admin order dropdown
add_action( 'woocommerce_order_actions', 'net_terms_order_actions' );
function net_terms_order_actions( $actions ) {
	global $theorder;

	if ($theorder->get_payment_method() === "net_terms") {
		if ($theorder->is_paid()) {
			$actions['net_terms_mark_unpaid'] = "Mark as unpaid";
		} else {
			$actions['net_terms_mark_paid'] = "Mark as paid";
		}
	}

	return $actions;
}

// Action to mark an order as paid
add_action( 'woocommerce_order_action_net_terms_mark_paid', 'net_terms_mark_paid_action' );
function net_terms_mark_paid_action( $order ) {
	$message = sprintf( 'Marked as paid by %s.', wp_get_current_user()->display_name );
	$order->add_order_note( $message );

	$order->set_date_paid( current_time( 'timestamp', true ) );
	$order->save();
}

// Action to mark an order as unpaid
add_action( 'woocommerce_order_action_net_terms_mark_unpaid', 'net_terms_mark_unpaid_action' );
function net_terms_mark_unpaid_action( $order ) {
	$message = sprintf( 'Marked as unpaid by %s.', wp_get_current_user()->display_name );
	$order->add_order_note( $message );

	$order->set_date_paid(null);
	$order->save();
}

add_filter( 'manage_edit-shop_order_columns', 'net_terms_admin_columns' );
function net_terms_admin_columns( $columns ) {
	$columns['net_terms_status'] = 'Net terms';
	return $columns;
}

add_action( 'manage_shop_order_posts_custom_column', 'net_terms_admin_status_column' );
function net_terms_admin_status_column( $column ) {
	global $post;

	if ( 'net_terms_status' === $column ) {
		$order = wc_get_order( $post->ID );
		if ($order->get_payment_method() === "net_terms") {
			echo net_terms_order_status($order);
		}
	}
}

// add_filter( 'woocommerce_account_orders_columns', 'net_terms_account_orders_columns' );
// function net_terms_account_orders_columns($columns) {
// 	$columns["net-terms-status"] = "";
// 	return $columns;
// }

// add_action( 'woocommerce_my_account_my_orders_column_net-terms-status', 'net_terms_account_column' );
// function net_terms_account_column($order) {
// 	if ($order->get_payment_method() === "net_terms") {
// 		echo net_terms_order_status($order);
// 	}
// }

// add "paid" or "payment due" column to admin?
// - add count to badge?
// - add filter option? https://github.com/bekarice/woocommerce-filter-orders/blob/master/woocommerce-filter-orders-by-coupon.php
// or whole separate page for invoicing? (or both?)
// - put specific badge on it
// or is there a specific filter I can use first to just add "awaiting payment" or something?
// https://www.skyverge.com/blog/add-woocommerce-orders-list-column/
