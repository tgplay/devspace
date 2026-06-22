<?php
defined('ABSPATH') || exit;

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('kava-parent', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('kava-child',  get_stylesheet_uri(), ['kava-parent'], '1.0.0');
    // Fontes self-hosted — sem requisição externa
    wp_enqueue_style('vm-fonts',    get_stylesheet_directory_uri() . '/assets/css/fonts.css', [], '1.0.0');
    wp_enqueue_style('vm-main',     get_stylesheet_directory_uri() . '/assets/css/main.css',  ['vm-fonts'], '1.0.1');
}, 20);
