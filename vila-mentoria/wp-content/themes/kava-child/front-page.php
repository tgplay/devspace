<?php
defined('ABSPATH') || exit;

$uploads = content_url('uploads');

$varanda_query = new WP_Query([
    'post_type'      => 'post',
    'posts_per_page' => 4,
    'tax_query'      => [['taxonomy' => 'category', 'field' => 'slug', 'terms' => 'na-varanda']],
]);
$varanda_posts = [];
while ($varanda_query->have_posts()) : $varanda_query->the_post();
    $varanda_posts[] = [
        'permalink'   => get_permalink(),
        'title'       => get_the_title(),
        'date'        => get_the_date('d M Y'),
        'thumb_large' => get_the_post_thumbnail('medium_large'),
        'thumb_small' => get_the_post_thumbnail('thumbnail'),
        'has_thumb'   => has_post_thumbnail(),
    ];
endwhile;
wp_reset_postdata();

$artigos_query = new WP_Query([
    'post_type'      => 'post',
    'posts_per_page' => 3,
    'tax_query'      => [['taxonomy' => 'category', 'field' => 'slug', 'terms' => ['revista-ag', 'revista-perfil'], 'operator' => 'IN']],
]);
$artigos_posts = [];
while ($artigos_query->have_posts()) : $artigos_query->the_post();
    $cats = get_the_category();
    $artigos_posts[] = [
        'permalink'   => get_permalink(),
        'title'       => get_the_title(),
        'date'        => get_the_date('d M Y'),
        'thumb_large' => get_the_post_thumbnail('large'),
        'thumb_small' => get_the_post_thumbnail('medium'),
        'has_thumb'   => has_post_thumbnail(),
        'cat'         => $cats ? $cats[0]->name : '',
    ];
endwhile;
wp_reset_postdata();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class('vm-home'); ?>>
<?php wp_body_open(); ?>

<!-- ══════════════════════════════════════ HEADER ══ -->
<header class="vm-header" id="vm-site-header">
    <div class="vm-topbar">
        <div class="vm-topbar-inner">
            <span>Consultoria Internacional &middot; Palestrante &middot; Produtor Rural</span>
            <div class="vm-topbar-social">
                <a href="https://www.instagram.com/professorfranciscovila/" target="_blank" rel="noopener" title="Instagram">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".5" fill="currentColor"/></svg>
                </a>
                <a href="https://www.youtube.com/@navaranda" target="_blank" rel="noopener" title="YouTube">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M21.8 8s-.2-1.4-.8-2c-.8-.8-1.7-.8-2.1-.9C16.2 5 12 5 12 5s-4.2 0-6.9.1c-.4.1-1.3.1-2.1.9-.6.6-.8 2-.8 2S2 9.6 2 11.2v1.6c0 1.6.2 3.2.2 3.2s.2 1.4.8 2c.8.8 1.8.8 2.3.9C6.8 19 12 19 12 19s4.2 0 6.9-.2c.4 0 1.3-.1 2.1-.9.6-.6.8-2 .8-2s.2-1.6.2-3.2v-1.6C22 9.6 21.8 8 21.8 8zM10 15V9l5.2 3-5.2 3z"/></svg>
                </a>
            </div>
        </div>
    </div>
    <div class="vm-header-inner">
        <a class="vm-logo" href="<?= home_url('/') ?>">
            <img src="<?= $uploads ?>/2024/10/logo-vila-mentoria-removebg-preview.png" alt="Vila Mentoria">
        </a>
        <nav class="vm-nav" id="vm-nav">
            <ul>
                <li><a href="<?= home_url('/#sobre') ?>">Sobre</a></li>
                <li class="has-submenu">
                    <a href="<?= home_url('/artigos/') ?>">Artigos</a>
                    <ul class="sub-menu">
                        <li><a href="<?= home_url('/artigos/#revista-perfil') ?>">Revista Perfil</a></li>
                        <li><a href="<?= home_url('/artigos/#revista-ag') ?>">Revista AG</a></li>
                        <li><a href="<?= home_url('/artigos/#outros') ?>">Outros</a></li>
                    </ul>
                </li>
                <li><a href="<?= home_url('/na-varanda/') ?>">Na Varanda</a></li>
                <li><a href="<?= home_url('/category/conversa-da-semana/') ?>">Conversa da Semana</a></li>
                <li><a href="<?= home_url('/#contato') ?>">Contato</a></li>
                <li class="vm-nav-cta"><a href="<?= home_url('/mentoria/') ?>">Mentoria</a></li>
            </ul>
        </nav>
        <button class="vm-hamburger" id="vm-hamburger" aria-label="Menu" type="button">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>

