<?php

/* functions to return user addresses */

function pretty_pmpro_billing_address( $user_id ) {

    global $woocommerce;
    $user = get_userdata($user_id);
    $pmpro_baddr = $woocommerce->countries->get_formatted_address(
	array(
	    'first_name' => $user->pmpro_bfirstname,
	    'last_name' => $user->pmpro_blastname,
	    'company' => $user->pmpro_bcompany,
	    'address_1' => $user->pmpro_baddress1,
	    'address_2' => $user->pmpro_baddress2,
	    'city' => $user->pmpro_bcity,
	    'state' => $user->pmpro_bstate,
	    'postcode' => $user->pmpro_bzipcode,
	    'country' => $user->pmpro_bcountry)
    );

    return $pmpro_baddr;
}

function pretty_pmpro_shipping_address( $user_id ) {

    global $woocommerce;
    $user = get_userdata($user_id);
    $pmpro_saddr = $woocommerce->countries->get_formatted_address(
	array(
	    'first_name' => $user->pmpro_sfirstname,
	    'last_name' => $user->pmpro_slastname,
	    'company' => $user->pmpro_scompany,
	    'address_1' => $user->pmpro_saddress1,
	    'address_2' => $user->pmpro_saddress2,
	    'city' => $user->pmpro_scity,
	    'state' => $user->pmpro_sstate,
	    'postcode' => $user->pmpro_szipcode,
	    'country' => $user->pmpro_scountry)
    );

    return $pmpro_saddr;
}

function pretty_woo_billing_address( $user_id ) {

    global $woocommerce;
    $user = get_userdata($user_id);
    $woo_baddr = $woocommerce->countries->get_formatted_address(
	array(
	    'first_name' => $user->billing_first_name,
	    'last_name' => $user->billing_last_name,
	    'company' => $user->billing_company,
	    'address_1' => $user->billing_address_1,
	    'address_2' => $user->billing_address_2,
	    'city' => $user->billing_city,
	    'state' => $user->billing_state,
	    'postcode' => $user->billing_postcode,
	    'country' => $user->billing_country)
    );

    return $woo_baddr;
}

function pretty_woo_shipping_address( $user_id ) {

    global $woocommerce;
    $user = get_userdata($user_id);
    $woo_saddr = $woocommerce->countries->get_formatted_address(
	array(
	    'first_name' => $user->shipping_first_name,
	    'last_name' => $user->shipping_last_name,
	    'company' => $user->shipping_company,
	    'address_1' => $user->shipping_address_1,
	    'address_2' => $user->shipping_address_2,
	    'city' => $user->shipping_city,
	    'state' => $user->shipping_state,
	    'postcode' => $user->shipping_postcode,
	    'country' => $user->shipping_country)
    );
    
    return $woo_saddr;
}

function copy_bill_addr_pmpro_to_woo($user_id) {

    update_user_meta( $user_id, 'billing_first_name', $user->pmpro_bfirstname );
    update_user_meta( $user_id, 'billing_last_name', $user->pmpro_blastname);
    update_user_meta( $user_id, 'billing_company', $user->pmpro_bcompany );
    update_user_meta( $user_id, 'billing_address_1', $user->pmpro_baddress1 );
    update_user_meta( $user_id, 'billing_address_2', $user->pmpro_baddress2 );
    update_user_meta( $user_id, 'billing_city', $user->pmpro_bcity );
    update_user_meta( $user_id, 'billing_state', $user->pmpro_bstate );
    update_user_meta( $user_id, 'billing_postcode', $user->pmpro_bzipcode );
    update_user_meta( $user_id, 'billing_country', $user->pmpro_bcountry );

}

function copy_ship_addr_pmpro_to_woo($user_id) {
    update_user_meta( $user_id, 'shipping_first_name', $user->pmpro_sfirstname );
    update_user_meta( $user_id, 'shipping_last_name', $user->pmpro_slastname);
    update_user_meta( $user_id, 'shipping_company', $user->pmpro_scompany );
    update_user_meta( $user_id, 'shipping_address_1', $user->pmpro_saddress1 );
    update_user_meta( $user_id, 'shipping_address_2', $user->pmpro_saddress2 );
    update_user_meta( $user_id, 'shipping_city', $user->pmpro_scity );
    update_user_meta( $user_id, 'shipping_state', $user->pmpro_sstate );
    update_user_meta( $user_id, 'shipping_postcode', $user->pmpro_szipcode );
    update_user_meta( $user_id, 'shipping_country', $user->pmpro_scountry );
}
