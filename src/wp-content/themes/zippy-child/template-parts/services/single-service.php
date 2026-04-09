<?php
if ( ! defined('ABSPATH') ) exit;

$archive_url = get_post_type_archive_link('services');

while ( have_posts() ) : the_post();
    $service_id = get_the_ID();
    $meta = zippy_get_service_meta($service_id);
    $button_url = $meta['button_url'] ?: home_url('/contact-us/');
    $image_url = get_the_post_thumbnail_url($service_id, 'full');
    $service_terms = get_the_terms($service_id, 'services_category');
    $banner_image_url = $image_url;

    if ( ! $banner_image_url && ! empty($service_terms) && ! is_wp_error($service_terms) ) {
        $banner_image_url = zippy_get_services_category_image_url($service_terms[0]->term_id, 'full');
    }

    if ( ! $banner_image_url ) {
        $banner_image_url = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1600 420'%3E%3Crect width='1600' height='420' fill='%23292929'/%3E%3C/svg%3E";
    }

    if ( ! $image_url ) {
        $image_url = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 900'%3E%3Crect width='1200' height='900' fill='%23f5f5f5'/%3E%3C/svg%3E";
    }
?>
<section class="zippy-archive-banner" style="background-image:url('<?php echo esc_url($banner_image_url); ?>');">
    <div class="zippy-archive-banner__overlay"></div>
    <div class="zippy-archive-banner__inner">
        <div class="zippy-archive-banner__eyebrow"><?php esc_html_e('Service Detail', 'flatsome-child'); ?></div>
        <h1 class="zippy-archive-banner__title"><?php the_title(); ?></h1>

        <nav class="zippy-breadcrumb center-breadcrumb zippy-archive-banner__breadcrumb" aria-label="<?php esc_attr_e('Breadcrumb', 'flatsome-child'); ?>">
            <ol class="zippy-breadcrumb__list">
                <li class="zippy-breadcrumb__item">
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'flatsome-child'); ?></a>
                </li>
                <li class="zippy-breadcrumb__item">
                    <span class="zippy-breadcrumb__sep">/</span>
                    <a href="<?php echo esc_url($archive_url); ?>"><?php esc_html_e('Our Services', 'flatsome-child'); ?></a>
                </li>
                <li class="zippy-breadcrumb__item zippy-breadcrumb__item--active">
                    <span class="zippy-breadcrumb__sep">/</span>
                    <span><?php the_title(); ?></span>
                </li>
            </ol>
        </nav>
    </div>
</section>

<section class="zippy-service-single">
    <div class="zippy-service-single__inner">
        <article class="zippy-service-panel zippy-service-panel--single">
            <div class="zippy-service-panel__media">
                <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>" />
            </div>

            <div class="zippy-service-panel__content">
                <h2 class="zippy-service-panel__title"><?php the_title(); ?></h2>

                <div class="zippy-service-panel__description zippy-service-panel__description--full">
                    <?php the_content(); ?>
                </div>

                <div class="zippy-service-panel__actions">
                    <a class="zippy-service-panel__button" href="<?php echo esc_url($button_url); ?>">
                        <?php esc_html_e('Contact us now', 'flatsome-child'); ?>
                    </a>
                </div>
            </div>
        </article>
    </div>
</section>
<?php endwhile; ?>
