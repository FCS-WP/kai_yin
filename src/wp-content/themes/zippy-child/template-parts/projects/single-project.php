<?php
if (! defined('ABSPATH')) exit;

while (have_posts()) : the_post();
    $project_id = get_the_ID();
    $project_image = get_the_post_thumbnail_url($project_id, 'full');
    $project_terms = get_the_terms($project_id, 'projects_category');
    $project_credit = zippy_get_project_credit($project_id);
    $project_completed = zippy_get_project_completed_date($project_id);
    $project_eyebrow = get_post_meta($project_id, '_project_eyebrow', true) ?: __('Crafted Project', 'flatsome-child');

    if (! $project_image) {
        $project_image = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 900'%3E%3Crect width='1200' height='900' fill='%23f4f5fa'/%3E%3C/svg%3E";
    }

    $recent_projects = new WP_Query([
        'post_type'      => 'projects',
        'post__not_in'   => [$project_id],
        'posts_per_page' => 5,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);

    $related_query_args = [
        'post_type'      => 'projects',
        'post__not_in'   => [$project_id],
        'posts_per_page' => 3,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ];

    if (! empty($project_terms) && ! is_wp_error($project_terms)) {
        $related_query_args['tax_query'] = [[
            'taxonomy' => 'projects_category',
            'field'    => 'term_id',
            'terms'    => wp_list_pluck($project_terms, 'term_id'),
        ]];
    }

    $related_projects = new WP_Query($related_query_args);
?>

    <div class="zippy-project-single">
        <div class="row zippy-project-single__row">
            <main class="large-8 col zippy-project-single__main" role="main">
                <article class="zippy-project-detail">
                    <header class="zippy-project-detail__header">
                        <div class="zippy-project-detail__eyebrow"><?php echo esc_html($project_eyebrow); ?></div>
                        <h1 class="zippy-project-detail__title"><?php the_title(); ?></h1>

                        <?php if (! empty($project_terms) && ! is_wp_error($project_terms)) : ?>
                            <div class="zippy-project-detail__terms">
                                <?php foreach ($project_terms as $term) : ?>
                                    <a href="<?php echo esc_url(get_term_link($term)); ?>"><?php echo esc_html($term->name); ?></a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </header>

                    <div class="zippy-project-detail__media">
                        <img src="<?php echo esc_url($project_image); ?>" alt="<?php the_title_attribute(); ?>" />
                    </div>

                    <div class="zippy-project-detail__content-card">
                        <div class="zippy-project-detail__intro">
                            <?php if (has_excerpt()) : ?>
                                <div class="zippy-project-detail__excerpt">
                                    <?php echo wpautop(get_the_excerpt()); ?>
                                </div>
                            <?php endif; ?>

                            <div class="zippy-project-detail__content">
                                <?php the_content(); ?>
                            </div>
                        </div>

                        <div class="zippy-project-detail__meta">
                            <div class="zippy-project-detail__meta-item">
                                <span><?php esc_html_e('By Master Artisan', 'flatsome-child'); ?></span>
                                <strong><?php echo esc_html($project_credit); ?></strong>
                            </div>
                            <div class="zippy-project-detail__meta-item">
                                <span><?php esc_html_e('Date Completed', 'flatsome-child'); ?></span>
                                <strong><?php echo esc_html($project_completed); ?></strong>
                            </div>
                        </div>
                    </div>
                </article>

                <?php if ($related_projects->have_posts()) : ?>
                    <section class="zippy-project-related">
                        <div class="zippy-project-related__eyebrow"><?php esc_html_e('Continue Exploring', 'flatsome-child'); ?></div>
                        <h2 class="zippy-project-related__title"><?php esc_html_e('Related Projects', 'flatsome-child'); ?></h2>

                        <div class="zippy-project-related__grid">
                            <?php while ($related_projects->have_posts()) : $related_projects->the_post(); ?>
                                <?php
                                $related_id = get_the_ID();
                                $related_thumb = get_the_post_thumbnail_url($related_id, 'medium_large');
                                if (! $related_thumb) {
                                    $related_thumb = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 800 600'%3E%3Crect width='800' height='600' fill='%23f4f5fa'/%3E%3C/svg%3E";
                                }
                                ?>
                                <article class="zippy-project-related-card">
                                    <a class="zippy-project-related-card__image" href="<?php the_permalink(); ?>">
                                        <img src="<?php echo esc_url($related_thumb); ?>" alt="<?php the_title_attribute(); ?>" />
                                    </a>

                                    <div class="zippy-project-related-card__content">
                                        <div class="zippy-project-related-card__date"><?php echo esc_html(zippy_get_project_completed_date($related_id)); ?></div>
                                        <h3 class="zippy-project-related-card__title">
                                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                        </h3>
                                    </div>
                                </article>
                            <?php endwhile;
                            wp_reset_postdata(); ?>
                        </div>
                    </section>
                <?php endif; ?>
            </main>

            <aside class="large-4 col zippy-project-single__sidebar">
                <div class="zippy-project-sidebar-card">
                    <h3 class="zippy-project-sidebar-card__title"><?php esc_html_e('Find a Project', 'flatsome-child'); ?></h3>
                    <form method="get" class="zippy-project-search" action="<?php echo esc_url(get_post_type_archive_link('projects')); ?>" role="search">
                        <input type="hidden" name="post_type" value="projects" />
                        <input type="search" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="<?php esc_attr_e('Search archive...', 'flatsome-child'); ?>" />
                        <button type="submit" aria-label="<?php esc_attr_e('Search projects', 'flatsome-child'); ?>">&#128269;</button>
                    </form>
                </div>

                <div class="zippy-project-sidebar-block">
                    <h3 class="zippy-project-sidebar-block__heading"><?php esc_html_e('Recent Projects', 'flatsome-child'); ?></h3>

                    <div class="zippy-project-recent-list">
                        <?php while ($recent_projects->have_posts()) : $recent_projects->the_post(); ?>
                            <?php
                            $recent_id = get_the_ID();
                            $recent_thumb = get_the_post_thumbnail_url($recent_id, 'thumbnail');
                            if (! $recent_thumb) {
                                $recent_thumb = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 200'%3E%3Crect width='200' height='200' fill='%23f4f5fa'/%3E%3C/svg%3E";
                            }
                            ?>
                            <article class="zippy-project-recent-item">
                                <a class="zippy-project-recent-item__thumb" href="<?php the_permalink(); ?>">
                                    <img src="<?php echo esc_url($recent_thumb); ?>" alt="<?php the_title_attribute(); ?>" />
                                </a>

                                <div class="zippy-project-recent-item__content">
                                    <h4 class="zippy-project-recent-item__title">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h4>
                                    <div class="zippy-project-recent-item__date"><?php echo esc_html(zippy_get_project_completed_date($recent_id)); ?></div>
                                </div>
                            </article>
                        <?php endwhile;
                        wp_reset_postdata(); ?>
                    </div>
                </div>
            </aside>
        </div>
    </div>
<?php endwhile; ?>