<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WSU_Settings {

    const OPTION_KEY     = 'wsu_disabled_modules';
    const CUSTOM_PHP_KEY = 'wsu_custom_php';
    const SNIPPETS_KEY   = 'wsu_snippets';

    public static function modules(): array {
        return [
            'beveiliging'    => [ 'label' => 'Beveiliging',           'desc' => 'XML-RPC uitgeschakeld, REST API beperkt, versienummer verborgen',        'icon' => '🔒' ],
            'admin-branding' => [ 'label' => 'Admin branding',        'desc' => 'WeScaleUp logo, aangepaste footer en login-pagina huisstijl',            'icon' => '🎨' ],
            'svg-upload'     => [ 'label' => 'SVG uploads',           'desc' => 'SVG bestanden uploaden toegestaan voor administrators',                   'icon' => '🖼️' ],
            'reacties'       => [ 'label' => 'Reacties uitschakelen', 'desc' => 'Reacties volledig uitgeschakeld en verborgen uit het admin-menu',         'icon' => '💬' ],
            'password-page'  => [ 'label' => 'Wachtwoordpagina',      'desc' => 'Gestylde wachtwoordpagina in WeScaleUp stijl',                           'icon' => '🔑' ],
            'dashboard'      => [ 'label' => 'Dashboard widget',      'desc' => 'WeScaleUp contactwidget op het WordPress dashboard',                     'icon' => '📊' ],
        ];
    }

    public static function is_enabled( string $module ): bool {
        return ! in_array( $module, (array) get_option( self::OPTION_KEY, [] ), true );
    }

    public static function get_snippets(): array {
        return get_option( self::SNIPPETS_KEY, [] );
    }

    public static function run_snippets(): void {
        foreach ( self::get_snippets() as $snippet ) {
            if ( ! empty( $snippet['active'] ) && ! empty( trim( $snippet['code'] ) ) ) {
                eval( $snippet['code'] );
            }
        }
    }

    public static function run_custom_php(): void {
        $code = get_option( self::CUSTOM_PHP_KEY, '' );
        if ( ! empty( trim( $code ) ) ) {
            eval( $code );
        }
    }

    public function __construct() {
        add_action( 'admin_menu',            [ $this, 'add_menu' ] );
        add_action( 'admin_init',            [ $this, 'save_settings' ] );
        add_action( 'admin_post_wsu_save_snippet',   [ $this, 'save_snippet' ] );
        add_action( 'admin_post_wsu_delete_snippet', [ $this, 'delete_snippet' ] );
        add_action( 'admin_post_wsu_toggle_snippet', [ $this, 'toggle_snippet' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
        add_filter( 'plugin_action_links_' . plugin_basename( WSU_PLUGIN_FILE ), [ $this, 'add_settings_link' ] );
    }

    public function add_menu(): void {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6991 5346" fill="currentColor" style="fill-rule:evenodd;clip-rule:evenodd;"><path d="M2171.801,491.308l-152.688,939.946l871.125,141.513l152.692,-939.946l-871.129,-141.513Z"/><path d="M13.551,2736.462l259.154,-1595.508c17.429,-107.583 117.525,-179.625 225.108,-162.054l489.167,79.392c107.446,17.433 179.621,117.525 162.192,224.975l-207.133,1274.525c-51.475,317.304 116.846,504.554 358.838,543.912c252.754,40.854 462.75,-96.008 512.454,-402.417l206.862,-1274.529c17.567,-107.446 117.525,-179.625 219.662,-163.008l489.3,79.529c107.583,17.429 179.763,117.388 162.192,224.971l-259.154,1595.513c-97.371,599.608 -600.288,963.621 -1473.217,813.55c-786.588,-127.738 -1245.25,-623.442 -1145.425,-1238.85" fill-rule="nonzero"/><path d="M3228.343,701.579l-484.946,-672.875c-22.196,-30.917 -65.229,-37.996 -96.279,-15.662l-672.875,484.808c-49.846,35.95 -31.325,114.254 29.279,124.196l1157.817,187.933c60.604,9.942 103.092,-58.558 67.004,-108.4" fill-rule="nonzero"/><path d="M5357.26,3169.875c55.017,-338.683 -125.288,-621.804 -485.354,-680.363c-328.2,-53.25 -622.079,125.287 -681.454,490.8c-58.558,360.338 163.825,622.758 491.887,675.871c360.204,58.558 620.854,-152.933 674.921,-486.308m881.912,143.267c-115.346,709.779 -688.533,1350.788 -1538.037,1212.7c-365.65,-59.375 -585.858,-233.142 -714.821,-458.254l-181.667,1118.467c-16.479,102.133 -116.571,174.175 -224.154,156.742l-489.442,-79.529c-102.133,-16.613 -174.037,-116.571 -157.562,-218.571l549.358,-3382.354c17.433,-107.583 117.525,-179.625 219.663,-163.008l537.783,87.292c80.621,13.075 134.55,88.108 121.475,168.729l-20.021,123.654c193.654,-172.679 457.438,-267.871 823.088,-208.496c849.5,137.954 1190.5,927.4 1074.338,1642.629" fill-rule="nonzero"/></svg>';

        add_menu_page( 'WeScaleUp&#174; Manager', 'WeScaleUp<sup style="font-size:8px;">&#174;</sup>', 'manage_options', 'wescaleup-manager', [ $this, 'render_page' ], 'data:image/svg+xml;base64,' . base64_encode( $svg ), 2 );
        add_action( 'admin_head', function () { echo '<style>#toplevel_page_wescaleup-manager .wp-menu-name sup{line-height:0;}</style>'; } );
    }

    public function add_settings_link( array $links ): array {
        array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=wescaleup-manager' ) ) . '">Instellingen</a>' );
        return $links;
    }

    public function enqueue_styles( string $hook ): void {
        if ( $hook !== 'toplevel_page_wescaleup-manager' ) return;
        wp_enqueue_code_editor( [ 'type' => 'text/x-php' ] );
        wp_enqueue_script( 'wp-theme-plugin-editor' );
        wp_enqueue_style( 'wp-codemirror' );
    }

    public function save_settings(): void {
        if ( ! isset( $_POST['wsu_save_settings'] ) || ! check_admin_referer( 'wsu_settings_save' ) ) return;
        $disabled = [];
        foreach ( array_keys( self::modules() ) as $module ) {
            if ( empty( $_POST['wsu_modules'][ $module ] ) ) $disabled[] = $module;
        }
        update_option( self::OPTION_KEY, $disabled );
        update_option( self::CUSTOM_PHP_KEY, isset( $_POST['wsu_custom_php'] ) ? wp_unslash( $_POST['wsu_custom_php'] ) : '' );
        add_action( 'admin_notices', function () { echo '<div class="notice notice-success is-dismissible"><p>✅ Instellingen opgeslagen.</p></div>'; } );
    }

    public function save_snippet(): void {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Geen toegang.' );
        check_admin_referer( 'wsu_snippet_save' );
        $snippets = self::get_snippets();
        $id   = isset( $_POST['wsu_snippet_id'] ) ? sanitize_key( $_POST['wsu_snippet_id'] ) : '';
        $data = [
            'name'   => sanitize_text_field( wp_unslash( $_POST['wsu_snippet_name'] ?? '' ) ),
            'desc'   => sanitize_text_field( wp_unslash( $_POST['wsu_snippet_desc'] ?? '' ) ),
            'code'   => wp_unslash( $_POST['wsu_snippet_code'] ?? '' ),
            'active' => $id && isset( $snippets[ $id ] ) ? $snippets[ $id ]['active'] : true,
        ];
        $snippets[ $id ?: 'snip_' . uniqid() ] = $data;
        update_option( self::SNIPPETS_KEY, $snippets );
        wp_safe_redirect( admin_url( 'admin.php?page=wescaleup-manager&tab=snippets&saved=1' ) );
        exit;
    }

    public function delete_snippet(): void {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Geen toegang.' );
        check_admin_referer( 'wsu_delete_snippet' );
        $snippets = self::get_snippets();
        unset( $snippets[ sanitize_key( $_POST['wsu_snippet_id'] ?? '' ) ] );
        update_option( self::SNIPPETS_KEY, $snippets );
        wp_safe_redirect( admin_url( 'admin.php?page=wescaleup-manager&tab=snippets' ) );
        exit;
    }

    public function toggle_snippet(): void {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Geen toegang.' );
        check_admin_referer( 'wsu_toggle_snippet' );
        $snippets = self::get_snippets();
        $id = sanitize_key( $_POST['wsu_snippet_id'] ?? '' );
        if ( isset( $snippets[ $id ] ) ) {
            $snippets[ $id ]['active'] = empty( $snippets[ $id ]['active'] );
            update_option( self::SNIPPETS_KEY, $snippets );
        }
        wp_safe_redirect( admin_url( 'admin.php?page=wescaleup-manager&tab=snippets' ) );
        exit;
    }

    public function render_page(): void {
        $tab        = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'modules';
        $edit_id    = isset( $_GET['edit'] ) ? sanitize_key( $_GET['edit'] ) : '';
        $modules    = self::modules();
        $disabled   = get_option( self::OPTION_KEY, [] );
        $snippets   = self::get_snippets();
        $custom_php = get_option( self::CUSTOM_PHP_KEY, '' );
        $editing    = ( $edit_id && isset( $snippets[ $edit_id ] ) ) ? $snippets[ $edit_id ] : null;

        $svg_logo = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6991 5346" style="height:36px;fill:#000B41;fill-rule:evenodd;clip-rule:evenodd;"><path d="M2171.801,491.308l-152.688,939.946l871.125,141.513l152.692,-939.946l-871.129,-141.513Z"/><path d="M13.551,2736.462l259.154,-1595.508c17.429,-107.583 117.525,-179.625 225.108,-162.054l489.167,79.392c107.446,17.433 179.621,117.525 162.192,224.975l-207.133,1274.525c-51.475,317.304 116.846,504.554 358.838,543.912c252.754,40.854 462.75,-96.008 512.454,-402.417l206.862,-1274.529c17.567,-107.446 117.525,-179.625 219.662,-163.008l489.3,79.529c107.583,17.429 179.763,117.388 162.192,224.971l-259.154,1595.513c-97.371,599.608 -600.288,963.621 -1473.217,813.55c-786.588,-127.738 -1245.25,-623.442 -1145.425,-1238.85" fill-rule="nonzero"/><path d="M3228.343,701.579l-484.946,-672.875c-22.196,-30.917 -65.229,-37.996 -96.279,-15.662l-672.875,484.808c-49.846,35.95 -31.325,114.254 29.279,124.196l1157.817,187.933c60.604,9.942 103.092,-58.558 67.004,-108.4" fill-rule="nonzero"/><path d="M5357.26,3169.875c55.017,-338.683 -125.288,-621.804 -485.354,-680.363c-328.2,-53.25 -622.079,125.287 -681.454,490.8c-58.558,360.338 163.825,622.758 491.887,675.871c360.204,58.558 620.854,-152.933 674.921,-486.308m881.912,143.267c-115.346,709.779 -688.533,1350.788 -1538.037,1212.7c-365.65,-59.375 -585.858,-233.142 -714.821,-458.254l-181.667,1118.467c-16.479,102.133 -116.571,174.175 -224.154,156.742l-489.442,-79.529c-102.133,-16.613 -174.037,-116.571 -157.562,-218.571l549.358,-3382.354c17.433,-107.583 117.525,-179.625 219.663,-163.008l537.783,87.292c80.621,13.075 134.55,88.108 121.475,168.729l-20.021,123.654c193.654,-172.679 457.438,-267.871 823.088,-208.496c849.5,137.954 1190.5,927.4 1074.338,1642.629" fill-rule="nonzero"/></svg>';
        ?>
        <div class="wrap wsu-wrap">
            <div class="wsu-header">
                <?php echo $svg_logo; ?>
                <div>
                    <h1>WeScaleUp&#174; Manager</h1>
                    <span class="wsu-version">v<?php echo esc_html( WSU_VERSION ); ?></span>
                </div>
            </div>

            <div class="wsu-tabs">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wescaleup-manager&tab=modules' ) ); ?>" class="wsu-tab <?php echo $tab === 'modules' ? 'is-active' : ''; ?>">⚙️ Modules</a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wescaleup-manager&tab=snippets' ) ); ?>" class="wsu-tab <?php echo $tab === 'snippets' ? 'is-active' : ''; ?>">
                    🧩 Snippets<?php if ( ! empty( $snippets ) ) echo ' <span class="wsu-badge">' . count( $snippets ) . '</span>'; ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wescaleup-manager&tab=custom' ) ); ?>" class="wsu-tab <?php echo $tab === 'custom' ? 'is-active' : ''; ?>">📝 Losse code</a>
            </div>

            <?php if ( isset( $_GET['saved'] ) ) echo '<div class="notice notice-success is-dismissible"><p>✅ Snippet opgeslagen.</p></div>'; ?>

            <?php if ( $tab === 'modules' ) : ?>
            <form method="post" action="">
                <?php wp_nonce_field( 'wsu_settings_save' ); ?>
                <div class="wsu-section">
                    <h2>Modules</h2>
                    <p>Standaard WeScaleUp functionaliteit. Zet een module uit om hem op <strong>deze website</strong> te deactiveren.</p>
                    <div class="wsu-modules">
                        <?php foreach ( $modules as $key => $module ) :
                            $enabled = ! in_array( $key, (array) $disabled, true ); ?>
                        <div class="wsu-module <?php echo $enabled ? 'is-active' : ''; ?>">
                            <div class="wsu-module-info">
                                <span class="wsu-module-icon"><?php echo $module['icon']; ?></span>
                                <div>
                                    <strong><?php echo esc_html( $module['label'] ); ?></strong>
                                    <span><?php echo esc_html( $module['desc'] ); ?></span>
                                </div>
                            </div>
                            <label class="wsu-toggle">
                                <input type="checkbox" name="wsu_modules[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( $enabled ); ?>>
                                <span class="wsu-toggle-slider"></span>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="wsu-footer">
                    <button type="submit" name="wsu_save_settings" class="wsu-btn">Opslaan</button>
                    <a href="https://github.com/wescaleup/snippets/releases" target="_blank" class="wsu-changelog">Changelog →</a>
                </div>
            </form>

            <?php elseif ( $tab === 'snippets' ) : ?>
            <div class="wsu-section">
                <h2><?php echo $editing ? 'Snippet bewerken' : 'Nieuw snippet toevoegen'; ?></h2>
                <p>Snippets worden opgeslagen in de database van <strong>deze website</strong> en draaien bij elke pageload.</p>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php wp_nonce_field( 'wsu_snippet_save' ); ?>
                    <input type="hidden" name="action" value="wsu_save_snippet">
                    <input type="hidden" name="wsu_snippet_id" value="<?php echo esc_attr( $edit_id ); ?>">
                    <div class="wsu-field">
                        <label for="wsu_snippet_name">Naam <span style="color:#ef4444">*</span></label>
                        <input type="text" id="wsu_snippet_name" name="wsu_snippet_name" value="<?php echo esc_attr( $editing['name'] ?? '' ); ?>" placeholder="bijv. Verberg admin menu items" required>
                    </div>
                    <div class="wsu-field">
                        <label for="wsu_snippet_desc">Omschrijving</label>
                        <input type="text" id="wsu_snippet_desc" name="wsu_snippet_desc" value="<?php echo esc_attr( $editing['desc'] ?? '' ); ?>" placeholder="Wat doet dit snippet?">
                    </div>
                    <div class="wsu-field">
                        <label for="wsu_snippet_code">PHP code <small style="color:#6b7280">(geen &lt;?php nodig)</small></label>
                        <div class="wsu-editor-wrap">
                            <textarea id="wsu_snippet_code" name="wsu_snippet_code" rows="12"><?php echo esc_textarea( $editing['code'] ?? '' ); ?></textarea>
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;margin-top:16px;">
                        <button type="submit" class="wsu-btn"><?php echo $editing ? 'Opslaan' : 'Snippet toevoegen'; ?></button>
                        <?php if ( $editing ) echo '<a href="' . esc_url( admin_url( 'admin.php?page=wescaleup-manager&tab=snippets' ) ) . '" class="wsu-btn-ghost">Annuleren</a>'; ?>
                    </div>
                </form>
            </div>

            <?php if ( ! empty( $snippets ) ) : ?>
            <div class="wsu-section">
                <h2>Opgeslagen snippets</h2>
                <div class="wsu-modules">
                    <?php foreach ( $snippets as $id => $snippet ) :
                        $active = ! empty( $snippet['active'] ); ?>
                    <div class="wsu-module <?php echo $active ? 'is-active' : ''; ?>">
                        <div class="wsu-module-info">
                            <span class="wsu-module-icon">🧩</span>
                            <div>
                                <strong><?php echo esc_html( $snippet['name'] ); ?></strong>
                                <?php if ( ! empty( $snippet['desc'] ) ) echo '<span>' . esc_html( $snippet['desc'] ) . '</span>'; ?>
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:12px;flex-shrink:0;">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=wescaleup-manager&tab=snippets&edit=' . $id ) ); ?>" class="wsu-action-link">Bewerken</a>
                            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0;">
                                <?php wp_nonce_field( 'wsu_delete_snippet' ); ?>
                                <input type="hidden" name="action" value="wsu_delete_snippet">
                                <input type="hidden" name="wsu_snippet_id" value="<?php echo esc_attr( $id ); ?>">
                                <button type="submit" class="wsu-action-link wsu-delete-link" onclick="return confirm('Snippet verwijderen?')">Verwijderen</button>
                            </form>
                            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0;">
                                <?php wp_nonce_field( 'wsu_toggle_snippet' ); ?>
                                <input type="hidden" name="action" value="wsu_toggle_snippet">
                                <input type="hidden" name="wsu_snippet_id" value="<?php echo esc_attr( $id ); ?>">
                                <label class="wsu-toggle" title="<?php echo $active ? 'Uitzetten' : 'Aanzetten'; ?>">
                                    <input type="checkbox" <?php checked( $active ); ?> onchange="this.form.submit()">
                                    <span class="wsu-toggle-slider"></span>
                                </label>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php elseif ( $tab === 'custom' ) : ?>
            <form method="post" action="">
                <?php wp_nonce_field( 'wsu_settings_save' ); ?>
                <div class="wsu-section">
                    <h2>Losse code</h2>
                    <p>PHP code die <strong>alleen op deze website</strong> wordt uitgevoerd. Geen <code>&lt;?php</code> tag nodig.<br>
                    <span style="color:#f59e0b;">⚠️ Dit is voor eenmalige aanpassingen. Voor herbruikbare code gebruik je het <a href="<?php echo esc_url( admin_url( 'admin.php?page=wescaleup-manager&tab=snippets' ) ); ?>">Snippets tabblad</a>.</span></p>
                    <div class="wsu-editor-wrap">
                        <textarea id="wsu_custom_php" name="wsu_custom_php" rows="16"><?php echo esc_textarea( $custom_php ); ?></textarea>
                    </div>
                </div>
                <div class="wsu-footer">
                    <button type="submit" name="wsu_save_settings" class="wsu-btn">Opslaan</button>
                </div>
            </form>
            <?php endif; ?>
        </div>

        <style>
            .wsu-wrap{max-width:860px}
            .wsu-header{display:flex;align-items:center;gap:16px;padding:24px 0 20px;border-bottom:1px solid #e5e7eb;margin-bottom:0}
            .wsu-header h1{margin:0;font-size:22px;line-height:1.2}
            .wsu-version{display:inline-block;background:#f3f4f6;color:#6b7280;font-size:11px;padding:2px 8px;border-radius:20px;margin-top:4px}
            .wsu-tabs{display:flex;gap:4px;padding:16px 0 0;margin-bottom:20px;border-bottom:2px solid #e5e7eb}
            .wsu-tab{display:flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px 8px 0 0;font-size:13px;font-weight:500;color:#6b7280;text-decoration:none;border:1px solid transparent;border-bottom:none;margin-bottom:-2px;transition:all .15s}
            .wsu-tab:hover{color:#111827;background:#f9fafb}
            .wsu-tab.is-active{color:#000B41;background:#fff;border-color:#e5e7eb;border-bottom-color:#fff}
            .wsu-badge{background:#000B41;color:#fff;font-size:10px;padding:1px 6px;border-radius:20px}
            .wsu-section{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;margin-bottom:20px}
            .wsu-section h2{margin:0 0 6px;font-size:15px;font-weight:600}
            .wsu-section>p{margin:0 0 20px;color:#6b7280;font-size:13px}
            .wsu-modules{display:flex;flex-direction:column;gap:2px}
            .wsu-module{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-radius:8px;background:#f9fafb;border:1px solid transparent;transition:background .15s}
            .wsu-module.is-active{background:#f0fdf4;border-color:#bbf7d0}
            .wsu-module-info{display:flex;align-items:center;gap:12px}
            .wsu-module-icon{font-size:20px;line-height:1}
            .wsu-module-info strong{display:block;font-size:13px;margin-bottom:2px}
            .wsu-module-info span{font-size:12px;color:#6b7280}
            .wsu-toggle{position:relative;display:inline-block;width:44px;height:24px;flex-shrink:0}
            .wsu-toggle input{opacity:0;width:0;height:0}
            .wsu-toggle-slider{position:absolute;inset:0;background:#d1d5db;border-radius:24px;cursor:pointer;transition:background .2s}
            .wsu-toggle-slider:before{content:'';position:absolute;width:18px;height:18px;left:3px;top:3px;background:#fff;border-radius:50%;transition:transform .2s;box-shadow:0 1px 3px rgba(0,0,0,.2)}
            .wsu-toggle input:checked+.wsu-toggle-slider{background:#16a34a}
            .wsu-toggle input:checked+.wsu-toggle-slider:before{transform:translateX(20px)}
            .wsu-field{margin-bottom:16px}
            .wsu-field label{display:block;font-size:13px;font-weight:600;margin-bottom:6px}
            .wsu-field input[type="text"]{width:100%;padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;box-sizing:border-box}
            .wsu-field input[type="text"]:focus{outline:none;border-color:#000B41;box-shadow:0 0 0 3px rgba(0,11,65,.08)}
            .wsu-action-link{font-size:12px;color:#6b7280;text-decoration:none;background:none;border:none;cursor:pointer;padding:0}
            .wsu-action-link:hover{color:#000B41}
            .wsu-delete-link:hover{color:#ef4444 !important}
            .wsu-editor-wrap .CodeMirror{height:280px;border-radius:8px;border:1px solid #e5e7eb;font-size:13px}
            #wsu_custom_php,#wsu_snippet_code{width:100%;font-family:monospace;font-size:13px;border-radius:8px;border:1px solid #e5e7eb;padding:12px;background:#1e1e1e;color:#d4d4d4;box-sizing:border-box}
            .wsu-footer{display:flex;align-items:center;gap:16px;padding-bottom:40px}
            .wsu-btn{background:#FC6404;color:#fff;border:none;padding:10px 24px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;text-decoration:none}
            .wsu-btn:hover{background:#e55a00}
            .wsu-btn-ghost{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;padding:10px 24px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;text-decoration:none;display:inline-block}
            .wsu-btn-ghost:hover{background:#e5e7eb}
            .wsu-changelog{font-size:13px;color:#6b7280;text-decoration:none}
            .wsu-changelog:hover{color:#FC6404}
        </style>

        <script>
        jQuery(function($){
            $('.wsu-toggle input[type="checkbox"]').not('[onchange]').on('change',function(){
                $(this).closest('.wsu-module').toggleClass('is-active',this.checked);
            });
            function initEditor(id){
                var $el=$('#'+id);
                if($el.length&&typeof wp!=='undefined'&&wp.codeEditor){
                    wp.codeEditor.initialize($el,{codemirror:{mode:'php',lineNumbers:true,indentUnit:4,tabSize:4,indentWithTabs:true}});
                }
            }
            initEditor('wsu_custom_php');
            initEditor('wsu_snippet_code');
        });
        </script>
        <?php
    }
}
