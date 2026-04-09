<?php
if ( ! defined('ABSPATH') ) exit;

add_action('customize_register', function ( $wp_customize ) {
    $wp_customize->add_section('zippy_archive_banners', [
        'title'       => __('Archive Banners', 'flatsome-child'),
        'priority'    => 160,
        'description' => __('Set default banner images for archive pages.', 'flatsome-child'),
    ]);

    $wp_customize->add_setting('zippy_projects_archive_banner_image', [
        'default'           => 0,
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'zippy_projects_archive_banner_image', [
        'label'      => __('Projects Archive Banner', 'flatsome-child'),
        'section'    => 'zippy_archive_banners',
        'mime_type'  => 'image',
    ]));

    $wp_customize->add_setting('zippy_services_archive_banner_image', [
        'default'           => 0,
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'zippy_services_archive_banner_image', [
        'label'      => __('Services Archive Banner', 'flatsome-child'),
        'section'    => 'zippy_archive_banners',
        'mime_type'  => 'image',
    ]));
});

function zippy_get_theme_mod_image_url( $theme_mod_key, $size = 'full' ) {
    $image_id = (int) get_theme_mod($theme_mod_key, 0);

    if ( ! $image_id ) {
        return '';
    }

    return wp_get_attachment_image_url($image_id, $size) ?: '';
}
