<?php
if ( ! defined('ABSPATH') ) exit;

$current_term = is_tax('projects_category') ? get_queried_object() : null;
$featured_id = 0;
$is_first_page = max(1, (int) get_query_var('paged')) === 1;
$archive_title = $current_term ? single_term_title('', false) : __('Our Projects', 'flatsome-child');
$archive_eyebrow = $current_term ? __('Project Category', 'flatsome-child') : __('Kai Yin Pte Ltd', 'flatsome-child');
$archive_banner_url = '';
$archive_description = '';

if ( $current_term && ! empty($current_term->term_id) ) {
    $archive_banner_url = zippy_get_projects_category_image_url($current_term->term_id, 'full');
    $archive_description = term_description($current_term->term_id, 'projects_category');
}

if ( ! $archive_banner_url ) {
    $archive_banner_url = zippy_get_theme_mod_image_url('zippy_projects_archive_banner_image', 'full');
}

$recent_query_args = [
    'post_type'      => 'projects',
    'posts_per_page' => 5,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
];

if ( $current_term && ! empty($current_term->slug) ) {
    $recent_query_args['tax_query'] = [[
        'taxonomy' => 'projects_category',
        'field'    => 'slug',
        'terms'    => [$current_term->slug],
    ]];
}

$recent_projects = new WP_Query($recent_query_args);

if ( ! $archive_banner_url ) {
    $banner_fallback = get_posts([
        'post_type'      => 'projects',
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
        'fields'         => 'ids',
    ]);

    if ( ! empty($banner_fallback[0]) ) {
        $archive_banner_url = get_the_post_thumbnail_url($banner_fallback[0], 'full');
    }
}

if ( ! $archive_banner_url ) {
    $archive_banner_url = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1600 420'%3E%3Crect width='1600' height='420' fill='%23292929'/%3E%3C/svg%3E";
}
?>

<section class="zippy-archive-banner" style="background-image:url('<?php echo esc_url($archive_banner_url); ?>');">
    <div class="zippy-archive-banner__overlay"></div>
    <div class="zippy-archive-banner__inner">
        <h1 class="zippy-archive-banner__title"><?php echo esc_html($archive_title); ?></h1>

        <nav class="zippy-breadcrumb center-breadcrumb zippy-archive-banner__breadcrumb" aria-label="<?php esc_attr_e('Breadcrumb', 'flatsome-child'); ?>">
            <ol class="zippy-breadcrumb__list">
                <li class="zippy-breadcrumb__item">
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'flatsome-child'); ?></a>
                </li>
                <li class="zippy-breadcrumb__item">
                    <span class="zippy-breadcrumb__sep">/</span>
                    <?php if ( $current_term ) : ?>
                        <a href="<?php echo esc_url(get_post_type_archive_link('projects')); ?>"><?php esc_html_e('Projects', 'flatsome-child'); ?></a>
                    <?php else : ?>
                        <span><?php esc_html_e('Projects', 'flatsome-child'); ?></span>
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

<div class="zippy-project-archive">
    <div class="row zippy-project-archive__row">
        <main class="large-8 col zippy-project-archive__main" role="main">
            <header class="zippy-project-archive__header">
                <div class="zippy-project-archive__eyebrow"><?php echo esc_html($archive_eyebrow); ?></div>
                <h1 class="zippy-project-archive__title"><?php echo esc_html($archive_title); ?></h1>
            </header>

            <?php if ( have_posts() ) : ?>
                <?php if ( $is_first_page ) : the_post(); ?>
                    <?php
                    $featured_id = get_the_ID();
                    $featured_image = get_the_post_thumbnail_url($featured_id, 'full');
                    if ( ! $featured_image ) {
                        $featured_image = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 900'%3E%3Crect width='1200' height='900' fill='%23f4f5fa'/%3E%3C/svg%3E";
                    }
                    ?>
                    <article class="zippy-project-featured">
                        <div class="zippy-project-featured__badge"><?php esc_html_e('Newest Project', 'flatsome-child'); ?></div>

                        <a class="zippy-project-featured__image" href="<?php the_permalink(); ?>">
                            <img src="<?php echo esc_url($featured_image); ?>" alt="<?php the_title_attribute(); ?>" />
                        </a>

                        <div class="zippy-project-featured__content">
                            <h2 class="zippy-project-featured__title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>

                            <div class="zippy-project-featured__excerpt">
                                <?php echo wpautop(wp_trim_words(get_the_excerpt() ?: get_the_content(), 36)); ?>
                            </div>

                            <div class="zippy-project-featured__meta">
                                <div class="zippy-project-featured__meta-item">
                                    <span class="zippy-project-featured__meta-label"><?php esc_html_e('By Master Artisan', 'flatsome-child'); ?></span>
                                    <strong><?php echo esc_html(zippy_get_project_credit($featured_id)); ?></strong>
                                </div>

                                <div class="zippy-project-featured__meta-item">
                                    <span class="zippy-project-featured__meta-label"><?php esc_html_e('Date Completed', 'flatsome-child'); ?></span>
                                    <strong><?php echo esc_html(zippy_get_project_completed_date($featured_id)); ?></strong>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endif; ?>

                <?php if ( have_posts() ) : ?>
                    <div class="zippy-project-archive-grid">
                        <?php while ( have_posts() ) : the_post(); ?>
                            <?php
                            $project_id = get_the_ID();
                            $thumb = get_the_post_thumbnail_url($project_id, 'medium_large');
                            if ( ! $thumb ) {
                                $thumb = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 800 600'%3E%3Crect width='800' height='600' fill='%23f4f5fa'/%3E%3C/svg%3E";
                            }
                            ?>
                            <article class="zippy-project-archive-card">
                                <a class="zippy-project-archive-card__image" href="<?php the_permalink(); ?>">
                                    <img src="<?php echo esc_url($thumb); ?>" alt="<?php the_title_attribute(); ?>" />
                                </a>

                                <div class="zippy-project-archive-card__content">
                                    <h3 class="zippy-project-archive-card__title">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h3>
                                    <div class="zippy-project-archive-card__date"><?php echo esc_html(zippy_get_project_completed_date($project_id)); ?></div>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>

                    <div class="zippy-project-archive__pagination">
                        <?php echo paginate_links([
                            'type'      => 'list',
                            'prev_text' => __('Previous', 'flatsome-child'),
                            'next_text' => __('Next', 'flatsome-child'),
                        ]); ?>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <p class="zippy-projects-empty"><?php esc_html_e('No projects found.', 'flatsome-child'); ?></p>
            <?php endif; ?>
        </main>

        <aside class="large-4 col zippy-project-archive__sidebar">
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
                    <?php while ( $recent_projects->have_posts() ) : $recent_projects->the_post(); ?>
                        <?php
                        $recent_id = get_the_ID();
                        if ( $featured_id && $recent_id === $featured_id ) {
                            continue;
                        }

                        $recent_thumb = get_the_post_thumbnail_url($recent_id, 'thumbnail');
                        if ( ! $recent_thumb ) {
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
                                <div class="zippy-project-recent-item__date"><?php echo esc_html(get_the_date('M d, Y', $recent_id)); ?></div>
                            </div>
                        </article>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </div>

         
        </aside>
    </div>
</div>
