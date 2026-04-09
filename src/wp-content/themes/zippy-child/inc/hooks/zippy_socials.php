<?php

if (! defined('ABSPATH')) {
    exit;
}

function zippy_socials_default_options()
{
    return [
        'position' => 'bottom-right',
        'buttons'  => [
            [
                'label'   => 'WhatsApp',
                'url'     => '',
                'icon_id' => 0,
                'icon_url' => '',
                'enabled' => 0,
            ],
            [
                'label'   => 'Facebook',
                'url'     => '',
                'icon_id' => 0,
                'icon_url' => '',
                'enabled' => 0,
            ],
            [
                'label'   => 'Instagram',
                'url'     => '',
                'icon_id' => 0,
                'icon_url' => '',
                'enabled' => 0,
            ],
            [
                'label'   => 'TikTok',
                'url'     => '',
                'icon_id' => 0,
                'icon_url' => '',
                'enabled' => 0,
            ],
        ],
    ];
}

function zippy_socials_get_options()
{
    $defaults = zippy_socials_default_options();
    $options  = get_option('zippy_socials_settings', []);

    if (! is_array($options)) {
        return $defaults;
    }

    $options = wp_parse_args($options, $defaults);

    if (empty($options['buttons']) || ! is_array($options['buttons'])) {
        $options['buttons'] = $defaults['buttons'];
    }

    foreach ($defaults['buttons'] as $index => $default_button) {
        $current = $options['buttons'][$index] ?? [];
        if (! is_array($current)) {
            $current = [];
        }
        $options['buttons'][$index] = wp_parse_args($current, $default_button);
    }

    return $options;
}

function zippy_socials_sanitize_settings($input)
{
    $defaults = zippy_socials_default_options();
    $output   = $defaults;
    $positions = ['top-left', 'top-right', 'bottom-left', 'bottom-right'];

    $position = $input['position'] ?? $defaults['position'];
    $output['position'] = in_array($position, $positions, true) ? $position : $defaults['position'];

    $buttons = $input['buttons'] ?? [];

    foreach ($defaults['buttons'] as $index => $default_button) {
        $button = $buttons[$index] ?? [];

        $output['buttons'][$index] = [
            'label'    => sanitize_text_field($button['label'] ?? $default_button['label']),
            'url'      => esc_url_raw($button['url'] ?? ''),
            'icon_id'  => absint($button['icon_id'] ?? 0),
            'icon_url' => esc_url_raw($button['icon_url'] ?? ''),
            'enabled'  => empty($button['enabled']) ? 0 : 1,
        ];
    }

    return $output;
}

function zippy_socials_register_settings()
{
    register_setting(
        'zippy_socials_settings_group',
        'zippy_socials_settings',
        [
            'type'              => 'array',
            'sanitize_callback' => 'zippy_socials_sanitize_settings',
            'default'           => zippy_socials_default_options(),
        ]
    );
}
add_action('admin_init', 'zippy_socials_register_settings');

function zippy_socials_add_settings_page()
{
    add_options_page(
        'Socials',
        'Socials',
        'manage_options',
        'zippy-socials',
        'zippy_socials_render_settings_page'
    );
}
add_action('admin_menu', 'zippy_socials_add_settings_page');

function zippy_socials_admin_assets($hook_suffix)
{
    if ($hook_suffix !== 'settings_page_zippy-socials') {
        return;
    }

    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'zippy_socials_admin_assets');

