<?php
if ( ! defined('ABSPATH') ) exit;

// ============================================================
// Register "projects" Custom Post Type
// ============================================================
add_action('init', function () {
    register_post_type('projects', [
        'label'         => __('Projects', 'flatsome-child'),
        'labels'        => [
            'name'               => __('Projects', 'flatsome-child'),
            'singular_name'      => __('Project', 'flatsome-child'),
            'add_new_item'       => __('Add New Project', 'flatsome-child'),
            'edit_item'          => __('Edit Project', 'flatsome-child'),
            'new_item'           => __('New Project', 'flatsome-child'),
            'view_item'          => __('View Project', 'flatsome-child'),
            'search_items'       => __('Search Projects', 'flatsome-child'),
            'not_found'          => __('No projects found', 'flatsome-child'),
            'not_found_in_trash' => __('No projects found in Trash', 'flatsome-child'),
        ],
        'public'        => true,
        'menu_icon'     => 'dashicons-portfolio',
        'supports'      => ['title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'],
        'show_in_rest'  => true,
        'rewrite'       => ['slug' => 'projects'],
        'has_archive'   => true,
    ]);
});

// ============================================================
// Register "projects_category" Taxonomy
// ============================================================
add_action('init', function () {
    register_taxonomy('projects_category', 'projects', [
        'label'             => __('Project Categories', 'flatsome-child'),
        'public'            => true,
        'publicly_queryable'=> true,
        'hierarchical'      => true,
        'show_in_rest'      => true,
        'query_var'         => true,
        'rewrite'           => ['slug' => 'projects-category'],
        'show_admin_column' => true,
    ]);
});

function zippy_get_projects_category_image_id( $term_id ) {
    return (int) get_term_meta($term_id, 'thumbnail_id', true);
}

function zippy_get_projects_category_image_url( $term_id, $size = 'full' ) {
    $image_id = zippy_get_projects_category_image_id($term_id);

    if ( ! $image_id ) {
        return '';
    }

    return wp_get_attachment_image_url($image_id, $size) ?: '';
}

add_action('projects_category_add_form_fields', function () {
    ?>
    <div class="form-field term-thumbnail-wrap">
        <label for="projects-category-thumbnail-id"><?php esc_html_e('Category Image', 'flatsome-child'); ?></label>
        <input type="hidden" id="projects-category-thumbnail-id" name="thumbnail_id" value="" />
        <div id="projects-category-thumbnail-preview" style="margin-bottom:12px;"></div>
        <button type="button" class="button zippy-term-image-upload"><?php esc_html_e('Upload/Add image', 'flatsome-child'); ?></button>
        <button type="button" class="button zippy-term-image-remove" style="display:none;"><?php esc_html_e('Remove image', 'flatsome-child'); ?></button>
        <p class="description"><?php esc_html_e('Recommended for archive banner background.', 'flatsome-child'); ?></p>
    </div>
    <?php
});

add_action('projects_category_edit_form_fields', function ( $term ) {
    $image_id = zippy_get_projects_category_image_id($term->term_id);
    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : '';
    ?>
    <tr class="form-field term-thumbnail-wrap">
        <th scope="row"><label for="projects-category-thumbnail-id"><?php esc_html_e('Category Image', 'flatsome-child'); ?></label></th>
        <td>
            <input type="hidden" id="projects-category-thumbnail-id" name="thumbnail_id" value="<?php echo esc_attr($image_id); ?>" />
            <div id="projects-category-thumbnail-preview" style="margin-bottom:12px;">
                <?php if ( $image_url ) : ?>
                    <img src="<?php echo esc_url($image_url); ?>" alt="" style="max-width:220px;height:auto;display:block;" />
                <?php endif; ?>
            </div>
            <button type="button" class="button zippy-term-image-upload"><?php esc_html_e('Upload/Add image', 'flatsome-child'); ?></button>
            <button type="button" class="button zippy-term-image-remove" <?php echo $image_url ? '' : 'style="display:none;"'; ?>><?php esc_html_e('Remove image', 'flatsome-child'); ?></button>
            <p class="description"><?php esc_html_e('Recommended for archive banner background.', 'flatsome-child'); ?></p>
        </td>
    </tr>
    <?php
});

