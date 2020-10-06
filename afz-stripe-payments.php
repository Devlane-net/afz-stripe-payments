<?php
/**
* Plugin Name: Stripe Payments
* Description: Simple Stripe payments integration.
* Version: 1.1
* Author: Álvaro Franz
**/

defined('ABSPATH') || exit;

// Plugin Activation
function afz_stripe_payments_activation(){

    // Set default option values
    do_action( 'afz_stripe_payments_default_options' );
}
register_activation_hook( __FILE__, 'afz_stripe_payments_activation' );

// Set default values here
function afz_stripe_payments_default_options_values(){

    add_option('page_payment_charge', '/pay');

}
add_action( 'afz_stripe_payments_default_options', 'afz_stripe_payments_default_options_values' );

// Remove default values on deactivation
function afz_stripe_payments_deactivation() {
    delete_option('page_payment_charge');
}
register_deactivation_hook( __FILE__, 'afz_stripe_payments_deactivation' );

// Enqueue Stripe script
function afz_enqueue_stripe_scripts(){
wp_register_script( 'stripe', 'https://js.stripe.com/v3/', null, null, false );
wp_enqueue_script('stripe');
}
add_action( 'wp_enqueue_scripts', 'afz_enqueue_stripe_scripts' );

// Include CPT transactions
require_once plugin_dir_path(__FILE__).'/inc/custom-post-type-transactions.php';

// Include the main class
require_once plugin_dir_path(__FILE__).'inc/class-afz-stripe-payments.php';