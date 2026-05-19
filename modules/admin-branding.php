<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ── Admin-balk: vervang WordPress-logo door WeScaleUp-logo ───────────────────
add_action( 'admin_bar_menu', function ( $wp_admin_bar ) {
    $wp_admin_bar->remove_node( 'wp-logo' );

    $wp_admin_bar->add_node( [
        'id'    => 'wp-logo',
        'title' => '<img src="https://wescaleup.nl/wp-content/uploads/2025/03/Ontwerp-zonder-titel-24.png" style="height:20px; margin-top:6px;">',
        'href'  => admin_url(),
        'meta'  => [ 'title' => 'Dashboard' ],
    ] );
}, 11 );

// ── Admin-footer ─────────────────────────────────────────────────────────────
add_filter( 'admin_footer_text', function () {
    return 'Website in beheer bij <a href="https://wescaleup.nl" target="_blank" rel="noopener">WeScaleUp</a>. Voor support: <a href="mailto:info@wescaleup.nl">info@wescaleup.nl</a>';
} );

// ── Login-pagina: huisstijl ───────────────────────────────────────────────────
add_action( 'login_enqueue_scripts', function () {
    wp_enqueue_style(
        'wsu-login-style',
        'https://code.wescaleup.nl/styles/login-style.css',
        [],
        WSU_VERSION
    );
} );

// ── Login-pagina: achtergrondafbeelding ───────────────────────────────────────
add_action( 'login_head', function () {
    $foto_url = get_option( 'wsu_login_foto', '' );

    // Fallback naar de standaard WeScaleUp foto
    if ( empty( $foto_url ) ) {
        $foto_url = 'https://code.wescaleup.nl/media/foto-login.jpg';
    }

    echo '<style>
        body.login {
            background-image: url(' . esc_url( $foto_url ) . ') !important;
            background-size: cover !important;
            background-position: center center !important;
            background-repeat: no-repeat !important;
        }
    </style>';
} );

add_filter( 'login_headerurl',  fn() => home_url() );
add_filter( 'login_headertext', fn() => 'WeScaleUp' );
