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
// Hangt aan login_head (standaard wp-login.php) én password_protected_login_head
// (Password Protected plugin gebruikt een eigen template zonder login_head).
if ( ! function_exists( 'wsu_login_achtergrond' ) ) {
    function wsu_login_achtergrond() {
        $foto_url = get_option( 'wsu_login_foto', '' );

        if ( empty( $foto_url ) ) {
            $foto_url = 'https://code.wescaleup.nl/media/foto-login.jpg';
        }

        echo '<style>
            body.login::after {
                content: "";
                position: fixed;
                top: 0;
                right: 0;
                width: 50%;
                height: 100%;
                background-image: url(' . esc_url( $foto_url ) . ') !important;
                background-size: cover !important;
                background-position: center center !important;
                background-repeat: no-repeat !important;
                z-index: 0;
            }
        </style>';

        // ── Eigen achtergrondkleur (bijv. huisstijlkleur van de klant) ──
        $kleur = sanitize_hex_color( get_option( 'wsu_login_kleur', '' ) );
        if ( $kleur ) {
            // Lichte kleur? Dan donkere links onder het formulier, anders wit
            $link_kleur = wsu_is_lichte_kleur( $kleur ) ? '#000B41' : '#ffffff';
            echo '<style>
                body.login { background: ' . esc_attr( $kleur ) . ' !important; }
                .login #nav a, .login #backtoblog a { color: ' . esc_attr( $link_kleur ) . ' !important; }
            </style>';

            // (Bijna) witte achtergrond? Dan een grijs formulier, anders is het wit op wit
            if ( wsu_is_bijna_wit( $kleur ) ) {
                echo '<style>
                    .login form { background: #f3f4f6 !important; border: 1px solid #e5e7eb !important; }
                </style>';
            }
        }

        // ── Eigen logo (standaard blijft het WeScaleUp logo uit de huisstijl-CSS) ──
        $logo = get_option( 'wsu_login_logo', '' );
        if ( $logo ) {
            echo '<style>
                .login h1 a {
                    background-image: url(' . esc_url( $logo ) . ') !important;
                    background-size: contain !important;
                    background-position: center center !important;
                    background-repeat: no-repeat !important;
                    width: 100% !important;
                }
            </style>';
        }
    }
}

// Relatieve luminantie van een hex-kleur (0 = zwart, 1 = wit)
if ( ! function_exists( 'wsu_luminantie' ) ) {
    function wsu_luminantie( string $hex ): float {
        $hex = ltrim( $hex, '#' );
        if ( strlen( $hex ) === 3 ) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $kanalen = array_map( function ( $c ) {
            $c = hexdec( $c ) / 255;
            return $c <= 0.03928 ? $c / 12.92 : pow( ( $c + 0.055 ) / 1.055, 2.4 );
        }, str_split( $hex, 2 ) );
        return 0.2126 * $kanalen[0] + 0.7152 * $kanalen[1] + 0.0722 * $kanalen[2];
    }
}

// Is de kleur zo licht dat een wit formulier erop wegvalt?
if ( ! function_exists( 'wsu_is_bijna_wit' ) ) {
    function wsu_is_bijna_wit( string $hex ): bool {
        return wsu_luminantie( $hex ) > 0.85;
    }
}

// Bepaalt of een hex-kleur licht is, voor leesbare tekst erop
if ( ! function_exists( 'wsu_is_lichte_kleur' ) ) {
    function wsu_is_lichte_kleur( string $hex ): bool {
        $luminantie = wsu_luminantie( $hex );

        // Licht = navy tekst geeft meer contrast dan witte tekst (WCAG-contrastverhouding)
        $contrast_wit  = 1.05 / ( $luminantie + 0.05 );
        $contrast_navy = ( $luminantie + 0.05 ) / ( 0.0055 + 0.05 ); // luminantie #000B41 ≈ 0.0055
        return $contrast_navy > $contrast_wit;
    }
}
add_action( 'login_head', 'wsu_login_achtergrond' );
add_action( 'password_protected_login_head', 'wsu_login_achtergrond' );

add_filter( 'login_headerurl',  fn() => home_url() );
// Met een eigen logo hoort de klantnaam bij het logo, anders WeScaleUp
add_filter( 'login_headertext', fn() => get_option( 'wsu_login_logo', '' ) ? get_bloginfo( 'name' ) : 'WeScaleUp' );