add_action('created_projects_category', function ( $term_id ) {
    if ( isset($_POST['thumbnail_id']) ) {
        update_term_meta($term_id, 'thumbnail_id', absint($_POST['thumbnail_id']));
    }
});

add_action('edited_projects_category', function ( $term_id ) {
    if ( isset($_POST['thumbnail_id']) ) {
        update_term_meta($term_id, 'thumbnail_id', absint($_POST['thumbnail_id']));
    }
});

add_action('admin_enqueue_scripts', function ( $hook ) {
    if ( ! in_array($hook, ['edit-tags.php', 'term.php'], true) ) {
        return;
    }

    $screen = get_current_screen();
    if ( ! $screen || $screen->taxonomy !== 'projects_category' ) {
        return;
    }

    wp_enqueue_media();
    wp_add_inline_script('jquery-core', "
        jQuery(function($) {
            var frame;
            function updatePreview(attachment) {
                var preview = $('#projects-category-thumbnail-preview');
                var removeBtn = $('.zippy-term-image-remove');
                var image = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
                preview.html('<img src=\"' + image + '\" alt=\"\" style=\"max-width:220px;height:auto;display:block;\" />');
                removeBtn.show();
            }

            $(document).on('click', '.zippy-term-image-upload', function(e) {
                e.preventDefault();
                if (frame) {
                    frame.open();
                    return;
                }

                frame = wp.media({
                    title: 'Select category image',
                    button: { text: 'Use image' },
                    multiple: false
                });

                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#projects-category-thumbnail-id').val(attachment.id);
                    updatePreview(attachment);
                });

                frame.open();
            });

            $(document).on('click', '.zippy-term-image-remove', function(e) {
                e.preventDefault();
                $('#projects-category-thumbnail-id').val('');
                $('#projects-category-thumbnail-preview').empty();
                $(this).hide();
            });
        });
    ");
});

// ============================================================
// Project meta box
// ============================================================
add_action('add_meta_boxes', function () {
    add_meta_box(
        'zippy_project_meta',
        __('Project Details', 'flatsome-child'),
        'zippy_project_meta_box',
        'projects',
        'normal',
        'high'
    );
});

function zippy_project_meta_box( $post ) {
    wp_nonce_field('zippy_project_meta_save', 'zippy_project_nonce');

    $eyebrow       = get_post_meta($post->ID, '_project_eyebrow', true);
    $label_right   = get_post_meta($post->ID, '_project_label_right', true);
    $credit        = get_post_meta($post->ID, '_project_credit', true);
    $completed     = get_post_meta($post->ID, '_project_completed_date', true);
    $button_text   = get_post_meta($post->ID, '_project_button_text', true);
    $button_url    = get_post_meta($post->ID, '_project_button_url', true);
    ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div>
            <label for="project_eyebrow"><?php esc_html_e('Small Label', 'flatsome-child'); ?></label>
            <input id="project_eyebrow" type="text" name="project_eyebrow" value="<?php echo esc_attr($eyebrow); ?>" />
        </div>

        <div>
            <label for="project_label_right"><?php esc_html_e('Right Label', 'flatsome-child'); ?></label>
            <input id="project_label_right" type="text" name="project_label_right" value="<?php echo esc_attr($label_right); ?>" />
        </div>

        <div>
            <label for="project_credit"><?php esc_html_e('Project Credit', 'flatsome-child'); ?></label>
            <input id="project_credit" type="text" name="project_credit" value="<?php echo esc_attr($credit); ?>" />
        </div>

        <div>
            <label for="project_completed_date"><?php esc_html_e('Completed Date', 'flatsome-child'); ?></label>
            <input id="project_completed_date" type="date" name="project_completed_date" value="<?php echo esc_attr($completed); ?>" />
        </div>

        <div>
            <label for="project_button_text"><?php esc_html_e('Button Text', 'flatsome-child'); ?></label>
            <input id="project_button_text" type="text" name="project_button_text" value="<?php echo esc_attr($button_text); ?>" />
        </div>

        <div>
            <label for="project_button_url"><?php esc_html_e('Button URL', 'flatsome-child'); ?></label>
            <input id="project_button_url" type="url" name="project_button_url" value="<?php echo esc_attr($button_url); ?>" />
        </div>
    </div>
    <?php
}

