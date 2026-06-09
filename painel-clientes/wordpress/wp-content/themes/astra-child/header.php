<?php
defined('ABSPATH') || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#primary">
    <?php esc_html_e('Ir para o conteúdo', 'astra-child'); ?>
</a>

<div id="page" class="site">

<header id="site-header" class="gps-header" role="banner">
    <div class="gps-header__inner">

        <!-- Logo -->
        <div class="gps-header__logo">
            <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                <?php if (has_custom_logo()): ?>
                    <?php the_custom_logo(); ?>
                <?php else: ?>
                    <span class="gps-header__site-name"><?php bloginfo('name'); ?></span>
                <?php endif; ?>
            </a>
        </div>

        <!-- Nav Principal -->
        <nav id="primary-nav" class="gps-header__nav"
             aria-label="<?php esc_attr_e('Navegação principal', 'astra-child'); ?>">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary',
                'menu_class'     => 'gps-menu',
                'container'      => false,
                'fallback_cb'    => false,
            ]);
            ?>
        </nav>

        <!-- Ações -->
        <div class="gps-header__actions">

            <!-- Entrar -->
            <a href="<?php echo esc_url('http://localhost:8081/login'); ?>"
               class="gps-btn gps-btn--ghost">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                    <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4"/>
                </svg>
                Login
            </a>

            <!-- Atendimento Dropdown -->
            <div class="gps-dropdown" id="atendimento-dropdown">
                <button class="gps-btn gps-btn--primary gps-dropdown__toggle"
                        aria-haspopup="true" aria-expanded="false"
                        aria-controls="atendimento-menu">
                    Atendimento
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                        <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708"/>
                    </svg>
                </button>
                <ul class="gps-dropdown__menu" id="atendimento-menu" role="menu"
                    aria-label="<?php esc_attr_e('Opções de atendimento', 'astra-child'); ?>">
                    <li role="none">
                        <a href="<?php echo esc_url(home_url('/contato?tipo=vendas')); ?>" role="menuitem">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                                <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5z"/>
                            </svg>
                            Solicitar Orçamento
                        </a>
                    </li>
                    <li role="none">
                        <a href="<?php echo esc_url(home_url('/contato?tipo=suporte')); ?>" role="menuitem">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                                <path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.6.6 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524z"/>
                            </svg>
                            Suporte Técnico
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Hamburger (mobile) -->
            <button class="gps-header__hamburger" id="hamburger-btn"
                    aria-label="<?php esc_attr_e('Abrir menu', 'astra-child'); ?>"
                    aria-expanded="false" aria-controls="primary-nav">
                <span></span><span></span><span></span>
            </button>

        </div><!-- .gps-header__actions -->
    </div><!-- .gps-header__inner -->
</header>
<script>
(function(){
    var btn=document.getElementById('hamburger-btn'),nav=document.getElementById('primary-nav');
    if(btn&&nav)btn.addEventListener('click',function(){var o=nav.classList.toggle('open');btn.classList.toggle('open',o);btn.setAttribute('aria-expanded',String(o));});
})();
</script>