<!-- ══════════════════════════════════════ HERO ══ -->
<section class="vm-hero">
    <img class="vm-hero-photo" src="<?= $uploads ?>/2024/10/vila.jpeg" alt="Professor Francisco Vila">
    <div class="vm-hero-overlay"></div>
    <div class="vm-hero-content">
        <div class="vm-container">
            <span class="vm-hero-eyebrow">Professor Francisco Vila</span>
            <img class="vm-hero-title-img"
                 src="<?= $uploads ?>/2024/10/Aprender_com_quem_ja_fez__1_-removebg-preview-1.png"
                 alt="Na varanda com o Vila">
            <p class="vm-hero-sub">Consultoria Internacional &middot; Palestrante &middot; Produtor Rural</p>
            <div class="vm-hero-ctas">
                <a href="<?= home_url('/mentoria/') ?>" class="vm-btn">Conheça a Mentoria</a>
                <a href="<?= home_url('/na-varanda/') ?>" class="vm-btn-outline">Na Varanda com o Vila</a>
            </div>
            <div class="vm-hero-dots">
                <span class="active"></span><span></span><span></span>
                <em class="vm-hero-num">01</em>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════ SOBRE ══ -->
<section class="vm-sobre" id="sobre">
    <div class="vm-container">
        <div class="vm-sobre-grid">

            <div class="vm-sobre-text">
                <span class="vm-section-label">Sobre</span>
                <div class="vm-gold-line"></div>
                <h2 class="vm-sobre-heading">Professor<br>Francisco Vila</h2>
                <p>Formado em Economia na Alemanha, Administração e Gestão de Projetos nos EUA, com 15 anos de experiência como docente em cursos de pós-graduação em Portugal e Alemanha. Atua como consultor internacional para governos, empresas, bancos e instituições multilaterais em diversos países.</p>
                <p>Pesquisador de modelos de gestão para a pecuária brasileira e consultor especializado em sucessão familiar. Além disso, é palestrante, comentarista, colunista e apresentador do programa semanal de vídeo "Na Varanda com o Vila".</p>
                <p>É também conselheiro da COSAG/FIESP, da Sociedade Rural Brasileira e da Associação Novilho Precoce.</p>

                <div class="vm-sobre-cards">
                    <a href="<?= $uploads ?>/2024/11/cv-vila.jpg" target="_blank" class="vm-sobre-card">
                        <img src="<?= $uploads ?>/2024/11/cv-vila.jpg" alt="Currículo">
                        <div class="vm-sobre-card-label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            Currículo
                        </div>
                    </a>
                    <a href="<?= $uploads ?>/2024/11/Francisco-Vila-Biografia-Intro-Ingles_page-0001.jpg" target="_blank" class="vm-sobre-card">
                        <img src="<?= $uploads ?>/2024/11/Francisco-Vila-Biografia-Intro-Ingles_page-0001.jpg" alt="Biografia">
                        <div class="vm-sobre-card-label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            Biografia
                        </div>
                    </a>
                    <a href="#" class="vm-sobre-card">
                        <img src="<?= $uploads ?>/2024/11/bf1.jpg" alt="Ovelha Buffet">
                        <div class="vm-sobre-card-label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            Ovelha Buffet
                        </div>
                    </a>
                </div>
            </div>

            <div class="vm-sobre-sidebar">
                <div class="vm-sobre-photo-wrap">
                    <img src="<?= $uploads ?>/2024/11/WhatsApp-Image-2024-11-01-at-18.56.58.jpeg" alt="Professor Francisco Vila">
                </div>
                <div class="vm-youtube-wrap">
                    <iframe src="https://www.youtube.com/embed/UW0nTqxYJig"
                            title="Criação Positiva"
                            loading="lazy"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen></iframe>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ══════════════════════════════════════ ARTIGOS ══ -->
