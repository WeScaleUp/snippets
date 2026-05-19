<?php
/**
 * Plugin Name: WeScaleUp Manager
 * Plugin URI:  https://github.com/wescaleup/snippets
 * Description: Beheert alle WeScaleUp standaardfunctionaliteit: branding, beveiliging en admin-instellingen.
 * Version:     4.0.0
 * Author:      WeScaleUp
 * Author URI:  https://wescaleup.nl
 * License:     Proprietary
 * Update URI:  https://api.github.com/repos/wescaleup/snippets/releases/latest
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'WSU_VERSION',     '4.0.0' );
define( 'WSU_PLUGIN_FILE', __FILE__ );
define( 'WSU_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'WSU_GITHUB_REPO', 'wescaleup/snippets' );

// ─── Bij activatie: zorg dat alle modules standaard aan staan ─────────────────
register_activation_hook( __FILE__, function () {
    if ( get_option( 'wsu_disabled_modules' ) === false ) {
        update_option( 'wsu_disabled_modules', [] );
    }
} );

// ─── Bij update: reset disabled_modules naar leeg als het een oude waarde heeft ─
add_action( 'upgrader_process_complete', function ( $upgrader, $options ) {
    if (
        $options['action'] === 'update' &&
        $options['type'] === 'plugin' &&
        isset( $options['plugins'] ) &&
        in_array( plugin_basename( WSU_PLUGIN_FILE ), $options['plugins'], true )
    ) {
        update_option( 'wsu_disabled_modules', [] );
    }
}, 10, 2 );

// ─── Auto-updater ────────────────────────────────────────────────────────────
require_once WSU_PLUGIN_DIR . 'includes/class-updater.php';
new WSU_Updater( WSU_PLUGIN_FILE, WSU_GITHUB_REPO, WSU_VERSION );

// Automatisch updaten zonder bevestiging
add_filter( 'auto_update_plugin', function ( $update, $item ) {
    if ( isset( $item->plugin ) && $item->plugin === plugin_basename( WSU_PLUGIN_FILE ) ) {
        return true;
    }
    return $update;
}, 10, 2 );

// ─── Instellingen ─────────────────────────────────────────────────────────────
require_once WSU_PLUGIN_DIR . 'includes/class-settings.php';
new WSU_Settings();

// ─── Functionaliteit laden (alleen actieve modules) ───────────────────────────
$modules = [
    'beveiliging',
    'admin-branding',
    'svg-upload',
    'reacties',
    'password-page',
    'dashboard',
];

foreach ( $modules as $module ) {
    if ( ! WSU_Settings::is_enabled( $module ) ) continue;
    $path = WSU_PLUGIN_DIR . 'modules/' . $module . '.php';
    if ( file_exists( $path ) ) {
        require_once $path;
    }
}

// ─── Custom PHP per site ──────────────────────────────────────────────────────
// Prioriteit 0: zorgt dat register_post_type/register_taxonomy in snippets
// vroeg genoeg draaien zodat WordPress ze correct registreert.
add_action( 'init', [ 'WSU_Settings', 'run_custom_php' ], 0 );

// ─── Site-specifieke snippets ─────────────────────────────────────────────────
add_action( 'init', [ 'WSU_Settings', 'run_snippets' ], 0 );
