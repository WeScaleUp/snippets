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

// ─── Functionaliteit laden ────────────────────────────────────────────────────
$modules = [
    'modules/beveiliging.php',
    'modules/admin-branding.php',
    'modules/svg-upload.php',
    'modules/reacties.php',
    'modules/password-page.php',
    'modules/dashboard.php',
];

foreach ( $modules as $module ) {
    $path = WSU_PLUGIN_DIR . $module;
    if ( file_exists( $path ) ) {
        require_once $path;
    }
}