<section class="vm-artigos" id="artigos">
    <div class="vm-container">

        <div class="vm-artigos-top">
            <div>
                <span class="vm-section-label">Artigos</span>
                <div class="vm-gold-line"></div>
            </div>
            <a href="<?= home_url('/artigos/') ?>" class="vm-link-arrow">Ver todos os artigos &rarr;</a>
        </div>

        <?php if (!empty($artigos_posts)) :
            $art_featured = $artigos_posts[0];
            $art_sides    = array_slice($artigos_posts, 1);
        ?>
        <div class="vm-artigos-editorial">

            <a href="<?= esc_url($art_featured['permalink']) ?>" class="vm-artigo-featured">
                <div class="vm-artigo-featured-img">
                    <?php if ($art_featured['has_thumb']) : ?>
                        <?= $art_featured['thumb_large'] ?>
                    <?php else : ?>
                        <div class="vm-artigo-no-thumb"></div>
                    <?php endif; ?>
                    <div class="vm-artigo-featured-overlay"></div>
                </div>
                <div class="vm-artigo-featured-body">
                    <?php if ($art_featured['cat']) : ?>
                        <span class="vm-artigo-cat"><?= esc_html($art_featured['cat']) ?></span>
                    <?php endif; ?>
                    <h3><?= esc_html($art_featured['title']) ?></h3>
                    <div class="vm-artigo-featured-meta">
                        <span><?= esc_html($art_featured['date']) ?></span>
                        <span class="vm-artigo-featured-cta">Ler artigo &rarr;</span>
                    </div>
                </div>
            </a>

            <div class="vm-artigos-side">
                <?php foreach ($art_sides as $art) : ?>
                <a href="<?= esc_url($art['permalink']) ?>" class="vm-artigo-side-card">
                    <div class="vm-artigo-side-thumb">
                        <?php if ($art['has_thumb']) : ?>
                            <?= $art['thumb_small'] ?>
                        <?php else : ?>
                            <div class="vm-artigo-no-thumb"></div>
                        <?php endif; ?>
                    </div>
                    <div class="vm-artigo-side-body">
                        <?php if ($art['cat']) : ?>
                            <span class="vm-artigo-cat-sm"><?= esc_html($art['cat']) ?></span>
                        <?php endif; ?>
                        <h4><?= esc_html($art['title']) ?></h4>
                        <span class="vm-artigo-date"><?= esc_html($art['date']) ?></span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

        </div>
        <?php endif; ?>

        <div class="vm-artigos-footer">
            <div class="vm-destaques">
                <h3>Destaques</h3>
                <div class="vm-destaques-line"></div>
                <p>70 artigos na Revista AG &middot; 50 artigos na Revista PERFIL &middot; Coluna Francisco Vila — entrevistas com famílias do campo.</p>
            </div>
            <aside class="vm-commodities">
                <h3>Commodities Agrícolas</h3>
                <div class="vm-commodities-line"></div>
                <iframe src="https://sslecal2.investing.com/widgets/economicCalendar.html?columns=exc_flags,exc_currency,exc_importance,exc_actual,exc_forecast,exc_previous&features=datepicker,timezone&countries=76&calType=week&timeZone=12&lang=12"
                        height="300"
                        loading="lazy"
                        title="Commodities Agrícolas">
                </iframe>
            </aside>
        </div>

    </div>
