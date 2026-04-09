<?php
/**
 * Zippy Product Categories Slider
 *
 * - [zippy_product_categories]
 *
 * Displays WooCommerce product categories as a Swiper slider.
 * - Category image from WooCommerce thumbnail_id term meta
 * - Category name overlay at bottom
 * - Click → product category archive page
 * - External prev/next controls via .custom-prev-btn and .custom-next-btn
 *
 * Usage in functions.php:
 *   require_once get_stylesheet_directory() . '/inc/zippy-product-categories.php';
 */

if ( ! defined('ABSPATH') ) exit;

function zippy_get_product_category_terms( $atts ) {
    $include_slugs = [];
    $query_args = [
        'taxonomy'   => 'product_cat',
        'hide_empty' => $atts['hide_empty'] === 'true',
        'orderby'    => sanitize_key($atts['orderby']),
        'order'      => sanitize_key($atts['order']),
    ];

    if ( $atts['parent'] !== '-1' ) {
        $query_args['parent'] = (int) $atts['parent'];
    }

    if ( (int) $atts['limit'] > 0 ) {
        $query_args['number'] = (int) $atts['limit'];
    }
    

    if ( ! empty($atts['include']) ) {
        $include_slugs = array_values(array_filter(array_map('trim', explode(',', $atts['include']))));
        $query_args['slug'] = $include_slugs;
    }

    $terms = get_terms($query_args);

    if ( is_wp_error($terms) ) {
        return $terms;
    }

    if ( ! empty($atts['exclude']) ) {
        $exclude_slugs = array_map('trim', explode(',', $atts['exclude']));
        $terms = array_filter($terms, fn($term) => ! in_array($term->slug, $exclude_slugs, true));
    }

    $default_cat = get_option('default_product_cat');
    $terms = array_filter($terms, fn($term) => (int) $term->term_id !== (int) $default_cat);
    $terms = array_values($terms);

    if ( ! empty($include_slugs) ) {
        $slug_positions = array_flip($include_slugs);
        usort($terms, function( $left, $right ) use ( $slug_positions ) {
            $left_position = $slug_positions[$left->slug] ?? PHP_INT_MAX;
            $right_position = $slug_positions[$right->slug] ?? PHP_INT_MAX;

            return $left_position <=> $right_position;
        });
    }

    return $terms;
}

function zippy_get_product_category_card_data( $term, $fallback_image = '' ) {
    $thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);
    $image_url    = '';

    if ( $thumbnail_id ) {
        $image_url = wp_get_attachment_image_url($thumbnail_id, 'full');
    }

    if ( ! $image_url ) {
        $image_url = ! empty($fallback_image)
            ? esc_url($fallback_image)
            : wc_placeholder_img_src('woocommerce_single');
    }

    return [
        'url'   => get_term_link($term),
        'name'  => $term->name,
        'count' => (int) $term->count,
        'image' => $image_url,
    ];
}

function zippy_render_product_category_card( $term, $atts ) {
    $card = zippy_get_product_category_card_data($term, $atts['fallback_image']);

    if ( is_wp_error($card['url']) ) {
        return '';
    }

    ob_start();
    ?>
    <a
        href="<?php echo esc_url($card['url']); ?>"
        class="zippy-cat-card"
        style="height:<?php echo esc_attr($atts['image_height']); ?>;border-radius:<?php echo esc_attr($atts['border_radius']); ?>"
        aria-label="<?php echo esc_attr($card['name']); ?>"
    >
        <div
            class="zippy-cat-card__bg"
            style="background-image:url('<?php echo esc_url($card['image']); ?>')"
            role="img"
            aria-label="<?php echo esc_attr($card['name']); ?>"
        ></div>

        <div class="zippy-cat-card__overlay"></div>

        <div class="zippy-cat-card__label">
            <span class="zippy-cat-card__name"><?php echo esc_html($card['name']); ?></span>
            <?php if ( $atts['show_count'] === 'true' ) : ?>
            <span class="zippy-cat-card__count"><?php echo esc_html($card['count']); ?> products</span>
            <?php endif; ?>
        </div>
    </a>
    <?php

    return ob_get_clean();
}


