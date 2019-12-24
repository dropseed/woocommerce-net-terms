<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Net_Terms_Gateway extends WC_Payment_Gateway {
	public function __construct() {
		$this->id                 = 'net_terms';
		$this->icon               = '';
		$this->has_fields         = false;
		$this->method_title       = 'Net terms';
		$this->method_description = 'Allow specific customers to pay offline with net terms.';

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->title        = $this->get_option( 'title' );
		$this->description  = $this->get_option( 'description' );
		$this->instructions = $this->get_option( 'instructions' );

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		add_filter( 'woocommerce_payment_complete_order_status', array( $this, 'change_payment_complete_order_status' ), 10, 3 );
		add_filter( 'woocommerce_order_is_paid', array( $this, 'order_is_paid' ), 10, 2 );

		// Customer Emails.
		add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'      => array(
				'title'   => 'Enable/Disable',
				'type'    => 'checkbox',
				'label'   => 'Enable net terms payments',
				'default' => 'no',
			),
			'title'        => array(
				'title'       => 'Title',
				'type'        => 'text',
				'description' => 'This controls the title which the user sees during checkout. Use {days} to insert the terms for the specific user.',
				'default'     => "Net {days}",
				'desc_tip'    => false,
			),
			'description'  => array(
				'title'       => 'Description',
				'type'        => 'textarea',
				'description' => 'Payment method description that the customer will see on your checkout. Use {days} to insert the terms for the specific user.',
				'default'     => 'Please pay by check within {days} days.',
				'desc_tip'    => false,
			),
			'instructions' => array(
				'title'       => 'Instructions',
				'type'        => 'textarea',
				'description' => 'Instructions that will be added to the thank you page and emails. Use {days} to insert the terms for the specific user.',
				'default'     => '',
				'desc_tip'    => false,
			),
		);
	}

	/**
	 * Output for the order received page.
	 */
	public function thankyou_page() {
		if ( $this->instructions ) {
			echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) );
		}
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @access public
	 * @param WC_Order $order Order object.
	 * @param bool     $sent_to_admin Sent to admin.
	 * @param bool     $plain_text Email format: plain text or HTML.
	 */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		if ( $this->instructions && ! $sent_to_admin && $this->id === $order->get_payment_method() && $order->has_status( 'processing' ) ) {
			echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) . PHP_EOL );
		}
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {

		$order = wc_get_order( $order_id );

		$days = net_terms_days_for_user();
		if ( $order->get_total() > 0 ) {
			$order->add_meta_data( '_net_terms', $days, true);  // add meta first so it's saved with order status update
			$order->update_status( 'processing', 'Payment due in ' . $days . ' days.' );
		} else {
			$order->payment_complete();
		}

		WC()->cart->empty_cart();

		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}

	// Prevent net terms orders being marked paid when "processing" or "completed"
	public function change_payment_complete_order_status( $status, $order_id = 0, $order = false ) {
		if ( $order && $this->id === $order->get_payment_method() && $order->get_total() > 0 ) {
			$status = 'net-terms-paid';  // does not exist
		}
		return $status;
	}

	// Net terms are only considered paid if there is a date, not "processing" or "completed"
	public function order_is_paid($is_paid, $order) {
		if ($this->id === $order->get_payment_method()) {
			return $order->get_date_paid() != null;
		}
		return $is_paid;
	}
}