</section>

<!-- ══════════════════════════════════════ NA VARANDA ══ -->
<section class="vm-varanda" id="na-varanda">
    <div class="vm-container">
        <div class="vm-varanda-header">
            <div>
                <span class="vm-section-label">Programa Semanal</span>
                <div class="vm-gold-line"></div>
                <h2>Na Varanda com o Vila</h2>
                <p class="vm-varanda-sub">Aprender com quem já fez.</p>
            </div>
            <a href="<?= home_url('/na-varanda/') ?>" class="vm-btn">Ver Todos os Episódios</a>
        </div>

        <?php if (!empty($varanda_posts)) :
            $ep_featured = $varanda_posts[0];
            $ep_list     = array_slice($varanda_posts, 1, 3);
            $fallback    = $uploads . '/2024/12/na-varanda-1.0.png';
        ?>
        <div class="vm-varanda-layout">

            <a href="<?= esc_url($ep_featured['permalink']) ?>" class="vm-varanda-featured">
                <div class="vm-varanda-featured-img">
                    <?php if ($ep_featured['has_thumb']) : ?>
                        <?= $ep_featured['thumb_large'] ?>
                    <?php else : ?>
                        <img src="<?= $fallback ?>" alt="Na Varanda com o Vila">
                    <?php endif; ?>
                    <div class="vm-varanda-featured-overlay"></div>
                </div>
                <div class="vm-varanda-featured-play">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                </div>
                <div class="vm-varanda-featured-body">
                    <span class="vm-varanda-date"><?= esc_html($ep_featured['date']) ?></span>
                    <h3><?= esc_html($ep_featured['title']) ?></h3>
                </div>
            </a>

            <div class="vm-varanda-list">
                <?php foreach ($ep_list as $ep) : ?>
                <a href="<?= esc_url($ep['permalink']) ?>" class="vm-varanda-list-item">
                    <div class="vm-varanda-list-thumb">
                        <?php if ($ep['has_thumb']) : ?>
                            <?= $ep['thumb_small'] ?>
                        <?php else : ?>
                            <img src="<?= $fallback ?>" alt="Na Varanda">
                        <?php endif; ?>
                        <div class="vm-varanda-list-play">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                        </div>
                    </div>
                    <div class="vm-varanda-list-body">
                        <span class="vm-varanda-date"><?= esc_html($ep['date']) ?></span>
                        <h4><?= esc_html($ep['title']) ?></h4>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ══════════════════════════════════════ CONTATO ══ -->
