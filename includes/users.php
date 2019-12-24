<?php

function net_terms_can_edit() {
    return current_user_can( 'edit_users' );
}

// Add the net terms enabled field to each user
function net_terms_enabled_for_user($user_id = null) {
	if (!$user_id) $user_id = get_current_user_id();
	return get_user_meta($user_id, 'net_terms_enabled', true);
}


function net_terms_days_for_user($user_id = null) {
	if (!$user_id) $user_id = get_current_user_id();
	$net_terms_days = get_user_meta($user_id, 'net_terms_days', true);
	if (empty($net_terms_days)) $net_terms_days = 0;
	return $net_terms_days;
}

add_action( 'show_user_profile', 'net_terms_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'net_terms_show_extra_profile_fields' );

function net_terms_show_extra_profile_fields( $user ) {

    if (!net_terms_can_edit()) return;

	$net_terms_enabled = net_terms_enabled_for_user($user->ID);
    $net_terms_days = net_terms_days_for_user($user->ID);
	?>
	<h3>Net Terms</h3>

	<table class="form-table">
		<tr>
			<th><label for="net_terms_enabled">Enabled</label></th>
			<td>
				<input type="checkbox"
			       id="net_terms_enabled"
			       name="net_terms_enabled"
                   <?php if ($net_terms_enabled) echo 'checked="checked"'; ?>
				/>
			</td>
		</tr>
		<tr>
			<th><label for="net_terms_days">Days</label></th>
			<td>
				<input type="number"
			       min="0"
			       max="999"
			       step="1"
			       id="net_terms_days"
			       name="net_terms_days"
			       value="<?php echo esc_attr( $net_terms_days ); ?>"
				/>
			</td>
		</tr>
	</table>
	<?php
}

add_action( 'user_profile_update_errors', 'net_terms_user_profile_update_errors', 10, 3 );
function net_terms_user_profile_update_errors( $errors, $update, $user ) {
	if ( ! $update ) {
		return;
	}

	if ( $_POST['net_terms_days'] === "" || intval( $_POST['net_terms_days'] ) < 0 ) {
		$errors->add( 'net_terms_days_error', '<strong>ERROR</strong>: Net terms must be 0 or more days.' );
	}
}


add_action( 'personal_options_update', 'net_terms_update_profile_fields' );
add_action( 'edit_user_profile_update', 'net_terms_update_profile_fields' );

function net_terms_update_profile_fields( $user_id ) {
    if (!net_terms_can_edit()) return false;

	$net_terms_enabled = $_POST['net_terms_enabled'] === "on";
	var_dump($net_terms_enabled);
    update_user_meta( $user_id, 'net_terms_enabled', $net_terms_enabled );

	if ( $_POST['net_terms_days'] !== "" && intval( $_POST['net_terms_days'] ) >= 0 ) {
		update_user_meta( $user_id, 'net_terms_days', intval( $_POST['net_terms_days'] ) );
	}
}
