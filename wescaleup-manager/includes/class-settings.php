<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WSU_Settings {

    const OPTION_KEY = 'wsu_disabled_modules';

    public static function modules(): array {
        return [
            'beveiliging'    => 'Beveiliging (XML-RPC, REST API, versienummer verbergen)',
            'admin-branding' => 'Admin branding (logo, footer, login-pagina)',
            'svg-upload'     => 'SVG uploads toestaan voor administrators',
            'reacties'       => 'Reacties uitschakelen en verbergen',
            'password-page'  => 'Gestylde wachtwoordpagina',
            'dashboard'      => 'WeScaleUp dashboard widget',
        ];
    }

    public static function is_enabled( string $module ): bool {
        $disabled = get_option( self::OPTION_KEY, [] );
        return ! in_array( $module, (array) $disabled, true );
    }

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_menu' ] );
        add_action( 'admin_init', [ $this, 'save_settings' ] );
        add_filter( 'plugin_action_links_' . plugin_basename( WSU_PLUGIN_FILE ), [ $this, 'add_settings_link' ] );
    }

    public function add_menu(): void {
        add_options_page(
            'WeScaleUp Manager',
            'WeScaleUp Manager',
            'manage_options',
            'wescaleup-manager',
            [ $this, 'render_page' ]
        );
    }

    public function add_settings_link( array $links ): array {
        $url = admin_url( 'options-general.php?page=wescaleup-manager' );
        array_unshift( $links, '<a href="' . esc_url( $url ) . '">Instellingen</a>' );
        return $links;
    }

    public function save_settings(): void {
        if (
            ! isset( $_POST['wsu_save_settings'] ) ||
            ! check_admin_referer( 'wsu_settings_save' )
        ) return;

        $disabled = [];
        foreach ( array_keys( self::modules() ) as $module ) {
            if ( empty( $_POST['wsu_modules'][ $module ] ) ) {
                $disabled[] = $module;
            }
        }

        update_option( self::OPTION_KEY, $disabled );

        add_action( 'admin_notices', function () {
            echo '<div class="notice notice-success is-dismissible"><p>Instellingen opgeslagen.</p></div>';
        } );
    }

    public function render_page(): void {
        $modules  = self::modules();
        $disabled = get_option( self::OPTION_KEY, [] );
        ?>
        <div class="wrap">
            <h1 style="display:flex;align-items:center;gap:12px;">
                <img src="https://wescaleup.nl/wp-content/uploads/2025/03/Ontwerp-zonder-titel-24.png" style="height:28px;">
                WeScaleUp Manager
            </h1>
            <p style="color:#666;">Alle modules staan standaard aan. Vink een module uit om hem op deze website te deactiveren.</p>

            <form method="post" action="">
                <?php wp_nonce_field( 'wsu_settings_save' ); ?>

                <table class="form-table" role="presentation">
                    <tbody>
                        <?php foreach ( $modules as $key => $label ) :
                            $checked = ! in_array( $key, (array) $disabled, true );
                        ?>
                        <tr>
                            <th scope="row"><?php echo esc_html( $label ); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox"
                                           name="wsu_modules[<?php echo esc_attr( $key ); ?>]"
                                           value="1"
                                           <?php checked( $checked ); ?>>
                                    Actief
                                </label>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <p class="submit">
                    <button type="submit" name="wsu_save_settings" class="button button-primary">Opslaan</button>
                </p>
            </form>

            <hr>
            <p style="color:#999;font-size:12px;">
                WeScaleUp Manager v<?php echo esc_html( WSU_VERSION ); ?> —
                <a href="https://github.com/wescaleup/snippets/releases" target="_blank">Changelog</a>
            </p>
        </div>
        <?php
    }
}
