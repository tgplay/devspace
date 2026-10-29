<?php
defined('ABSPATH') || exit;
?>
<footer class="dv-footer" role="contentinfo">
    <div class="dv-footer__inner">
        <p class="dv-footer__copy">
            &copy; <?php echo date('Y'); ?> <strong>devspace</strong>
            &nbsp;|&nbsp;
            Desenvolvido por
            <a href="https://github.com/tgplay" target="_blank" rel="noopener noreferrer">Tiago Martins</a>
            para WordPress
            <?php
            $dv_priv  = function_exists('gps_legal_url') ? gps_legal_url(GPS_LEGAL_SLUG_PRIVACY) : '';
            $dv_terms = function_exists('gps_legal_url') ? gps_legal_url(GPS_LEGAL_SLUG_TERMS) : '';
            if ($dv_priv)  { echo '&nbsp;|&nbsp;<a href="' . esc_url($dv_priv)  . '">Privacidade</a>'; }
            if ($dv_terms) { echo '&nbsp;|&nbsp;<a href="' . esc_url($dv_terms) . '">Termos de Uso</a>'; }
            ?>
        </p>
    </div>
</footer>

<style>
.dv-footer{
    background:#0f172a;
    border-top:1px solid #1e293b;
    padding:20px 24px;
    text-align:center;
}
.dv-footer__inner{
    max-width:1200px;
    margin:0 auto;
}
.dv-footer__copy{
    font-size:.8125rem;
    color:#64748b;
    margin:0;
    line-height:1.5;
}
.dv-footer__copy strong{color:#94a3b8;}
.dv-footer__copy a{
    color:#60a5fa;
    text-decoration:none;
}
.dv-footer__copy a:hover{text-decoration:underline;}
</style>

<?php wp_footer(); ?>
</body>
</html>