add_action('save_post_projects', function ( $post_id ) {
    if ( ! isset($_POST['zippy_project_nonce']) ) return;
    if ( ! wp_verify_nonce($_POST['zippy_project_nonce'], 'zippy_project_meta_save') ) return;

    update_post_meta($post_id, '_project_eyebrow', sanitize_text_field($_POST['project_eyebrow'] ?? ''));
    update_post_meta($post_id, '_project_label_right', sanitize_text_field($_POST['project_label_right'] ?? ''));
    update_post_meta($post_id, '_project_credit', sanitize_text_field($_POST['project_credit'] ?? ''));
    update_post_meta($post_id, '_project_completed_date', sanitize_text_field($_POST['project_completed_date'] ?? ''));
    update_post_meta($post_id, '_project_button_text', sanitize_text_field($_POST['project_button_text'] ?? ''));
    update_post_meta($post_id, '_project_button_url', esc_url_raw($_POST['project_button_url'] ?? ''));
});

function zippy_get_project_completed_date( $post_id ) {
    $completed = get_post_meta($post_id, '_project_completed_date', true);

    if ( ! empty($completed) ) {
        $timestamp = strtotime($completed);
        if ( $timestamp ) {
            return date_i18n('F d, Y', $timestamp);
        }
    }

    return get_the_date('F d, Y', $post_id);
}

function zippy_get_project_credit( $post_id ) {
    $credit = get_post_meta($post_id, '_project_credit', true);

    if ( ! empty($credit) ) {
        return $credit;
    }

    return get_the_author_meta('display_name', (int) get_post_field('post_author', $post_id));
}

add_action('pre_get_posts', function ( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) return;

    if ( $query->is_post_type_archive('projects') || $query->is_tax('projects_category') ) {
        if ( ! $query->get('orderby') ) {
            $query->set('orderby', 'date');
        }

        if ( ! $query->get('order') ) {
            $query->set('order', 'DESC');
        }
    }
});

