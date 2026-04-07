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

    // ── Build terms query ──
    $query_args = [
        'taxonomy'   => 'product_cat',
        'hide_empty' => $atts['hide_empty'] === 'true',
        'orderby'    => sanitize_key($atts['orderby']),
        'order'      => sanitize_key($atts['order']),
    ];

    // Parent filter
    if ( $atts['parent'] !== '-1' ) {
        $query_args['parent'] = (int) $atts['parent'];
    }

    // Limit
    if ( (int) $atts['limit'] > 0 ) {
        $query_args['number'] = (int) $atts['limit'];
    }

    // Include by slug
    if ( ! empty($atts['include']) ) {
        $slugs = array_map('trim', explode(',', $atts['include']));
        $query_args['slug'] = $slugs;
    }

    $terms = get_terms($query_args);

    // Exclude by slug
    if ( ! empty($atts['exclude']) ) {
        $exclude_slugs = array_map('trim', explode(',', $atts['exclude']));
        $terms = array_filter($terms, fn($t) => ! in_array($t->slug, $exclude_slugs));
    }

    // Remove uncategorized
    $default_cat = get_option('default_product_cat');
    $terms = array_filter($terms, fn($t) => $t->term_id != $default_cat);
    $terms = array_values($terms);

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

                <?php foreach ( $terms as $term ) :
                    // ── Get category image ──
                    $thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);
                    $image_url    = '';

                    if ( $thumbnail_id ) {
                        $image_url = wp_get_attachment_image_url($thumbnail_id, 'woocommerce_single');
                    }

                    // Fallback
                    if ( ! $image_url ) {
                        $image_url = ! empty($atts['fallback_image'])
                            ? esc_url($atts['fallback_image'])
                            : wc_placeholder_img_src('woocommerce_single');
                    }

                    $cat_url    = get_term_link($term);
                    $cat_name   = $term->name;
                    $cat_count  = $term->count;
                ?>

                <div class="swiper-slide">
                    <a
                        href="<?php echo esc_url($cat_url); ?>"
                        class="zippy-cat-card"
                        style="height:<?php echo esc_attr($atts['image_height']); ?>;border-radius:<?php echo esc_attr($atts['border_radius']); ?>"
                        aria-label="<?php echo esc_attr($cat_name); ?>"
                    >
                        <!-- Background image -->
                        <div
                            class="zippy-cat-card__bg"
                            style="background-image:url('<?php echo esc_url($image_url); ?>')"
                            role="img"
                            aria-label="<?php echo esc_attr($cat_name); ?>"
                        ></div>

                        <!-- Gradient overlay -->
                        <div class="zippy-cat-card__overlay"></div>

                        <!-- Label -->
                        <div class="zippy-cat-card__label">
                            <span class="zippy-cat-card__name"><?php echo esc_html($cat_name); ?></span>
                            <?php if ( $atts['show_count'] === 'true' ) : ?>
                            <span class="zippy-cat-card__count"><?php echo $cat_count; ?> products</span>
                            <?php endif; ?>
                        </div>
                    </a>
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