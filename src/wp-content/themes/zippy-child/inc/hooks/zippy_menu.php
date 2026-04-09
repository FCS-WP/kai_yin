<?php


// ============================================
// [zippy_menu]
// ============================================
function zippy_menu( $atts ) {
    $atts = shortcode_atts([
        // Menu
        'menu'           => '',         // menu ID or name or slug
        'direction'      => 'horizontal', // horizontal | vertical

        // Style
        'align'          => 'left',     // left | center | right
        'gap'            => '32px',     // space between items
        'font_size'      => '',
        'font_weight'    => '',
        'font_family'    => '',
        'color'          => '',
        'color_hover'    => '',
        'color_active'   => '',
        'indicator'      => 'false',    // show active underline indicator
        'class'          => '',
    ], $atts, 'zippy_menu');

    if ( empty($atts['menu']) ) return '<!-- zippy_menu: no menu specified -->';

    // ── Resolve menu by ID, slug, or name ──
    $menu_obj = null;
    if ( is_numeric($atts['menu']) ) {
        $menu_obj = wp_get_nav_menu_object( (int) $atts['menu'] );
    }
    if ( ! $menu_obj ) {
        $menu_obj = wp_get_nav_menu_object( $atts['menu'] );
    }
    if ( ! $menu_obj ) return '<!-- zippy_menu: menu not found -->';

    // ── Unique ID for scoped CSS ──
    static $menu_index = 0;
    $menu_index++;
    $uid = 'zippy-menu-' . $menu_index;

    // ── Build scoped CSS vars ──
    $css_rules = [];

    if ( $atts['direction'] === 'horizontal' ) {
        $css_rules[] = 'display:flex;flex-wrap:wrap;align-items:center;';
        $css_rules[] = 'justify-content:' . ( $atts['align'] === 'center' ? 'center' : ( $atts['align'] === 'right' ? 'flex-end' : 'flex-start' ) ) . ';';
        $css_rules[] = 'gap:' . esc_attr($atts['gap']) . ';';
    } else {
        $css_rules[] = 'display:flex;flex-direction:column;';
        $css_rules[] = 'align-items:' . ( $atts['align'] === 'center' ? 'center' : ( $atts['align'] === 'right' ? 'flex-end' : 'flex-start' ) ) . ';';
        $css_rules[] = 'gap:' . esc_attr($atts['gap']) . ';';
    }

    $link_css   = [];
    $hover_css  = [];
    $active_css = [];

    if ( $atts['font_size'] )   $link_css[] = 'font-size:'   . esc_attr($atts['font_size']);
    if ( $atts['font_weight'] ) $link_css[] = 'font-weight:' . esc_attr($atts['font_weight']);
    if ( $atts['font_family'] ) $link_css[] = 'font-family:' . esc_attr($atts['font_family']);
    if ( $atts['color'] )       $link_css[] = 'color:'       . esc_attr($atts['color']);
    if ( $atts['color_hover'] ) $hover_css[] = 'color:'      . esc_attr($atts['color_hover']);
    if ( $atts['color_active']) $active_css[] = 'color:'     . esc_attr($atts['color_active']);

    // Indicator underline
    $indicator_css = '';
    if ( $atts['indicator'] === 'true' ) {
        $indicator_color = $atts['color_active'] ?: $atts['color'] ?: 'currentColor';
        $indicator_css = "
            #{$uid} .zippy-menu > li > a::after {
                content: '';
                display: block;
                height: 2px;
                width: 0;
                background: {$indicator_color};
                transition: width 0.25s ease;
                margin-top: 4px;
            }
            #{$uid} .zippy-menu > li > a:hover::after,
            #{$uid} .zippy-menu > li.current-menu-item > a::after {
                width: 100%;
            }
        ";
    }

    ob_start();
    ?>

    <style>
        #<?php echo $uid; ?> .zippy-menu {
            list-style: none;
            margin: 0;
            padding: 0;
            <?php echo implode('', $css_rules); ?>
        }
        #<?php echo $uid; ?> .zippy-menu li {
            margin: 0;
            padding: 0;
        }
        #<?php echo $uid; ?> .zippy-menu li a {
            text-decoration: none;
            display: inline-block;
            transition: color 0.2s ease;
            <?php echo implode(';', $link_css); ?>
        }
        <?php if ( $hover_css ) : ?>
        #<?php echo $uid; ?> .zippy-menu li a:hover {
            <?php echo implode(';', $hover_css); ?>
        }
        <?php endif; ?>
        <?php if ( $active_css ) : ?>
        #<?php echo $uid; ?> .zippy-menu li.current-menu-item > a,
        #<?php echo $uid; ?> .zippy-menu li.current-menu-ancestor > a {
            <?php echo implode(';', $active_css); ?>
        }
        <?php endif; ?>
        <?php echo $indicator_css; ?>
    </style>

    <nav id="<?php echo $uid; ?>"
         class="zippy-menu-wrap zippy-menu--<?php echo esc_attr($atts['direction']); ?> <?php echo esc_attr($atts['class']); ?>"
         aria-label="<?php echo esc_attr($menu_obj->name); ?>">
        <?php
        wp_nav_menu([
            'menu'            => $menu_obj,
            'menu_class'      => 'zippy-menu',
            'container'       => false,
            'items_wrap'      => '<ul class="%2$s">%3$s</ul>',
            'depth'           => 1,   // 0 = all levels, 1 = top level only
        ]);
        ?>
    </nav>

    <?php
    return ob_get_clean();
}
add_shortcode('zippy_menu', 'zippy_menu');