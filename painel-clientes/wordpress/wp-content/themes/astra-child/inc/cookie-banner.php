<?php
defined('ABSPATH') || exit;

/**
 * Banner de consentimento de cookies (LGPD).
 * Injetado via wp_footer. Desaparece ao aceitar e grava
 * flag em localStorage para não reexibir.
 */
add_action('wp_footer', function (): void {
    $privacy_url = home_url('/politica-de-privacidade');
    ?>
<div id="dv-cookie-banner" class="dv-cookie-banner" role="dialog"
     aria-live="polite" aria-label="Aviso de cookies" hidden>
    <p class="dv-cookie-banner__text">
        Usamos cookies para melhorar sua experiência. Ao continuar navegando, você concorda com o uso de cookies.
        <a href="<?php echo esc_url($privacy_url); ?>">Saiba mais</a>.
    </p>
    <button class="dv-cookie-banner__btn" id="dv-cookie-accept">Entendi</button>
</div>

<style>
.dv-cookie-banner{
    position:fixed;bottom:0;left:0;right:0;z-index:9999;
    display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;
    padding:14px 24px;
    background:#1e293b;
    border-top:1px solid #334155;
    font-family:inherit;
    box-shadow:0 -4px 20px rgba(0,0,0,.25);
    animation:dvCookieSlide .3s ease;
}
@keyframes dvCookieSlide{from{transform:translateY(100%)}to{transform:translateY(0)}}
.dv-cookie-banner[hidden]{display:none!important;}
.dv-cookie-banner__text{
    font-size:.875rem;color:#cbd5e1;margin:0;line-height:1.55;
}
.dv-cookie-banner__text a{
    color:#60a5fa;text-decoration:underline;
}
.dv-cookie-banner__text a:hover{color:#93c5fd;}
.dv-cookie-banner__btn{
    flex-shrink:0;
    padding:9px 22px;
    background:#2563eb;color:#fff;
    border:none;border-radius:8px;
    font-size:.875rem;font-weight:600;
    cursor:pointer;font-family:inherit;
    transition:background .15s;
}
.dv-cookie-banner__btn:hover{background:#1d4ed8;}
</style>

<script>
(function(){
    var KEY = 'dv_cookies_ok';
    var banner = document.getElementById('dv-cookie-banner');
    if(!banner) return;
    if(!localStorage.getItem(KEY)){
        banner.hidden = false;
    }
    document.getElementById('dv-cookie-accept').addEventListener('click', function(){
        localStorage.setItem(KEY, '1');
        banner.style.animation = 'dvCookieSlide .25s ease reverse';
        setTimeout(function(){ banner.hidden = true; }, 240);
    });
})();
</script>
    <?php
});
