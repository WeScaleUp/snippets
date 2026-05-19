<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_filter( 'the_password_form', function () {
    $post  = get_post();
    $label = 'pwbox_' . ( empty( $post ) ? wp_rand( 1000, 999999 ) : $post->ID );
    $actie = esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) );

    ob_start(); ?>
    <div class="wsupw_wrap">
        <div class="wsupw_card">
            <h2 class="wsupw_title">Deze pagina is afgeschermd</h2>
            <p class="wsupw_text">Voer het wachtwoord in om verder te gaan.</p>
            <form class="wsupw_form" action="<?php echo $actie; ?>" method="post">
                <label class="wsupw_label" for="<?php echo esc_attr( $label ); ?>">Wachtwoord</label>
                <div class="wsupw_row">
                    <input class="wsupw_input" name="post_password" id="<?php echo esc_attr( $label ); ?>"
                           type="password" autocomplete="current-password" required />
                    <button class="wsupw_btn" type="submit">Toegang</button>
                </div>
                <p class="wsupw_hint">Tip: het wachtwoord is hoofdlettergevoelig.</p>
            </form>
        </div>
    </div>
    <?php return ob_get_clean();
} );

add_action( 'wp_head', function () { ?>
    <style>
        :root {
            --wsupw_btn_bg:   var(--e-global-color-accent, #2b1f5e);
            --wsupw_btn_text: #ffffff;
            --wsupw_focus:    rgba(0,0,0,.08);
            --wsupw_border:   rgba(0,0,0,.18);
        }
        .wsupw_wrap  { max-width:720px; margin:40px auto; padding:0 16px; }
        .wsupw_card  { background:#fff; border:1px solid rgba(0,0,0,.08); border-radius:10px; padding:26px; box-shadow:0 14px 40px rgba(0,0,0,.08); }
        .wsupw_title { margin:0 0 8px; font-size:24px; line-height:1.2; }
        .wsupw_text  { margin:0 0 18px; opacity:.8; }
        .wsupw_label { display:block; font-weight:600; margin:0 0 8px; }
        .wsupw_row, .wsupw_input, .wsupw_btn { box-sizing:border-box; }
        .wsupw_row   { display:flex; gap:10px; align-items:stretch; }
        .wsupw_input, .wsupw_input[type="password"] {
            flex:1 1 auto; min-width:0; height:46px; padding:0 14px;
            border-radius:10px; border:1px solid var(--wsupw_border);
            font-size:16px; outline:none; background:#fff;
        }
        .wsupw_input:focus { border-color:rgba(0,0,0,.35); box-shadow:0 0 0 3px var(--wsupw_focus); }
        .wsupw_btn {
            flex:0 0 150px; height:46px; border-radius:10px; border:0; cursor:pointer;
            font-weight:700; font-size:16px; padding:0 16px;
            background:var(--wsupw_btn_bg); color:var(--wsupw_btn_text);
        }
        .wsupw_btn:hover  { filter:brightness(1.05); }
        .wsupw_hint { margin:10px 0 0; font-size:13px; opacity:.7; }
        @media (max-width:520px) { .wsupw_row { flex-direction:column; } .wsupw_btn { width:100%; } }
    </style>
<?php } );
