<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Verberg bestaande reacties
add_filter( 'comments_array', fn() => [], 10, 2 );

// Reacties-menu verwijderen
add_action( 'admin_menu', function () {
    remove_menu_page( 'edit-comments.php' );
} );

// Redirect bij directe toegang
add_action( 'admin_init', function () {
    global $pagenow;
    if ( $pagenow === 'edit-comments.php' ) {
        wp_redirect( admin_url() );
        exit;
    }
} );

// Dashboard-widget verwijderen
add_action( 'admin_init', function () {
    remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
} );
