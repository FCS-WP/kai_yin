<?php
if ( ! defined('ABSPATH') ) exit;

$current_term = is_tax('services_category') ? get_queried_object() : null;
$archive_title = $current_term ? single_term_title('', false) : __('Our Services', 'flatsome-child');
$archive_banner_url = '';
$archive_eyebrow = $current_term ? __('Service Category', 'flatsome-child') : __('What We Offer', 'flatsome-child');
$archive_description = '';

if ( $current_term && ! empty($current_term->term_id) ) {
    $archive_banner_url = zippy_get_services_category_image_url($current_term->term_id, 'full');
    $archive_description = term_description($current_term->term_id, 'services_category');
}

if ( ! $archive_banner_url ) {
    $archive_banner_url = zippy_get_theme_mod_image_url('zippy_services_archive_banner_image', 'full');
}

if ( ! $archive_banner_url ) {
    $banner_posts = get_posts([
        'post_type'      => 'services',
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
        'fields'         => 'ids',
    ]);

    if ( ! empty($banner_posts[0]) ) {
        $archive_banner_url = get_the_post_thumbnail_url($banner_posts[0], 'full');
    }
}

if ( ! $archive_banner_url ) {
    $archive_banner_url = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1600 420'%3E%3Crect width='1600' height='420' fill='%23292929'/%3E%3C/svg%3E";
}
?>

<section class="zippy-archive-banner" style="background-image:url('<?php echo esc_url($archive_banner_url); ?>');">
    <div class="zippy-archive-banner__overlay"></div>
    <div class="zippy-archive-banner__inner">
        <div class="zippy-archive-banner__eyebrow"><?php echo esc_html($archive_eyebrow); ?></div>
        <h1 class="zippy-archive-banner__title"><?php echo esc_html($archive_title); ?></h1>

        <nav class="zippy-breadcrumb center-breadcrumb zippy-archive-banner__breadcrumb" aria-label="<?php esc_attr_e('Breadcrumb', 'flatsome-child'); ?>">
            <ol class="zippy-breadcrumb__list">
                <li class="zippy-breadcrumb__item">
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'flatsome-child'); ?></a>
                </li>
                <li class="zippy-breadcrumb__item">
                    <span class="zippy-breadcrumb__sep">/</span>
                    <?php if ( $current_term ) : ?>
                        <a href="<?php echo esc_url(get_post_type_archive_link('services')); ?>"><?php esc_html_e('Our Services', 'flatsome-child'); ?></a>
                    <?php else : ?>
                        <span><?php echo esc_html($archive_title); ?></span>
                    <?php endif; ?>
                </li>
                <?php if ( $current_term ) : ?>
                <li class="zippy-breadcrumb__item zippy-breadcrumb__item--active">
                    <span class="zippy-breadcrumb__sep">/</span>
                    <span><?php echo esc_html($archive_title); ?></span>
                </li>
                <?php endif; ?>
            </ol>
        </nav>

        <?php if ( ! empty($archive_description) ) : ?>
        <div class="zippy-archive-banner__description">
            <?php echo wp_kses_post(wpautop($archive_description)); ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="zippy-services-archive">
    <div class="zippy-services-archive__inner">
        <?php if ( have_posts() ) : ?>
            <?php while ( have_posts() ) : the_post(); ?>
                <?php
                $service_id = get_the_ID();
                $meta = zippy_get_service_meta($service_id);
                $button_url = $meta['button_url'] ?: home_url('/contact-us/');
                $image_url = get_the_post_thumbnail_url($service_id, 'large');

                if ( ! $image_url ) {
                    $image_url = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 900 700'%3E%3Crect width='900' height='700' fill='%23f5f5f5'/%3E%3C/svg%3E";
                }
                ?>
                <article class="zippy-service-panel">
                    <a class="zippy-service-panel__media" href="<?php the_permalink(); ?>">
                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>" />
                    </a>

                    <div class="zippy-service-panel__content">
                        <h2 class="zippy-service-panel__title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>

                        <div class="zippy-service-panel__description">
                            <?php
                            $summary = get_the_excerpt();
                            if ( empty($summary) ) {
                                $summary = wp_trim_words(wp_strip_all_tags(get_the_content()), 42);
                            }
                            echo wpautop(esc_html($summary));
                            ?>
                        </div>

                        <div class="zippy-service-panel__actions">
                            <a class="zippy-service-panel__button" href="<?php echo esc_url($button_url); ?>">
                                <?php esc_html_e('Contact us now', 'flatsome-child'); ?>
                            </a>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
        <?php else : ?>
            <p class="zippy-services-empty"><?php esc_html_e('No services found.', 'flatsome-child'); ?></p>
        <?php endif; ?>
    </div>
</section>
