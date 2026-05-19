<?php
/**
 * Plugin Name: WeScaleUp Manager
 * Plugin URI:  https://github.com/wescaleup/snippets
 * Description: Beheert alle WeScaleUp standaardfunctionaliteit: branding, beveiliging en admin-instellingen.
 * Version:     1.0.0
 * Author:      WeScaleUp
 * Author URI:  https://wescaleup.nl
 * License:     Proprietary
 * Update URI:  https://api.github.com/repos/wescaleup/snippets/releases/latest
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'WSU_VERSION',     '1.0.0' );
define( 'WSU_PLUGIN_FILE', __FILE__ );
define( 'WSU_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'WSU_GITHUB_REPO', 'wescaleup/snippets' );

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
