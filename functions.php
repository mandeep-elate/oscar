<?php
if (!function_exists('breakdance_zero_theme_setup')) {
    function breakdance_zero_theme_setup()
    {
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
    }
}
add_action('after_setup_theme', 'breakdance_zero_theme_setup');
add_action('admin_notices', 'warn_if_breakdance_is_disabled');
function warn_if_breakdance_is_disabled()
{
    if (defined('__BREAKDANCE_DIR__')) {
        return;
    }
?>
<div class="notice notice-error is-dismissible">
    <p>You're using Breakdance's Zero Theme but Breakdance is not enabled. This isn't supported.</p>
</div>
<?php
}
function enqueue_elate_script()
{
    wp_enqueue_style(
        'theme-style',
        get_template_directory_uri() . '/assets/css/oscar.css',
        array(),
        null,
        'all'
    );
    /* wp_enqueue_style(
        'theme-style-mandeep',
        get_template_directory_uri() . '/assets/css/mandeep.css',
        array(),
        null,
        'all'
    ); */
    wp_enqueue_script(
        'theme-oscar',
        get_template_directory_uri() . '/assets/js/oscar.js',
        array(),
        null,
        true
    );
    wp_enqueue_script('trustpilot', 'https://widget.trustpilot.com/bootstrap/v5/tp.widget.bootstrap.min.js');
}
add_action('wp_enqueue_scripts', 'enqueue_elate_script');
/* function iconic_was_loop_position( $position ) {
	return 'woocommerce_shop_loop_item_title';
}
add_filter( 'iconic_was_loop_position', 'iconic_was_loop_position', 10, 1 ); */
function filter_by_stock_status($query)
{
    if (is_shop() && isset($_GET['availability'])) {
        $meta_query = $query->get('meta_query');
        $availability = sanitize_text_field($_GET['availability']);
        if ($availability === 'in_stock') {
            $meta_query[] = array(
                'key'     => '_stock_status',
                'value'   => 'instock',
                'compare' => '='
            );
        } elseif ($availability === 'out_of_stock') {
            $meta_query[] = array(
                'key'     => '_stock_status',
                'value'   => 'outofstock',
                'compare' => '='
            );
        }
        $query->set('meta_query', $meta_query);
    }
}
add_action('woocommerce_product_query', 'filter_by_stock_status');
add_filter('woocommerce_cross_sells_total', 'cross_sells_limit');
function cross_sells_limit()
{
    return -1;
}
function new_excerpt_more($more)
{
    return '...';
}
add_filter('excerpt_more', 'new_excerpt_more');
function custom_excerpt_length($length)
{
    return 30; //change the number for the length you want
}
add_filter('excerpt_length', 'custom_excerpt_length', 999);
// Advanced custom fields
if (function_exists('acf_add_options_page')) {
    acf_add_options_page(array(
        'page_title'     => 'Oscar & Hooch settings',
        'menu_title'    => 'Oscar & Hooch',
        'menu_slug'     => 'theme-global-settings',
        'capability'    => 'edit_posts',
        'redirect'        => false,
    ));
}
function display_size_chart()
{
    // Get the field value from ACF Options Page
    $size_chart = get_field('size_chart', 'option');
    // Check if the field has content
    if (!empty($size_chart)) {
        return $size_chart;
    } else {
        return 'Size chart not available.';
    }
}
// Register the shortcode
add_shortcode('size_chart', 'display_size_chart');
add_filter('woocommerce_catalog_orderby', 'remove_sort_by_rating');
function remove_sort_by_rating($options)
{
    if (isset($options['rating'])) {
        unset($options['rating']); // Removes "Sort by average rating"
    }
    return $options;
}
/* function filter_search_query($query)
{
    if (!is_admin() && $query->is_search()) {
        $query->set('post_type', 'product'); // Show only products
    }
    return $query;
}
add_filter('pre_get_posts', 'filter_search_query'); */
add_action('woocommerce_single_product_summary', 'custom_variation_description', 20);
function custom_variation_description()
{
    echo '<div class="woocommerce-product-details__short-description"></div>';
}

