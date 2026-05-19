<?php
/**
 * WSU_Updater — haalt update-info op via de GitHub Releases API
 * en injecteert die in de WordPress plugin-update flow.
 *
 * Werkt met een publieke GitHub repo — geen token nodig.
 * GitHub genereert bij elke Release automatisch een ZIP-download.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class WSU_Updater {

    private string $plugin_file;
    private string $plugin_slug;
    private string $github_repo;   // bijv. "WeScaleUp/wescaleup-manager"
    private string $current_version;

    public function __construct( string $plugin_file, string $github_repo, string $current_version ) {
        $this->plugin_file     = $plugin_file;
        $this->plugin_slug     = plugin_basename( $plugin_file );
        $this->github_repo     = $github_repo;
        $this->current_version = $current_version;

        add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'check_update' ] );
        add_filter( 'plugins_api', [ $this, 'plugin_info' ], 10, 3 );
        add_filter( 'upgrader_source_selection', [ $this, 'fix_directory_name' ], 10, 4 );
    }

    /** Vergelijk versies en voeg update toe aan WordPress transient. */
    public function check_update( $transient ) {
        if ( empty( $transient->checked ) ) return $transient;

        $release = $this->get_latest_release();
        if ( ! $release ) return $transient;

        $latest_version = ltrim( $release->tag_name, 'v' );

        if ( version_compare( $this->current_version, $latest_version, '<' ) ) {
            $transient->response[ $this->plugin_slug ] = (object) [
                'slug'        => dirname( $this->plugin_slug ),
                'plugin'      => $this->plugin_slug,
                'new_version' => $latest_version,
                'url'         => $release->html_url,
                'package'     => $this->get_zip_url( $release ),
                'requires'    => '6.0',
                'tested'      => '6.9',
            ];
        }

        return $transient;
    }

    /** Geeft plugin-informatie terug voor het WordPress update-popup venster. */
    public function plugin_info( $result, $action, $args ) {
        if ( $action !== 'plugin_information' ) return $result;
        if ( ! isset( $args->slug ) || $args->slug !== dirname( $this->plugin_slug ) ) return $result;

        $release = $this->get_latest_release();
        if ( ! $release ) return $result;

        $latest_version = ltrim( $release->tag_name, 'v' );

        $changelog = ! empty( $release->body )
            ? '<pre>' . esc_html( $release->body ) . '</pre>'
            : 'Zie <a href="' . esc_url( $release->html_url ) . '" target="_blank">GitHub</a> voor de changelog.';

        return (object) [
            'name'          => 'WeScaleUp Manager',
            'slug'          => dirname( $this->plugin_slug ),
            'version'       => $latest_version,
            'author'        => '<a href="https://wescaleup.nl">WeScaleUp</a>',
            'requires'      => '6.0',
            'tested'        => '6.9',
            'download_link' => $this->get_zip_url( $release ),
            'sections'      => [
                'changelog' => $changelog,
            ],
        ];
    }

    /**
     * Zorgt dat de uitgepakte map altijd 'wescaleup-manager' heet.
     * GitHub pakt de source ZIP uit als "wescaleup-manager-1.2.3/" — dit corrigeert dat.
     */
    public function fix_directory_name( $source, $remote_source, $upgrader, $hook_extra ) {
        if ( ! isset( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->plugin_slug ) {
            return $source;
        }

        $correct = trailingslashit( $remote_source ) . 'wescaleup-manager/';

        if ( $source !== $correct ) {
            global $wp_filesystem;
            if ( $wp_filesystem->move( $source, $correct ) ) {
                return $correct;
            }
        }

        return $source;
    }

    /**
     * Haalt de nieuwste GitHub Release op via de API.
     * Gecacht voor 12 uur om de GitHub rate limit te respecteren.
     */
    private function get_latest_release(): ?object {
        $cached = get_transient( 'wsu_github_release' );
        if ( $cached ) return $cached;

        $url      = "https://api.github.com/repos/{$this->github_repo}/releases/latest";
        $response = wp_remote_get( $url, [
            'timeout' => 10,
            'headers' => [
                'Accept'     => 'application/vnd.github+json',
                'User-Agent' => 'WeScaleUp-Manager-Updater/' . WSU_VERSION,
            ],
        ] );

        if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
            return null;
        }

        $release = json_decode( wp_remote_retrieve_body( $response ) );
        if ( empty( $release->tag_name ) ) return null;

        set_transient( 'wsu_github_release', $release, 12 * HOUR_IN_SECONDS );

        return $release;
    }

    /**
     * Geeft de ZIP-download URL terug.
     * Gebruikt de eerste geüploade asset als die aanwezig is,
     * anders de automatische GitHub source ZIP.
     */
    private function get_zip_url( object $release ): string {
        if ( ! empty( $release->assets ) ) {
            foreach ( $release->assets as $asset ) {
                if ( str_ends_with( $asset->name, '.zip' ) ) {
                    return $asset->browser_download_url;
                }
            }
        }

        // Fallback: automatische source ZIP van GitHub (werkt altijd bij publieke repos)
        return "https://github.com/{$this->github_repo}/archive/refs/tags/{$release->tag_name}.zip";
    }
}
