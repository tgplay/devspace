<?php
defined('ABSPATH') || exit;

define('GPS_CLIENT_PORTAL_URL', rtrim(getenv('CLIENT_PORTAL_URL') ?: 'http://localhost:8081', '/'));

/* ──────────────────────────────────────────────
   Suporte ao tema
────────────────────────────────────────────── */
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    register_nav_menus([
        'primary' => 'Menu Principal',
        'footer'  => 'Menu Rodapé',
    ]);
});

/* ──────────────────────────────────────────────
   Enqueue de estilos
────────────────────────────────────────────── */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('astra-parent', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('astra-child',  get_stylesheet_uri(), ['astra-parent'], '1.0.0');
    wp_enqueue_style('gps-header',   get_stylesheet_directory_uri() . '/assets/css/header.css', [], '1.0.0');

    wp_localize_script('wp-api-fetch', 'gpsConfig', [
        'clientPortalUrl' => GPS_CLIENT_PORTAL_URL . '/app',
        'restUrl'         => rest_url('gps/v1/contact'),
        'nonce'           => wp_create_nonce('wp_rest'),
    ]);
});

/* ──────────────────────────────────────────────
   Title tag: formato "Página | devspace"
────────────────────────────────────────────── */
add_filter('document_title_parts', function (array $parts): array {
    if (is_front_page()) {
        $parts['title']   = 'devspace';
        $parts['tagline'] = 'Sites, Apps e Sistemas para o seu negócio';
        unset($parts['site']);
    }
    return $parts;
});

add_filter('document_title_separator', fn() => '|');

/* ──────────────────────────────────────────────
   Logo: alt text descritivo
────────────────────────────────────────────── */
add_filter('get_custom_logo', function (string $html): string {
    return str_replace('alt=""', 'alt="devspace — Desenvolvimento de Sites, Apps e Sistemas"', $html);
});

/* ──────────────────────────────────────────────
   SEO: Meta description, Open Graph,
        Twitter Card e Canonical
────────────────────────────────────────────── */
add_action('wp_head', function () {
    global $post;

    $site_name = 'devspace';
    $og_image  = get_stylesheet_directory_uri() . '/assets/images/og-image.jpg';

    $meta = [
        'home'      => [
            'title'       => 'devspace | Sites, Apps e Sistemas para o seu negócio',
            'description' => 'Criamos sites, aplicativos iOS/Android e sistemas web sob medida para empresas de todos os portes. Tecnologia moderna, entrega no prazo e suporte dedicado.',
        ],
        'servicos'  => [
            'title'       => 'Serviços | devspace',
            'description' => 'Desenvolvimento de sites profissionais, apps mobile para iOS e Android, e sistemas web personalizados. Conheça as soluções digitais da devspace.',
        ],
        'portfolio' => [
            'title'       => 'Portfólio | devspace',
            'description' => 'Conheça os projetos que entregamos: sites, aplicativos mobile e sistemas de gestão para empresas de diferentes segmentos.',
        ],
        'contato'   => [
            'title'       => 'Contato | devspace',
            'description' => 'Entre em contato com a devspace. Solicite um orçamento ou suporte técnico por e-mail, WhatsApp ou pelo nosso formulário.',
        ],
    ];

    $slug   = is_front_page() ? 'home' : ($post ? get_post_field('post_name', $post) : '');
    $page   = $meta[$slug] ?? null;
    $title  = $page ? $page['title'] : (get_the_title() . ' | ' . $site_name);
    $desc   = $page ? $page['description'] : get_bloginfo('description');
    $url    = is_singular() ? get_permalink() : home_url('/');
    $type   = is_singular() && !is_front_page() ? 'article' : 'website';

    echo "\n<!-- SEO: devspace -->\n";
    echo '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";
    echo '<link rel="canonical" href="' . esc_url($url) . '">' . "\n";

    // Open Graph
    echo '<meta property="og:type" content="' . esc_attr($type) . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($desc) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr($site_name) . '">' . "\n";
    echo '<meta property="og:image" content="' . esc_url($og_image) . '">' . "\n";
    echo '<meta property="og:image:width" content="1200">' . "\n";
    echo '<meta property="og:image:height" content="630">' . "\n";
    echo '<meta property="og:locale" content="pt_BR">' . "\n";

    // Twitter Card
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($desc) . '">' . "\n";
    echo '<meta name="twitter:image" content="' . esc_url($og_image) . '">' . "\n";
    echo "<!-- /SEO -->\n\n";
}, 1);

/* ──────────────────────────────────────────────
   Schema.org JSON-LD
   Organization (todas as páginas) +
   WebPage específico por página
────────────────────────────────────────────── */
add_action('wp_head', function () {
    global $post;

    $site_url  = home_url('/');
    $logo_url  = get_stylesheet_directory_uri() . '/assets/images/logo.png';

    // Organization (global)
    $org = [
        '@context'     => 'https://schema.org',
        '@type'        => 'Organization',
        'name'         => 'devspace',
        'url'          => $site_url,
        'logo'         => [
            '@type' => 'ImageObject',
            'url'   => $logo_url,
        ],
        'description'  => 'Agência de desenvolvimento de sites, aplicativos mobile e sistemas web sob medida.',
        'contactPoint' => [
            [
                '@type'             => 'ContactPoint',
                'contactType'       => 'sales',
                'email'             => 'comercial@devspace.com.br',
                'availableLanguage' => 'Portuguese',
            ],
            [
                '@type'             => 'ContactPoint',
                'contactType'       => 'customer support',
                'email'             => 'suporte@devspace.com.br',
                'availableLanguage' => 'Portuguese',
            ],
        ],
        'sameAs' => [],
    ];

    echo '<script type="application/ld+json">'
        . wp_json_encode($org, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        . '</script>' . "\n";

    // WebPage específico por slug
    $slug = is_front_page() ? 'home' : ($post ? get_post_field('post_name', $post) : '');

    $service_schema = null;

    if ($slug === 'servicos') {
        $service_schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'ItemList',
            'name'     => 'Serviços devspace',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Desenvolvimento de Sites',     'url' => $site_url . 'servicos#sites'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Desenvolvimento de Aplicativos', 'url' => $site_url . 'servicos#apps'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => 'Desenvolvimento de Sistemas',   'url' => $site_url . 'servicos#sistemas'],
            ],
        ];
    }

    if ($slug === 'contato') {
        $service_schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'ContactPage',
            'name'     => 'Fale com a devspace',
            'url'      => $site_url . 'contato',
            'mainEntity' => [
                '@type'       => 'Organization',
                'name'        => 'devspace',
                'email'       => 'comercial@devspace.com.br',
                'telephone'   => '+55-11-99999-9999', // substitua pelo número real
            ],
        ];
    }

    if ($slug === 'home') {
        $service_schema = [
            '@context'        => 'https://schema.org',
            '@type'           => 'WebSite',
            'name'            => 'devspace',
            'url'             => $site_url,
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => $site_url . '?s={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    if ($service_schema) {
        echo '<script type="application/ld+json">'
            . wp_json_encode($service_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            . '</script>' . "\n";
    }
}, 10);

/* ──────────────────────────────────────────────
   Includes
────────────────────────────────────────────── */
require_once get_stylesheet_directory() . '/inc/rest-contact.php';
require_once get_stylesheet_directory() . '/inc/contact-shortcode.php';
require_once get_stylesheet_directory() . '/inc/cookie-banner.php';
