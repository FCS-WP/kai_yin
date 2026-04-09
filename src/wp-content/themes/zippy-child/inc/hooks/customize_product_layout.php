<?php
add_action('wp_footer', 'flatsome_custom_product_card_js');
function flatsome_custom_product_card_js()
{
?>
    <div id="pc-toast"></div>
    <script>
        (function($) {
            // Qty buttons — use event delegation on document
            $(document).on('click', '.qty-plus', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $btn = $(this);
                var $input = $btn.closest('.product-card__qty').find('.qty-input');
                var max = parseInt($input.attr('max')) || 99;
                var val = parseInt($input.val()) || 1;
                if (val < max) $input.val(val + 1);
            });

            $(document).on('click', '.qty-minus', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $btn = $(this);
                var $input = $btn.closest('.product-card__qty').find('.qty-input');
                var val = parseInt($input.val()) || 1;
                if (val > 1) $input.val(val - 1);
            });

            // Add to cart via AJAX
            $(document).on('click', '.product-card__atc', function() {
                var $btn = $(this);
                var id = $btn.data('product-id');
                var name = $btn.data('product-name');
                var qty = $btn.closest('.product-card-wrapper').find('.qty-input').val() || 1;

                $btn.text('Adding...').prop('disabled', true);

                $.post(wc_add_to_cart_params.ajax_url, {
                    action: 'woocommerce_ajax_add_to_cart',
                    product_id: id,
                    quantity: qty
                }, function(response) {
                    $btn.text('Add to Cart').prop('disabled', false);
                    showToast('✓ ' + name + ' added to cart');
                    $(document.body).trigger('wc_fragment_refresh');
                });
            });

            function showToast(msg) {
                var $t = $('#pc-toast');
                $t.text(msg).addClass('show');
                clearTimeout(window._pcToast);
                window._pcToast = setTimeout(function() {
                    $t.removeClass('show');
                }, 2500);
            }
        })(jQuery);
    </script>
<?php
}

// Register the AJAX add-to-cart handler
add_action('wp_ajax_woocommerce_ajax_add_to_cart', 'flatsome_ajax_add_to_cart');
add_action('wp_ajax_nopriv_woocommerce_ajax_add_to_cart', 'flatsome_ajax_add_to_cart');
function flatsome_ajax_add_to_cart()
{
    $product_id = absint($_POST['product_id']);
    $quantity   = absint($_POST['quantity']) ?: 1;

    WC()->cart->add_to_cart($product_id, $quantity);

    // Build fragments so the mini cart HTML updates
    WC_AJAX::get_refreshed_fragments();
}

// Return cart fragments after AJAX add to cart
add_filter('woocommerce_add_to_cart_fragments', 'flatsome_refresh_mini_cart_fragment');
function flatsome_refresh_mini_cart_fragment($fragments)
{
    ob_start();
?>
    <span class="header-cart-total">
        <?php echo WC()->cart->get_cart_total(); ?>
    </span>
    <?php
    $fragments['.header-cart-total'] = ob_get_clean();

    ob_start();
    ?>
    <span class="header-cart-count">
        <?php echo WC()->cart->get_cart_contents_count(); ?>
    </span>
<?php
    $fragments['.header-cart-count'] = ob_get_clean();

    return $fragments;
}
