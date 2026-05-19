<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// XML-RPC uitschakelen
add_filter( 'xmlrpc_enabled', '__return_false' );

// REST API beperken tot ingelogde gebruikers
add_filter( 'rest_authentication_errors', function ( $result ) {
    if ( ! is_user_logged_in() ) {
        return new WP_Error(
            'rest_not_logged_in',
            'REST API is alleen beschikbaar voor ingelogde gebruikers.',
            [ 'status' => 401 ]
        );
    }
    return $result;
} );

// WordPress versienummer verbergen
add_filter( 'the_generator', '__return_empty_string' );