function zippy_socials_render_settings_page()
{
    if (! current_user_can('manage_options')) {
        return;
    }

    $options = zippy_socials_get_options();
    ?>
    <div class="wrap">
        <h1>Socials Settings</h1>
        <p>Configure the fixed social buttons shown on the website.</p>

        <form method="post" action="options.php">
            <?php settings_fields('zippy_socials_settings_group'); ?>

            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="zippy-socials-position">Position</label>
                        </th>
                        <td>
                            <select
                                id="zippy-socials-position"
                                name="zippy_socials_settings[position]"
                            >
                                <option value="top-left" <?php selected($options['position'], 'top-left'); ?>>Top Left</option>
                                <option value="top-right" <?php selected($options['position'], 'top-right'); ?>>Top Right</option>
                                <option value="bottom-left" <?php selected($options['position'], 'bottom-left'); ?>>Bottom Left</option>
                                <option value="bottom-right" <?php selected($options['position'], 'bottom-right'); ?>>Bottom Right</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>

            <h2>Buttons</h2>

            <?php foreach ($options['buttons'] as $index => $button) : ?>
                <table class="form-table" role="presentation" style="margin-bottom:24px;border-top:1px solid #dcdcde;padding-top:16px;">
                    <tbody>
                        <tr>
                            <th scope="row">Button <?php echo esc_html($index + 1); ?></th>
                            <td>
                                <label>
                                    <input
                                        type="checkbox"
                                        name="zippy_socials_settings[buttons][<?php echo esc_attr($index); ?>][enabled]"
                                        value="1"
                                        <?php checked(! empty($button['enabled'])); ?>
                                    />
                                    Enable this button
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="zippy-socials-label-<?php echo esc_attr($index); ?>">Label</label>
                            </th>
                            <td>
                                <input
                                    type="text"
                                    class="regular-text"
                                    id="zippy-socials-label-<?php echo esc_attr($index); ?>"
                                    name="zippy_socials_settings[buttons][<?php echo esc_attr($index); ?>][label]"
                                    value="<?php echo esc_attr($button['label']); ?>"
                                />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="zippy-socials-url-<?php echo esc_attr($index); ?>">Redirect Link</label>
                            </th>
                            <td>
                                <input
                                    type="url"
                                    class="regular-text"
                                    id="zippy-socials-url-<?php echo esc_attr($index); ?>"
                                    name="zippy_socials_settings[buttons][<?php echo esc_attr($index); ?>][url]"
                                    value="<?php echo esc_attr($button['url']); ?>"
                                    placeholder="https://"
                                />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label>Icon</label>
                            </th>
                            <td>
                                <input
                                    type="hidden"
                                    class="zippy-socials-icon-id"
                                    name="zippy_socials_settings[buttons][<?php echo esc_attr($index); ?>][icon_id]"
                                    value="<?php echo esc_attr($button['icon_id']); ?>"
                                />
                                <input
                                    type="hidden"
                                    class="zippy-socials-icon-url"
                                    name="zippy_socials_settings[buttons][<?php echo esc_attr($index); ?>][icon_url]"
                                    value="<?php echo esc_attr($button['icon_url']); ?>"
                                />

                                <div class="zippy-socials-media-picker">
                                    <div class="zippy-socials-media-preview" style="margin-bottom:12px;">
                                        <?php if (! empty($button['icon_url'])) : ?>
                                            <img
                                                src="<?php echo esc_url($button['icon_url']); ?>"
                                                alt=""
                                                style="max-width:48px;max-height:48px;display:block;"
                                            />
                                        <?php endif; ?>
                                    </div>

                                    <button type="button" class="button zippy-socials-select-media">
                                        Select Icon
                                    </button>
                                    <button
                                        type="button"
                                        class="button zippy-socials-remove-media"
                                        <?php disabled(empty($button['icon_url'])); ?>
                                    >
                                        Remove Icon
                                    </button>
                                </div>
                                <p class="description">Choose an image or SVG from Media Library.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            <?php endforeach; ?>

            <?php submit_button(); ?>
        </form>
    </div>
    <script>
        (function() {
            var wrappers = document.querySelectorAll('.zippy-socials-media-picker');

            wrappers.forEach(function(wrapper) {
                var selectButton = wrapper.querySelector('.zippy-socials-select-media');
                var removeButton = wrapper.querySelector('.zippy-socials-remove-media');
                var preview = wrapper.querySelector('.zippy-socials-media-preview');
                var container = wrapper.closest('td');
                var inputId = container.querySelector('.zippy-socials-icon-id');
                var inputUrl = container.querySelector('.zippy-socials-icon-url');
                var frame;

                if (!selectButton || !removeButton || !preview || !inputId || !inputUrl) {
                    return;
                }

                selectButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    if (frame) {
                        frame.open();
                        return;
                    }

                    frame = wp.media({
                        title: 'Select icon',
                        button: {
                            text: 'Use this icon'
                        },
                        multiple: false
                    });

                    frame.on('select', function() {
                        var attachment = frame.state().get('selection').first().toJSON();
                        inputId.value = attachment.id || '';
                        inputUrl.value = attachment.url || '';
                        preview.innerHTML = attachment.url ? '<img src="' + attachment.url + '" alt="" style="max-width:48px;max-height:48px;display:block;" />' : '';
                        removeButton.disabled = !attachment.url;
                    });

                    frame.open();
                });

                removeButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    inputId.value = '';
                    inputUrl.value = '';
                    preview.innerHTML = '';
                    removeButton.disabled = true;
                });
            });
        })();
    </script>
    <?php
}

function zippy_socials_render_buttons()
{
    $options = zippy_socials_get_options();
    $buttons = array_filter(
        $options['buttons'],
        static function ($button) {
            return ! empty($button['enabled']) && ! empty($button['url']) && ! empty($button['icon_url']);
        }
    );

    if (empty($buttons)) {
        return;
    }
    ?>
    <div class="zippy-socials zippy-socials--<?php echo esc_attr($options['position']); ?>" aria-label="Social links">
        <?php foreach ($buttons as $button) : ?>
            <a
                class="zippy-socials__button"
                href="<?php echo esc_url($button['url']); ?>"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="<?php echo esc_attr($button['label']); ?>"
            >
                <span class="zippy-socials__icon" aria-hidden="true">
                    <img
                        src="<?php echo esc_url($button['icon_url']); ?>"
                        alt=""
                        loading="lazy"
                    />
                </span>
            </a>
        <?php endforeach; ?>
    </div>
    <?php
}
add_action('wp_footer', 'zippy_socials_render_buttons');
