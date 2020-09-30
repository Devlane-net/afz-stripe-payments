<?php

// POST TYPE
function afz_transactions_cpt(){
register_post_type( 'transactions',
    array(
    'labels'        => array(
                      'name'           => 'Transactions',
                      'singular_name'  => 'Transaction'
                      ),
    'supports'      => array(
                      'title',
                      'editor'
                      ),
    'public'        => true,
    'has_archive'   => false,
    'rewrite'       => array('slug' => 'transaction'),
    'menu_position' => 4,
    'menu_icon'     => 'dashicons-chart-area'
    )
);
}
add_action( 'init', 'afz_transactions_cpt' );





// META BOXES
function afz_add_transactions_metaboxes(){

    add_meta_box(
        'afz_metabox_transaction_amount',       // Id
        'Transaction amount',                   // Title
        'afz_metabox_transaction_amount',       // Callback
        'transactions',                         // CPT
        'side',                                 // Where
        'default'                               // Load priority
    );

    add_meta_box(
        'afz_metabox_transaction_email',       // Id
        'Transaction email',                   // Title
        'afz_metabox_transaction_email',       // Callback
        'transactions',                        // CPT
        'side',                                // Where
        'default'                              // Load priority
    );

    add_meta_box(
        'afz_metabox_transaction_name',       // Id
        'Transaction name',                   // Title
        'afz_metabox_transaction_name',       // Callback
        'transactions',                       // CPT
        'side',                               // Where
        'default'                             // Load priority
    );

}
add_action( 'add_meta_boxes', 'afz_add_transactions_metaboxes' );


// Render amount metabox
function afz_metabox_transaction_amount(){
    global $post;
    
    // Nonce field to validate form request came from current site
    wp_nonce_field( basename( __FILE__ ), 'field_transaction_amount' );
    
    // Get the data if it's already been entered
    $transaction_amount = get_post_meta( $post->ID, 'transaction_amount', true );
    if(!$transaction_amount){ $transaction_amount = ''; }

    // Input
    echo '<input name="transaction_amount" type="number" step="any" value="'. esc_attr($transaction_amount) .'">';
}

// Render email metabox
function afz_metabox_transaction_email(){
    global $post;
    
    // Nonce field to validate form request came from current site
    wp_nonce_field( basename( __FILE__ ), 'field_transaction_email' );
    
    // Get the data if it's already been entered
    $transaction_email = get_post_meta( $post->ID, 'transaction_email', true );
    if(!$transaction_email){ $transaction_email = ''; }

    // Input
    echo '<input name="transaction_email" type="email" value="'. esc_attr($transaction_email) .'">';
}

// Render name metabox
function afz_metabox_transaction_name(){
    global $post;
    
    // Nonce field to validate form request came from current site
    wp_nonce_field( basename( __FILE__ ), 'field_transaction_name' );
    
    // Get the data if it's already been entered
    $transaction_name = get_post_meta( $post->ID, 'transaction_name', true );
    if(!$transaction_name){ $transaction_name = ''; }

    // Input
    echo '<input name="transaction_name" type="text" value="'. esc_attr($transaction_name) .'">';
}


// Save metabox data
function afz_save_transactions_meta( $post_id, $post ){

    // Bail if we're doing an auto save
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
        return $post_id;
    }
    
	// Return if the user doesn't have edit permissions.
	if( !current_user_can( 'edit_post', $post_id ) ){
		return $post_id;
	}
    
    // Save metabox transaction_amount value
    if( isset( $_POST['transaction_amount'] ) and wp_verify_nonce( $_POST['field_transaction_amount'], basename(__FILE__) ) ){
        
        $transaction_amount = floatval( $_POST['transaction_amount'] );
        
        if( get_post_meta( $post_id, 'transaction_amount', false ) ){
            // If the custom field already has a value, update it.
            update_post_meta( $post_id, 'transaction_amount', $transaction_amount );
        }else{
            // If the custom field doesn't have a value, add it.
            add_post_meta( $post_id, 'transaction_amount', $transaction_amount);
        }
        if( strlen($transaction_amount) == 0 ) {
            // Delete the meta key if there's no value
            delete_post_meta( $post_id, 'transaction_amount' );
        }

    }

    // Save metabox transaction_email value
    if( isset( $_POST['transaction_email'] ) and wp_verify_nonce( $_POST['field_transaction_email'], basename(__FILE__) ) ){
        
        $transaction_email = sanitize_email( $_POST['transaction_email'] );
        
        if( get_post_meta( $post_id, 'transaction_email', false ) ){
            // If the custom field already has a value, update it.
            update_post_meta( $post_id, 'transaction_email', $transaction_email );
        }else{
            // If the custom field doesn't have a value, add it.
            add_post_meta( $post_id, 'transaction_email', $transaction_email);
        }
        if( strlen($transaction_email) == 0 ) {
            // Delete the meta key if there's no value
            delete_post_meta( $post_id, 'transaction_email' );
        }
        
    }

    // Save metabox transaction_name value
    if( isset( $_POST['transaction_name'] ) and wp_verify_nonce( $_POST['field_transaction_name'], basename(__FILE__) ) ){
        
        $transaction_name = sanitize_text_field( $_POST['transaction_name'] );
        
        if( get_post_meta( $post_id, 'transaction_name', false ) ){
            // If the custom field already has a value, update it.
            update_post_meta( $post_id, 'transaction_name', $transaction_name );
        }else{
            // If the custom field doesn't have a value, add it.
            add_post_meta( $post_id, 'transaction_name', $transaction_name);
        }
        if( strlen($transaction_name) == 0 ) {
            // Delete the meta key if there's no value
            delete_post_meta( $post_id, 'transaction_name' );
        }
        
    }

    return $post_id;
}
add_action( 'save_post', 'afz_save_transactions_meta', 1, 2 );



// ADD COLUMNS TO BACK END LISTING

// Add the custom columns to the book post type:
add_filter( 'manage_transactions_posts_columns', 'afz_set_custom_transaction_columns' );
function afz_set_custom_transaction_columns($columns){

    unset( $columns['date'] ); // Unset and set again to keep it the last
    $columns['amount'] = 'Amount';
    $columns['buyer_name'] = 'Name';
    $columns['buyer_email'] = 'E-Mail';
    $columns['date'] = 'Date';

    return $columns;
}

// Add the data to the custom columns for the book post type:
add_action( 'manage_transactions_posts_custom_column' , 'afz_custom_transactions_columns', 10, 2 );
function afz_custom_transactions_columns( $column, $post_id ){

    switch ( $column ){
        case 'amount' :
            echo (get_post_meta( $post_id , 'transaction_amount' , true ) / 100) . '€';
        break;
        case 'buyer_name' :
            echo get_post_meta( $post_id , 'transaction_name' , true );
        break;
        case 'buyer_email' :
            echo get_post_meta( $post_id , 'transaction_email' , true );
        break;
    }

}