<section class="vm-contato" id="contato">
    <div class="vm-container">
        <div class="vm-contato-layout">

            <div class="vm-contato-left">
                <span class="vm-section-label">Entre em Contato</span>
                <div class="vm-gold-line"></div>
                <h2>Fale conosco</h2>
                <p class="vm-contato-desc">Tem interesse em palestras, mentorias ou consultorias? Envie uma mensagem e retornaremos em breve.</p>

                <div class="vm-contato-social">
                    <a class="vm-social-item" href="https://www.instagram.com/professorfranciscovila/" target="_blank" rel="noopener">
                        <div class="vm-social-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".5" fill="currentColor"/></svg>
                        </div>
                        <div class="vm-social-info">
                            <strong>Instagram</strong>
                            <span>@professorfranciscovila</span>
                        </div>
                    </a>
                    <a class="vm-social-item" href="https://www.youtube.com/@navaranda" target="_blank" rel="noopener">
                        <div class="vm-social-icon">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M21.8 8s-.2-1.4-.8-2c-.8-.8-1.7-.8-2.1-.9C16.2 5 12 5 12 5s-4.2 0-6.9.1c-.4.1-1.3.1-2.1.9-.6.6-.8 2-.8 2S2 9.6 2 11.2v1.6c0 1.6.2 3.2.2 3.2s.2 1.4.8 2c.8.8 1.8.8 2.3.9C6.8 19 12 19 12 19s4.2 0 6.9-.2c.4 0 1.3-.1 2.1-.9.6-.6.8-2 .8-2s.2-1.6.2-3.2v-1.6C22 9.6 21.8 8 21.8 8zM10 15V9l5.2 3-5.2 3z"/></svg>
                        </div>
                        <div class="vm-social-info">
                            <strong>YouTube</strong>
                            <span>Na Varanda com o Vila</span>
                        </div>
                    </a>
                    <a class="vm-social-item" href="https://wa.me/5511999999999" target="_blank" rel="noopener">
                        <div class="vm-social-icon">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.5 14.4c-.3-.1-1.7-.8-1.9-.9-.3-.1-.5-.1-.7.1-.2.3-.7.9-.9 1.1-.2.2-.3.2-.6.1-1.6-.8-2.6-1.4-3.7-3.2-.3-.5.3-.5.9-1.5.1-.2 0-.4-.1-.5-.1-.1-.7-1.6-.9-2.2-.2-.6-.5-.5-.7-.5H8c-.2 0-.5.1-.8.4C6.9 7.5 6 8.5 6 10.4c0 1.9 1.4 3.8 1.6 4 .2.3 2.7 4.1 6.5 5.8 2.4 1 3.4 1.1 4.6.9.7-.1 2.3-.9 2.6-1.8.3-.9.3-1.6.2-1.8-.1-.2-.3-.3-.5-.4z"/><path d="M12 2C6.5 2 2 6.5 2 12c0 1.9.5 3.7 1.4 5.3L2 22l4.8-1.3C8.3 21.5 10.1 22 12 22c5.5 0 10-4.5 10-10S17.5 2 12 2zm0 18c-1.7 0-3.4-.5-4.8-1.3l-.3-.2-3.2.8.9-3.1-.2-.3C3.5 14.5 3 13.3 3 12 3 7 7 3 12 3s9 4 9 9-4 9-9 9z"/></svg>
                        </div>
                        <div class="vm-social-info">
                            <strong>WhatsApp</strong>
                            <span>Fale pelo WhatsApp</span>
                        </div>
                    </a>
                </div>
            </div>

            <div class="vm-contato-right">
                <form class="vm-form" method="post" action="">
                    <?php wp_nonce_field('vm_contato', 'vm_nonce'); ?>
                    <div class="vm-form-row">
                        <div class="vm-field">
                            <label>Nome *</label>
                            <input type="text" name="vm_nome" placeholder="Seu nome completo" required>
                        </div>
                        <div class="vm-field">
                            <label>E-mail *</label>
                            <input type="email" name="vm_email" placeholder="seu@email.com" required>
                        </div>
                    </div>
                    <div class="vm-form-row">
                        <div class="vm-field">
                            <label>Telefone</label>
                            <input type="tel" name="vm_telefone" placeholder="(11) 99999-9999">
                        </div>
                        <div class="vm-field">
                            <label>Assunto</label>
                            <input type="text" name="vm_assunto" placeholder="Assunto da mensagem">
                        </div>
                    </div>
                    <div class="vm-field vm-form-full">
                        <label>Mensagem *</label>
                        <textarea name="vm_mensagem" placeholder="Escreva sua mensagem aqui..." required></textarea>
                    </div>
                    <div class="vm-form-submit">
                        <button type="submit">Enviar Mensagem</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</section>