// ============================================================
// [zippy_product_categories]
// ============================================================
function zippy_product_categories( $atts ) {
    $atts = shortcode_atts([
        // Query
        'parent'          => '0',        // 0 = top level only, -1 = all, or specific ID
        'include'         => '',         // comma-separated category slugs to include
        'exclude'         => '',         // comma-separated category slugs to exclude
        'orderby'         => 'name',     // name | count | slug | term_order | menu_order
        'order'           => 'ASC',
        'hide_empty'      => 'true',     // hide categories with no products
        'limit'           => '-1',       // -1 = all

        // Slider
        'slides_per_view'        => '4',
        'slides_per_view_tablet' => '3',
        'slides_per_view_mobile' => '2',
        'space_between'          => '20',
        'space_between_tablet'   => '16',
        'space_between_mobile'   => '12',
        'loop'                   => 'false',
        'autoplay'               => 'false',   // false | ms e.g. "3000"
        'free_mode'              => 'false',

        // Navigation — external button selectors
        'prev_btn'               => '.custom-prev-btn',   // CSS selector
        'next_btn'               => '.custom-next-btn',   // CSS selector

        // Card
        'image_height'    => '340px',
        'border_radius'   => '12px',
        'show_count'      => 'false',    // show product count on card

        // Fallback image if category has no image
        'fallback_image'  => '',

        'class'           => '',
    ], $atts, 'zippy_product_categories');


    if ( ! function_exists('WC') ) return '<p>WooCommerce is required.</p>';

    $terms = zippy_get_product_category_terms($atts);

    if ( empty($terms) || is_wp_error($terms) ) {
        return '<p class="zippy-cat-slider-empty">' . __('No categories found.', 'flatsome-child') . '</p>';
    }

    // ── Unique ID for this slider instance ──
    static $instance = 0;
    $instance++;
    $uid        = 'zippy-cat-slider-' . $instance;
    $wrapper_class = 'zippy-cat-slider' . ( $atts['class'] ? ' ' . esc_attr($atts['class']) : '' );

    // ── Swiper config ──
    $spv_tablet = $atts['slides_per_view_tablet'] ?: $atts['slides_per_view'];
    $spb_tablet = $atts['space_between_tablet']   ?: $atts['space_between'];

    $config = [
        // Mobile-first base
        'slidesPerView' => (float) $atts['slides_per_view_mobile'],
        'spaceBetween'  => (int)   $atts['space_between_mobile'],
        'loop'          => $atts['loop'] === 'true',
        'freeMode'      => $atts['free_mode'] === 'true',
        'grabCursor'    => true,
        'breakpoints'   => [
            '768'  => [
                'slidesPerView' => (float) $spv_tablet,
                'spaceBetween'  => (int)   $spb_tablet,
            ],
            '1024' => [
                'slidesPerView' => (float) $atts['slides_per_view'],
                'spaceBetween'  => (int)   $atts['space_between'],
            ],
        ],
        'navigation' => [
            'prevEl' => $atts['prev_btn'],
            'nextEl' => $atts['next_btn'],
        ],
    ];

    if ( $atts['autoplay'] !== 'false' ) {
        $config['autoplay'] = [
            'delay'                => (int) $atts['autoplay'],
            'disableOnInteraction' => false,
        ];
    }

    ob_start();
    ?>

    <div id="<?php echo esc_attr($uid); ?>" class="<?php echo esc_attr($wrapper_class); ?>">
        <div class="swiper">
            <div class="swiper-wrapper">

                <?php foreach ( $terms as $term ) : ?>
                <div class="swiper-slide">
                    <?php echo zippy_render_product_category_card($term, $atts); ?>
                </div>
                <?php endforeach; ?>

            </div>
        </div>
    </div>

    <script>
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            new Swiper('#<?php echo esc_js($uid); ?> .swiper', <?php echo json_encode($config); ?>);
        });
    })();
    </script>

    <?php
    return ob_get_clean();
}
add_shortcode('zippy_product_categories', 'zippy_product_categories');


// ============================================================
// [zippy_product_categories_grid]
// ============================================================
function zippy_product_categories_grid( $atts ) {
    $atts = shortcode_atts([
        'parent'          => '0',
        'include'         => '',
        'exclude'         => '',
        'orderby'         => 'name',
        'order'           => 'ASC',
        'hide_empty'      => 'true',
        'limit'           => '-1',

        'columns'         => '4',
        'columns_tablet'  => '3',
        'columns_mobile'  => '2',
        'gap'             => '20px',
        'gap_tablet'      => '16px',
        'gap_mobile'      => '12px',

        'image_height'    => '340px',
        'border_radius'   => '12px',
        'show_count'      => 'false',
        'fallback_image'  => '',
        'class'           => '',
    ], $atts, 'zippy_product_categories_grid');

    if ( ! function_exists('WC') ) return '<p>WooCommerce is required.</p>';

    $terms = zippy_get_product_category_terms($atts);

    if ( empty($terms) || is_wp_error($terms) ) {
        return '<p class="zippy-cat-slider-empty">' . __('No categories found.', 'flatsome-child') . '</p>';
    }

    $wrapper_class = 'zippy-cat-grid-wrapper' . ( $atts['class'] ? ' ' . esc_attr($atts['class']) : '' );
    $style = sprintf(
        '--zippy-cat-grid-columns:%1$d;--zippy-cat-grid-columns-tablet:%2$d;--zippy-cat-grid-columns-mobile:%3$d;--zippy-cat-grid-gap:%4$s;--zippy-cat-grid-gap-tablet:%5$s;--zippy-cat-grid-gap-mobile:%6$s;',
        max(1, (int) $atts['columns']),
        max(1, (int) $atts['columns_tablet']),
        max(1, (int) $atts['columns_mobile']),
        esc_attr($atts['gap']),
        esc_attr($atts['gap_tablet']),
        esc_attr($atts['gap_mobile'])
    );

    ob_start();
    ?>
    <div class="<?php echo esc_attr($wrapper_class); ?>" style="<?php echo esc_attr($style); ?>">
        <div class="zippy-cat-grid">
            <?php foreach ( $terms as $term ) : ?>
            <div class="zippy-cat-grid__item">
                <?php echo zippy_render_product_category_card($term, $atts); ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode('zippy_product_categories_grid', 'zippy_product_categories_grid');
