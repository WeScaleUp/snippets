<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_dashboard_setup', function () {
    global $wp_meta_boxes;

    // Standaard widgets verwijderen
    $remove = [
        ['dashboard_activity',         'normal', 'core'],
        ['dashboard_right_now',        'normal', 'core'],
        ['dashboard_site_health',      'normal', 'core'],
        ['dashboard_quick_press',      'side',   'core'],
        ['dashboard_primary',          'side',   'core'],
        ['dashboard_secondary',        'side',   'core'],
    ];
    foreach ( $remove as [$id, $context, $priority] ) {
        unset( $wp_meta_boxes['dashboard'][$context][$priority][$id] );
    }

    // WeScaleUp widget toevoegen
    wp_add_dashboard_widget(
        'wescaleup_custom_dashboard',
        'Welkom bij WeScaleUp®',
        function () { ?>
            <div style="background:#f9fafb;border-left:5px solid #38b6ff;padding:20px;border-radius:8px;">
                <h2 style="color:#2b2b2b;font-size:24px;margin-top:0;">Fijn dat je er bent!</h2>
                <p style="color:#4a4a4a;font-size:16px;">
                    Deze website wordt professioneel beheerd door <strong>WeScaleUp®</strong>.
                    We zorgen ervoor dat je site veilig, snel en up-to-date blijft.
                </p>
                <hr style="margin:20px 0;border-top:1px solid #ddd;">
                <h3 style="color:#2b2b2b;font-size:18px;">Contact opnemen?</h3>
                <ul style="list-style:none;padding:0;margin:10px 0;">
                    <li style="margin-bottom:8px;"><strong>Email:</strong> <a href="mailto:info@wescaleup.nl" style="color:#38b6ff;">info@wescaleup.nl</a></li>
                    <li style="margin-bottom:8px;"><strong>Telefoon:</strong> <a href="tel:+31165240902" style="color:#38b6ff;">0165 24 09 02</a></li>
                    <li><strong>Website:</strong> <a href="https://wescaleup.nl" target="_blank" rel="noopener" style="color:#38b6ff;">wescaleup.nl</a></li>
                </ul>
                <p style="margin-top:20px;font-size:14px;color:#888;">© <?php echo date('Y'); ?> WeScaleUp®</p>
            </div>
        <?php }
    );
} );

add_action( 'admin_head', function () { ?>
    <style>
        #wescaleup_custom_dashboard .postbox-header { display:none; }
        #wescaleup_custom_dashboard { box-shadow:0 2px 8px rgba(0,0,0,.05); border-radius:10px; }
    </style>
<?php } );