// Add this code to your theme's functions.php or a custom plugin
add_action('wp_head', 'custom_json_ld_for_woocommerce_products');

function custom_json_ld_for_woocommerce_products()
{
    if (is_product()) {
        global $product;
        if (!$product) {
            return;
        }

        // Build the basic JSON-LD structure for the product
        $json_ld = array(
            '@context'    => 'https://schema.org/',
            '@type'       => 'Product',
            //'@id'         => $product->get_id(),
            'name'        => $product->get_name(),
            'image'       => wp_get_attachment_url($product->get_image_id()),
            'description' => wp_strip_all_tags($product->get_description()),
            'sku'         => $product->get_sku(),
            'brand' => array(
                '@type' => 'Brand',
                'name' => 'OSCAR & HOOCH',
                'logo' => array(
                    '@type' => 'ImageObject',
                    'url' => 'https://www.oscarandhooch.com/wp-content/uploads/2024/12/logo.png'
                )
            ),
        );


        // Set priceValidUntil date (next 15 days)
        $price_valid_until = date('Y-m-d', strtotime('+15 days'));

        // For simple products: output a single offer
        if ($product->is_type('simple')) {
            $json_ld['offers'] = array(
                '@type'         => 'Offer',
                'url'           => get_permalink($product->get_id()),
                'productID'   => $product->get_id(),
                'priceCurrency' => get_woocommerce_currency(),
                'price'         => $product->get_price(),
                'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'priceValidUntil' => $price_valid_until
            );
        }
        // For variable products: loop through each variation
        elseif ($product->is_type('variable')) {
            $variations = $product->get_available_variations();
            $prices = array_map(function ($variation) {
                return $variation['display_price'];
            }, $variations);

            $low_price = !empty($prices) ? min($prices) : $product->get_price();
            $high_price = !empty($prices) ? max($prices) : $product->get_price();

            $offers = array();

            $offers[] = array(
                '@type' => 'AggregateOffer',
                'url' => get_permalink($product->get_id()),
                'priceCurrency' => get_woocommerce_currency(),
                'lowPrice' => $low_price,
                'highPrice' => $high_price,
                'availability' => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'priceValidUntil' => $price_valid_until
            );

            foreach ($variations as $variation) {
                $variation_name = $product->get_name();

                if (!empty($variation['attributes']['attribute_pa_colour'])) {
                    $color = $variation['attributes']['attribute_pa_colour'];
                    $variation_name .= ' - ' . $color;
                } else {
                    $color = null;
                }

                if (!empty($variation['attributes']['attribute_pa_size'])) {
                    $size = $variation['attributes']['attribute_pa_size'];
                    $variation_name .= ' / ' . $size;
                } else {
                    $size = null;
                }

                $offers[] = array(
                    '@type'             => 'Offer',
                    'priceCurrency'     => get_woocommerce_currency(),
                    'price'             => $variation['display_price'],
                    'availability'      => !empty($variation['is_in_stock']) && $variation['is_in_stock'] ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                    'name'              => $variation_name,
                    'productID'         => $variation['variation_id'],
                    'gtin'              => $variation['sku'],
                    'url'               => $variation['variation_permalink'],
                    'priceValidUntil'   => $price_valid_until
                );

                if ($color) {
                    $offers[count($offers) - 1]['color'] = $color;
                }

                if ($size) {
                    $offers[count($offers) - 1]['size'] = $size;
                }
            }
            $json_ld['offers'] = $offers;
        }

        // Output the JSON-LD
        echo '<script type="application/ld+json">' . wp_json_encode($json_ld) . '</script>';
    }
}


add_action('wp_print_styles', 'zgwd_dequeue_styles');
function zgwd_dequeue_styles()
{
    if (! is_user_logged_in()) {
        wp_dequeue_style('dashicons');
        wp_deregister_style('dashicons');
    }
}