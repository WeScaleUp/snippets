<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WSU_Settings {

    const OPTION_KEY     = 'wsu_disabled_modules';
    const CUSTOM_PHP_KEY = 'wsu_custom_php';

    public static function modules(): array {
        return [
            'beveiliging'    => [
                'label' => 'Beveiliging',
                'desc'  => 'XML-RPC uitgeschakeld, REST API beperkt, versienummer verborgen',
                'icon'  => '🔒',
            ],
            'admin-branding' => [
                'label' => 'Admin branding',
                'desc'  => 'WeScaleUp logo, aangepaste footer en login-pagina huisstijl',
                'icon'  => '🎨',
            ],
            'svg-upload'     => [
                'label' => 'SVG uploads',
                'desc'  => 'SVG bestanden uploaden toegestaan voor administrators',
                'icon'  => '🖼️',
            ],
            'reacties'       => [
                'label' => 'Reacties uitschakelen',
                'desc'  => 'Reacties volledig uitgeschakeld en verborgen uit het admin-menu',
                'icon'  => '💬',
            ],
            'password-page'  => [
                'label' => 'Wachtwoordpagina',
                'desc'  => 'Gestylde wachtwoordpagina in WeScaleUp stijl',
                'icon'  => '🔑',
            ],
            'dashboard'      => [
                'label' => 'Dashboard widget',
                'desc'  => 'WeScaleUp contactwidget op het WordPress dashboard',
                'icon'  => '📊',
            ],
        ];
    }

    public static function is_enabled( string $module ): bool {
        $disabled = get_option( self::OPTION_KEY, [] );
        return ! in_array( $module, (array) $disabled, true );
    }

    public static function run_custom_php(): void {
        $code = get_option( self::CUSTOM_PHP_KEY, '' );
        if ( ! empty( trim( $code ) ) ) {
            eval( $code );
        }
    }

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_menu' ] );
        add_action( 'admin_init', [ $this, 'save_settings' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
        add_filter( 'plugin_action_links_' . plugin_basename( WSU_PLUGIN_FILE ), [ $this, 'add_settings_link' ] );
    }

    public function add_menu(): void {
        add_menu_page(
            'WeScaleUp Manager',
            'WeScaleUp',
            'manage_options',
            'wescaleup-manager',
            [ $this, 'render_page' ],
            'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><text y="15" font-size="14">⚡</text></svg>' ),
            2
        );
    }

    public function add_settings_link( array $links ): array {
        $url = admin_url( 'admin.php?page=wescaleup-manager' );
        array_unshift( $links, '<a href="' . esc_url( $url ) . '">Instellingen</a>' );
        return $links;
    }

    public function enqueue_styles( string $hook ): void {
        if ( $hook !== 'toplevel_page_wescaleup-manager' ) return;
        wp_enqueue_code_editor( [ 'type' => 'text/x-php' ] );
        wp_enqueue_script( 'wp-theme-plugin-editor' );
        wp_enqueue_style( 'wp-codemirror' );
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

        $custom_php = isset( $_POST['wsu_custom_php'] ) ? wp_unslash( $_POST['wsu_custom_php'] ) : '';
        update_option( self::CUSTOM_PHP_KEY, $custom_php );

        add_action( 'admin_notices', function () {
            echo '<div class="notice notice-success is-dismissible"><p>✅ Instellingen opgeslagen.</p></div>';
        } );
    }

    public function render_page(): void {
        $modules    = self::modules();
        $disabled   = get_option( self::OPTION_KEY, [] );
        $custom_php = get_option( self::CUSTOM_PHP_KEY, '' );
        ?>
        <div class="wrap wsu-wrap">

            <div class="wsu-header">
                <img src="https://wescaleup.nl/wp-content/uploads/2025/03/Ontwerp-zonder-titel-24.png" alt="WeScaleUp">
                <div>
                    <h1>WeScaleUp Manager</h1>
                    <span class="wsu-version">v<?php echo esc_html( WSU_VERSION ); ?></span>
                </div>
            </div>

            <form method="post" action="">
                <?php wp_nonce_field( 'wsu_settings_save' ); ?>

                <div class="wsu-section">
                    <h2>Modules</h2>
                    <p>Alle modules staan standaard aan. Zet een module uit om hem op deze website te deactiveren.</p>
                    <div class="wsu-modules">
                        <?php foreach ( $modules as $key => $module ) :
                            $enabled = ! in_array( $key, (array) $disabled, true );
                        ?>
                        <div class="wsu-module <?php echo $enabled ? 'is-active' : ''; ?>">
                            <div class="wsu-module-info">
                                <span class="wsu-module-icon"><?php echo $module['icon']; ?></span>
                                <div>
                                    <strong><?php echo esc_html( $module['label'] ); ?></strong>
                                    <span><?php echo esc_html( $module['desc'] ); ?></span>
                                </div>
                            </div>
                            <label class="wsu-toggle">
                                <input type="checkbox"
                                       name="wsu_modules[<?php echo esc_attr( $key ); ?>]"
                                       value="1"
                                       <?php checked( $enabled ); ?>>
                                <span class="wsu-toggle-slider"></span>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="wsu-section">
                    <h2>Custom PHP</h2>
                    <p>PHP code die alleen op deze website wordt uitgevoerd. Geen <code>&lt;?php</code> tag nodig.</p>
                    <div class="wsu-editor-wrap">
                        <textarea id="wsu_custom_php" name="wsu_custom_php" rows="16"><?php echo esc_textarea( $custom_php ); ?></textarea>
                    </div>
                </div>

                <div class="wsu-footer">
                    <button type="submit" name="wsu_save_settings" class="wsu-btn">Opslaan</button>
                    <a href="https://github.com/wescaleup/snippets/releases" target="_blank" class="wsu-changelog">Changelog →</a>
                </div>

            </form>
        </div>

        <style>
            .wsu-wrap { max-width: 780px; }
            .wsu-header { display:flex; align-items:center; gap:16px; padding:24px 0 20px; border-bottom:1px solid #e5e7eb; margin-bottom:28px; }
            .wsu-header img { height:36px; }
            .wsu-header h1 { margin:0; font-size:22px; line-height:1.2; }
            .wsu-version { display:inline-block; background:#f3f4f6; color:#6b7280; font-size:11px; padding:2px 8px; border-radius:20px; margin-top:4px; }
            .wsu-section { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:24px; margin-bottom:20px; }
            .wsu-section h2 { margin:0 0 6px; font-size:15px; font-weight:600; }
            .wsu-section > p { margin:0 0 20px; color:#6b7280; font-size:13px; }
            .wsu-modules { display:flex; flex-direction:column; gap:2px; }
            .wsu-module { display:flex; align-items:center; justify-content:space-between; padding:14px 16px; border-radius:8px; background:#f9fafb; border:1px solid transparent; transition:background 0.15s; }
            .wsu-module.is-active { background:#f0fdf4; border-color:#bbf7d0; }
            .wsu-module-info { display:flex; align-items:center; gap:12px; }
            .wsu-module-icon { font-size:20px; line-height:1; }
            .wsu-module-info strong { display:block; font-size:13px; margin-bottom:2px; }
            .wsu-module-info span { font-size:12px; color:#6b7280; }
            .wsu-toggle { position:relative; display:inline-block; width:44px; height:24px; flex-shrink:0; }
            .wsu-toggle input { opacity:0; width:0; height:0; }
            .wsu-toggle-slider { position:absolute; inset:0; background:#d1d5db; border-radius:24px; cursor:pointer; transition:background 0.2s; }
            .wsu-toggle-slider:before { content:''; position:absolute; width:18px; height:18px; left:3px; top:3px; background:#fff; border-radius:50%; transition:transform 0.2s; box-shadow:0 1px 3px rgba(0,0,0,.2); }
            .wsu-toggle input:checked + .wsu-toggle-slider { background:#16a34a; }
            .wsu-toggle input:checked + .wsu-toggle-slider:before { transform:translateX(20px); }
            .wsu-editor-wrap .CodeMirror { height:320px; border-radius:8px; border:1px solid #e5e7eb; font-size:13px; }
            #wsu_custom_php { width:100%; font-family:monospace; font-size:13px; border-radius:8px; border:1px solid #e5e7eb; padding:12px; background:#1e1e1e; color:#d4d4d4; }
            .wsu-footer { display:flex; align-items:center; gap:16px; padding-bottom:40px; }
            .wsu-btn { background:#FC6404; color:#fff; border:none; padding:10px 24px; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; }
            .wsu-btn:hover { background:#e55a00; }
            .wsu-changelog { font-size:13px; color:#6b7280; text-decoration:none; }
            .wsu-changelog:hover { color:#FC6404; }
        </style>

        <script>
        jQuery(function($) {
            $('.wsu-toggle input').on('change', function() {
                $(this).closest('.wsu-module').toggleClass('is-active', this.checked);
            });
            if (typeof wp !== 'undefined' && wp.codeEditor) {
                wp.codeEditor.initialize($('#wsu_custom_php'), {
                    codemirror: { mode:'php', lineNumbers:true, indentUnit:4, tabSize:4, indentWithTabs:true }
                });
            }
        });
        </script>
        <?php
    }
}
