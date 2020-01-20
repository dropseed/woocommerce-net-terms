<?php

function net_terms_get_remote_info() {
	$remote = get_transient( 'net_terms_upgrade_info' );
	if ($remote && $_GET["net_terms_force_updater"] === NULL) return $remote;

	$installed_payment_methods = WC()->payment_gateways->payment_gateways();
	$gateway = $installed_payment_methods["net_terms"];
	$license_key = $gateway->license_key;

	$remote = wp_remote_get( 'https://us-central1-woocommerce-net-terms.cloudfunctions.net/updater', array(
		'timeout' => 10,
		'headers' => array(
			'Accept' => 'application/json',
			"Authorization" => "license-key " . $license_key,
		) )
	);

	if ( !is_wp_error( $remote ) && isset( $remote['response']['code'] ) && $remote['response']['code'] == 200 && !empty( $remote['body'] ) ) {
		set_transient( 'net_terms_upgrade_info', $remote, 43200 ); // 12 hours cache
		return $remote;
	}

	return false;
}

add_action( 'upgrader_process_complete', 'net_terms_after_update', 10, 2 );
function net_terms_after_update( $upgrader_object, $options ) {
	if ( $options['action'] == 'update' && $options['type'] === 'plugin' )  {
		delete_transient( 'net_terms_upgrade_info' );
	}
}

add_filter('plugins_api', 'net_terms_plugin_info', 20, 3);
function net_terms_plugin_info( $res, $action, $args ){
	if( $action !== 'plugin_information' ) return false;
	if( 'woocommerce-net-terms' !== $args->slug ) return $res;

	$remote = net_terms_get_remote_info();

	if( $remote ) return json_decode( $remote['body'], true );

	return false;

}

add_filter('site_transient_update_plugins', 'net_terms_push_update' );
function net_terms_push_update( $transient ){
	if ( empty($transient->checked ) ) return $transient;

	$remote = net_terms_get_remote_info();

	if( $remote ) {
		$data = json_decode( $remote['body'] );

		if( $data && version_compare(NET_TERMS_CURRENT_VERSION, $data->version, '<' ) ) {
			$data->new_version = $data->version;
			$data->package = $data->download_link;

			$transient->response[$data->plugin] = $data;
			$transient->checked[$data->plugin] = $data->version;
		}
	} else {
		add_action('admin_notices', 'net_terms_update_remote_failed');
	}

	return $transient;
}

function net_terms_update_remote_failed() {
	echo '<div class="error"><p><strong>Updating the Net Terms for WooCommerce plugin failed.</strong> Have you entered a license key? You can change your license key in WooCommerce > Settings > Payments > Net terms.</p></div>';
}
