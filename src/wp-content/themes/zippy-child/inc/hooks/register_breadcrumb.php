<?php
// ============================================================
// [zippy_breadcrumb]
// ============================================================
function zippy_breadcrumb( $atts ) {
    $atts = shortcode_atts([
        'separator'       => '/',
        'show_home'       => 'true',
        'home_text'       => 'Home',
        'home_icon'       => 'false',
        'font_size'       => '',
        'font_weight'     => '',
        'color'           => '',
        'color_active'    => '',
        'color_hover'     => '',
        'color_separator' => '',
        'align'           => 'left',
        'class'           => '',
    ], $atts, 'zippy_breadcrumb');
 
    // Don't show on homepage
    if ( is_front_page() ) return '';
 
    static $bc_index = 0;
    $bc_index++;
    $uid = 'zippy-bc-' . $bc_index;
 
    // ── Build items ──
    $items = [];
 
    // Home item
    if ( $atts['show_home'] === 'true' ) {
        $home_label = $atts['home_icon'] === 'true'
            ? '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>'
            : esc_html($atts['home_text']);
 
        $items[] = [
            'label'  => $home_label,
            'url'    => home_url('/'),
            'active' => false,
        ];
    }
 
    // ── WooCommerce pages ──
    if ( function_exists('WC') ) {
 
        if ( is_shop() ) {
            $items[] = [ 'label' => get_the_title(wc_get_page_id('shop')), 'url' => '', 'active' => true ];
 
        } elseif ( is_product() ) {
            $items[] = [
                'label'  => get_the_title(wc_get_page_id('shop')),
                'url'    => get_permalink(wc_get_page_id('shop')),
                'active' => false,
            ];
            $terms = get_the_terms(get_the_ID(), 'product_cat');
            if ( $terms && ! is_wp_error($terms) ) {
                $term = array_reduce($terms, function($carry, $item) {
                    return (!$carry || $item->parent > $carry->parent) ? $item : $carry;
                });
                foreach ( array_reverse(get_ancestors($term->term_id, 'product_cat')) as $ancestor_id ) {
                    $ancestor = get_term($ancestor_id, 'product_cat');
                    $items[]  = [ 'label' => $ancestor->name, 'url' => get_term_link($ancestor), 'active' => false ];
                }
                $items[] = [ 'label' => $term->name, 'url' => get_term_link($term), 'active' => false ];
            }
            $items[] = [ 'label' => get_the_title(), 'url' => '', 'active' => true ];
 
        } elseif ( is_product_category() ) {
            $items[] = [
                'label'  => get_the_title(wc_get_page_id('shop')),
                'url'    => get_permalink(wc_get_page_id('shop')),
                'active' => false,
            ];
            $term = get_queried_object();
            foreach ( array_reverse(get_ancestors($term->term_id, 'product_cat')) as $ancestor_id ) {
                $ancestor = get_term($ancestor_id, 'product_cat');
                $items[]  = [ 'label' => $ancestor->name, 'url' => get_term_link($ancestor), 'active' => false ];
            }
            $items[] = [ 'label' => $term->name, 'url' => '', 'active' => true ];
        }
 
    }
 
    // ── Standard WordPress pages ──
    // Only run if no WooCommerce items were added beyond Home
    $wc_handled = count($items) > 1;
 
    if ( ! $wc_handled ) {
 
        if ( is_single() ) {
            $categories = get_the_category();
            if ( $categories ) {
                $items[] = [
                    'label'  => $categories[0]->name,
                    'url'    => get_category_link($categories[0]->term_id),
                    'active' => false,
                ];
            }
            $items[] = [ 'label' => get_the_title(), 'url' => '', 'active' => true ];
 
        } elseif ( is_page() ) {
            $ancestors = array_reverse(get_post_ancestors(get_the_ID()));
            foreach ( $ancestors as $ancestor_id ) {
                $items[] = [
                    'label'  => get_the_title($ancestor_id),
                    'url'    => get_permalink($ancestor_id),
                    'active' => false,
                ];
            }
            $items[] = [ 'label' => get_the_title(), 'url' => '', 'active' => true ];
 
        } elseif ( is_category() ) {
            $category = get_queried_object();
            foreach ( array_reverse(get_ancestors($category->term_id, 'category')) as $ancestor_id ) {
                $ancestor = get_term($ancestor_id, 'category');
                $items[]  = [ 'label' => $ancestor->name, 'url' => get_term_link($ancestor), 'active' => false ];
            }
            $items[] = [ 'label' => $category->name, 'url' => '', 'active' => true ];
 
        } elseif ( is_tag() ) {
            $items[] = [ 'label' => 'Tag: ' . single_tag_title('', false), 'url' => '', 'active' => true ];
 
        } elseif ( is_author() ) {
            $items[] = [ 'label' => 'Author: ' . get_the_author(), 'url' => '', 'active' => true ];
 
        } elseif ( is_date() ) {
            $items[] = [ 'label' => get_the_date('F Y'), 'url' => '', 'active' => true ];
 
        } elseif ( is_search() ) {
            $items[] = [ 'label' => 'Search: ' . get_search_query(), 'url' => '', 'active' => true ];
 
        } elseif ( is_404() ) {
            $items[] = [ 'label' => '404 - Page Not Found', 'url' => '', 'active' => true ];
        }
    }
 
    // Need at least 2 items to show breadcrumb
    if ( count($items) <= 1 ) return '';
 
    // ── Scoped CSS ──
    $link_css   = [];
    $hover_css  = [];
    $active_css = [];
    $sep_css    = [];
 
    if ( $atts['font_size'] )       $link_css[]   = 'font-size:'   . esc_attr($atts['font_size']);
    if ( $atts['font_weight'] )     $link_css[]   = 'font-weight:' . esc_attr($atts['font_weight']);
    if ( $atts['color'] )           $link_css[]   = 'color:'       . esc_attr($atts['color']);
    if ( $atts['color_hover'] )     $hover_css[]  = 'color:'       . esc_attr($atts['color_hover']);
    if ( $atts['color_active'] )    $active_css[] = 'color:'       . esc_attr($atts['color_active']);
    if ( $atts['color_separator'] ) $sep_css[]    = 'color:'       . esc_attr($atts['color_separator']);
 
    ob_start();
    ?>
 
    <?php if ( $link_css || $hover_css || $active_css || $sep_css ) : ?>
    <style>
        <?php if ($link_css) : ?>
        #<?php echo $uid; ?> .zippy-breadcrumb__item a,
        #<?php echo $uid; ?> .zippy-breadcrumb__item span { <?php echo implode(';', $link_css); ?> }
        <?php endif; ?>
        <?php if ($hover_css) : ?>
        #<?php echo $uid; ?> .zippy-breadcrumb__item a:hover { <?php echo implode(';', $hover_css); ?> }
        <?php endif; ?>
        <?php if ($active_css) : ?>
        #<?php echo $uid; ?> .zippy-breadcrumb__item--active span { <?php echo implode(';', $active_css); ?> }
        <?php endif; ?>
        <?php if ($sep_css) : ?>
        #<?php echo $uid; ?> .zippy-breadcrumb__sep { <?php echo implode(';', $sep_css); ?> }
        <?php endif; ?>
    </style>
    <?php endif; ?>
 
    <nav id="<?php echo $uid; ?>"
         class="zippy-breadcrumb <?php echo esc_attr($atts['class']); ?>"
         aria-label="Breadcrumb"
         style="text-align:<?php echo esc_attr($atts['align']); ?>">
        <ol class="zippy-breadcrumb__list" itemscope itemtype="https://schema.org/BreadcrumbList">
            <?php foreach ( $items as $index => $item ) :
                $is_last  = $index === array_key_last($items);
                $position = $index + 1;
            ?>
            <li class="zippy-breadcrumb__item<?php echo $is_last ? ' zippy-breadcrumb__item--active' : ''; ?>"
                itemprop="itemListElement"
                itemscope
                itemtype="https://schema.org/ListItem">
 
                <?php if ( ! $is_last && $item['url'] ) : ?>
                    <a href="<?php echo esc_url($item['url']); ?>" itemprop="item">
                        <span itemprop="name"><?php echo wp_kses_post($item['label']); ?></span>
                    </a>
                <?php else : ?>
                    <span itemprop="name" aria-current="page"><?php echo wp_kses_post($item['label']); ?></span>
                <?php endif; ?>
 
                <meta itemprop="position" content="<?php echo $position; ?>" />
 
                <?php if ( ! $is_last ) : ?>
                    <span class="zippy-breadcrumb__sep" aria-hidden="true"><?php echo esc_html($atts['separator']); ?></span>
                <?php endif; ?>
 
            </li>
            <?php endforeach; ?>
        </ol>
    </nav>
 
    <?php
    return ob_get_clean();
}
add_shortcode('zippy_breadcrumb', 'zippy_breadcrumb');