<!-- ══════════════════════════════════════ FOOTER ══ -->
<footer class="vm-footer">
    <div class="vm-footer-inner">
        <div class="vm-footer-brand">
            <img src="<?= $uploads ?>/2024/10/logo-vila-mentoria-removebg-preview.png" alt="Vila Mentoria">
            <p>Aprender com quem já fez.</p>
        </div>
        <div class="vm-footer-links">
            <h4>Navegação</h4>
            <ul>
                <li><a href="<?= home_url('/#sobre') ?>">Sobre</a></li>
                <li><a href="<?= home_url('/mentoria/') ?>">Mentoria</a></li>
                <li><a href="<?= home_url('/na-varanda/') ?>">Na Varanda</a></li>
                <li><a href="<?= home_url('/artigos/') ?>">Artigos</a></li>
                <li><a href="<?= home_url('/category/conversa-da-semana/') ?>">Conversa da Semana</a></li>
                <li><a href="<?= home_url('/#contato') ?>">Contato</a></li>
            </ul>
        </div>
        <div class="vm-footer-social-col">
            <h4>Redes Sociais</h4>
            <div class="vm-footer-social">
                <a href="https://www.instagram.com/professorfranciscovila/" target="_blank" rel="noopener">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".5" fill="currentColor"/></svg>
                    <span>@professorfranciscovila</span>
                </a>
                <a href="https://www.youtube.com/@navaranda" target="_blank" rel="noopener">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M21.8 8s-.2-1.4-.8-2c-.8-.8-1.7-.8-2.1-.9C16.2 5 12 5 12 5s-4.2 0-6.9.1c-.4.1-1.3.1-2.1.9-.6.6-.8 2-.8 2S2 9.6 2 11.2v1.6c0 1.6.2 3.2.2 3.2s.2 1.4.8 2c.8.8 1.8.8 2.3.9C6.8 19 12 19 12 19s4.2 0 6.9-.2c.4 0 1.3-.1 2.1-.9.6-.6.8-2 .8-2s.2-1.6.2-3.2v-1.6C22 9.6 21.8 8 21.8 8zM10 15V9l5.2 3-5.2 3z"/></svg>
                    <span>Na Varanda com o Vila</span>
                </a>
                <a href="https://wa.me/5511999999999" target="_blank" rel="noopener">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.5 14.4c-.3-.1-1.7-.8-1.9-.9-.3-.1-.5-.1-.7.1-.2.3-.7.9-.9 1.1-.2.2-.3.2-.6.1-1.6-.8-2.6-1.4-3.7-3.2-.3-.5.3-.5.9-1.5.1-.2 0-.4-.1-.5-.1-.1-.7-1.6-.9-2.2-.2-.6-.5-.5-.7-.5H8c-.2 0-.5.1-.8.4C6.9 7.5 6 8.5 6 10.4c0 1.9 1.4 3.8 1.6 4 .2.3 2.7 4.1 6.5 5.8 2.4 1 3.4 1.1 4.6.9.7-.1 2.3-.9 2.6-1.8.3-.9.3-1.6.2-1.8-.1-.2-.3-.3-.5-.4z"/><path d="M12 2C6.5 2 2 6.5 2 12c0 1.9.5 3.7 1.4 5.3L2 22l4.8-1.3C8.3 21.5 10.1 22 12 22c5.5 0 10-4.5 10-10S17.5 2 12 2zm0 18c-1.7 0-3.4-.5-4.8-1.3l-.3-.2-3.2.8.9-3.1-.2-.3C3.5 14.5 3 13.3 3 12 3 7 7 3 12 3s9 4 9 9-4 9-9 9z"/></svg>
                    <span>WhatsApp</span>
                </a>
            </div>
        </div>
    </div>
    <div class="vm-footer-bottom">
        <div class="vm-container">
            Copyright &copy; <?= date('Y') ?> Vila Mentoria &nbsp;&mdash;&nbsp; Desenvolvido por <a href="https://agplay.com.br" target="_blank" rel="noopener">agplay</a>
        </div>
    </div>
</footer>

<script>
(function () {
    var header = document.getElementById('vm-site-header');
    var hamburger = document.getElementById('vm-hamburger');
    var nav = document.getElementById('vm-nav');

    window.addEventListener('scroll', function () {
        header.classList.toggle('scrolled', window.scrollY > 40);
    }, { passive: true });

    hamburger.addEventListener('click', function () {
        hamburger.classList.toggle('open');
        nav.classList.toggle('open');
    });
})();
</script>

<?php wp_footer(); ?>
</body>
</html>
