<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// SVG uploads toestaan voor administrators
add_filter( 'upload_mimes', function ( $upload_mimes ) {
    if ( ! current_user_can( 'administrator' ) ) return $upload_mimes;

    $upload_mimes['svg']  = 'image/svg+xml';
    $upload_mimes['svgz'] = 'image/svg+xml';

    return $upload_mimes;
} );

add_filter( 'wp_check_filetype_and_ext', function ( $data, $file, $filename, $mimes, $real_mime ) {
    if ( ! $data['type'] ) {
        $check           = wp_check_filetype( $filename, $mimes );
        $ext             = $check['ext'];
        $type            = $check['type'];
        $proper_filename = $filename;

        if ( $type && str_starts_with( $type, 'image/' ) && $ext !== 'svg' ) {
            $ext  = false;
            $type = false;
        }

        $data = compact( 'ext', 'type', 'proper_filename' );
    }

    return $data;
}, 10, 5 );