// ============================================================
// [zippy_projects_showcase]
// ============================================================
function zippy_projects_showcase( $atts ) {
    $atts = shortcode_atts([
        'category'                 => '',
        'title'                    => 'Sustainable Craftsmanship, Lasting Quality',
        'eyebrow'                  => 'Environmentally Conscious',
        'description'              => '',
        'limit'                    => '6',
        'orderby'                  => 'menu_order',
        'order'                    => 'ASC',
        'slides_per_view'          => '3',
        'slides_per_view_tablet'   => '2.2',
        'slides_per_view_mobile'   => '1.15',
        'space_between'            => '18',
        'space_between_tablet'     => '16',
        'space_between_mobile'     => '14',
        'image_height'             => '360px',
        'show_arrows'              => 'true',
        'show_description'         => 'true',
        'class'                    => '',
    ], $atts, 'zippy_projects_showcase');

    $query_args = [
        'post_type'      => 'projects',
        'posts_per_page' => (int) $atts['limit'],
        'orderby'        => sanitize_key($atts['orderby']),
        'order'          => sanitize_key($atts['order']),
        'post_status'    => 'publish',
    ];

    if ( ! empty($atts['category']) ) {
        $query_args['tax_query'] = [[
            'taxonomy' => 'projects_category',
            'field'    => 'slug',
            'terms'    => array_values(array_filter(array_map('trim', explode(',', $atts['category'])))),
        ]];
    }

    $projects = new WP_Query($query_args);

    if ( ! $projects->have_posts() ) {
        return '<p class="zippy-projects-empty">' . __('No projects found.', 'flatsome-child') . '</p>';
    }

    static $instance = 0;
    $instance++;
    $uid = 'zippy-projects-showcase-' . $instance;
    $wrapper_class = 'zippy-projects-showcase' . ( $atts['class'] ? ' ' . esc_attr($atts['class']) : '' );
    $spv_tablet = $atts['slides_per_view_tablet'] ?: $atts['slides_per_view'];
    $spb_tablet = $atts['space_between_tablet'] ?: $atts['space_between'];

    $config = [
        'slidesPerView' => (float) $atts['slides_per_view_mobile'],
        'spaceBetween'  => (int) $atts['space_between_mobile'],
        'grabCursor'    => true,
        'breakpoints'   => [
            '768'  => [
                'slidesPerView' => (float) $spv_tablet,
                'spaceBetween'  => (int) $spb_tablet,
            ],
            '1024' => [
                'slidesPerView' => (float) $atts['slides_per_view'],
                'spaceBetween'  => (int) $atts['space_between'],
            ],
        ],
    ];

    if ( $atts['show_arrows'] === 'true' ) {
        $config['navigation'] = [
            'prevEl' => '#' . $uid . ' .zippy-projects-nav__prev',
            'nextEl' => '#' . $uid . ' .zippy-projects-nav__next',
        ];
    }

    ob_start();
    ?>
    <section id="<?php echo esc_attr($uid); ?>" class="<?php echo esc_attr($wrapper_class); ?>">
        <div class="zippy-projects-showcase__header">
            <?php if ( ! empty($atts['eyebrow']) ) : ?>
            <div class="zippy-projects-showcase__eyebrow"><?php echo esc_html($atts['eyebrow']); ?></div>
            <?php endif; ?>

            <h2 class="zippy-section-title"><?php echo esc_html($atts['title']); ?></h2>

            <?php if ( $atts['show_description'] === 'true' && ! empty($atts['description']) ) : ?>
            <div class="zippy-projects-showcase__description">
                <p><?php echo esc_html($atts['description']); ?></p>
            </div>
            <?php endif; ?>
        </div>

        <div class="zippy-projects-showcase__slider">
            <div class="swiper">
                <div class="swiper-wrapper">
                    <?php while ( $projects->have_posts() ) : $projects->the_post(); ?>
                    <?php
                    $project_id     = get_the_ID();
                    $project_title  = get_the_title();
                    $project_url    = get_permalink();
                    $project_image  = get_the_post_thumbnail_url($project_id, 'full');
                    $project_label  = get_post_meta($project_id, '_project_eyebrow', true);
                    $project_right  = get_post_meta($project_id, '_project_label_right', true);
                    $button_text    = get_post_meta($project_id, '_project_button_text', true);
                    $button_url     = get_post_meta($project_id, '_project_button_url', true) ?: $project_url;

                    if ( ! $project_image ) {
                        $project_image = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 900'%3E%3Crect width='1200' height='900' fill='%23f4f5fa'/%3E%3C/svg%3E";
                    }
                    ?>
                    <div class="swiper-slide">
                        <article class="zippy-project-card">
                            <a class="zippy-project-card__media" href="<?php echo esc_url($project_url); ?>" style="height:<?php echo esc_attr($atts['image_height']); ?>;">
                                <img src="<?php echo esc_url($project_image); ?>" alt="<?php echo esc_attr($project_title); ?>" loading="lazy" />
                            </a>

                            <div class="zippy-project-card__content">
                                <?php if ( ! empty($project_label) || ! empty($project_right) ) : ?>
                                <div class="zippy-project-card__meta">
                                    <span class="zippy-project-card__eyebrow"><?php echo esc_html($project_label); ?></span>
                                    <span class="zippy-project-card__tag"><?php echo esc_html($project_right); ?></span>
                                </div>
                                <?php endif; ?>

                                <h3 class="zippy-project-card__title">
                                    <a href="<?php echo esc_url($project_url); ?>"><?php echo esc_html($project_title); ?></a>
                                </h3>

                                <?php if ( ! empty($button_text) ) : ?>
                                <a class="zippy-project-card__button" href="<?php echo esc_url($button_url); ?>">
                                    <?php echo esc_html($button_text); ?>
                                </a>
                                <?php endif; ?>
                            </div>
                        </article>
                    </div>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </div>

            <?php if ( $atts['show_arrows'] === 'true' ) : ?>
            <div class="zippy-projects-nav">
                <button type="button" class="zippy-projects-nav__btn zippy-projects-nav__prev" aria-label="<?php esc_attr_e('Previous projects', 'flatsome-child'); ?>">
                    <span>&lsaquo;</span>
                </button>
                <div class="zippy-projects-nav__line"></div>
                <button type="button" class="zippy-projects-nav__btn zippy-projects-nav__next" aria-label="<?php esc_attr_e('Next projects', 'flatsome-child'); ?>">
                    <span>&rsaquo;</span>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <script>
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            new Swiper('#<?php echo esc_js($uid); ?> .swiper', <?php echo wp_json_encode($config); ?>);
        });
    })();
    </script>
    <?php

    return ob_get_clean();
}
add_shortcode('zippy_projects_showcase', 'zippy_projects_showcase');
