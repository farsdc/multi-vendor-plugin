<?php

class VendorProductList
{
    public function __construct()
    {
        add_shortcode('vendor_product_list', array($this, 'vendor_product_list'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_update_product_price', array($this, 'update_product_price'));
    }

    public function enqueue_scripts()
    {
        // Enqueue local DataTables scripts and styles
        wp_enqueue_style('datatables-style', 'https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css');
        wp_enqueue_script('datatables-script', 'https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js', array('jquery'), null, true);

        // Enqueue custom script for vendor product list
        wp_enqueue_script('vendor-product-list-script', plugin_dir_url(__FILE__) . '../assets/js/vendor-product-list.js', array('jquery', 'datatables-script'), null, true);
        wp_localize_script('vendor-product-list-script', 'multi_vendor_plugin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('update_product_price_nonce')
        )
        );
    }

    public function vendor_product_list()
    {
        if (!is_user_logged_in()) {
            wp_redirect(wp_login_url());
            exit;
        }

        $current_user = wp_get_current_user();
        $args = array(
            'post_type' => 'product',
            'post_status' => array('publish', 'pending', 'draft'),
            'author' => $current_user->ID,
        );

        $query = new WP_Query($args);

        ob_start();

        echo '<h1>' . __('My Products', 'multi-vendor-plugin') . '</h1>';
        echo '<table id="vendor-product-list" class="display">';
        echo '<thead><tr><th>' . __('Product', 'multi-vendor-plugin') . '</th><th>' . __('Description', 'multi-vendor-plugin') . '</th><th>' . __('Price', 'multi-vendor-plugin') . '</th><th>' . __('Date', 'multi-vendor-plugin') . '</th><th>' . __('Image', 'multi-vendor-plugin') . '</th><th>' . __('Status', 'multi-vendor-plugin') . '</th><th>' . __('Views', 'multi-vendor-plugin') . '</th><th>' . __('Actions', 'multi-vendor-plugin') . '</th></tr></thead>';
        echo '<tbody>';

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $product_id = get_the_ID();
                $product_title = get_the_title();
                $product_excerpt = wp_trim_words(get_the_excerpt(), 10);
                $product_price = get_post_meta($product_id, '_price', true);
                $product_date = get_the_date('Y-m-d', $product_id);
                $product_image = get_the_post_thumbnail($product_id, 'thumbnail');
                $product_status = get_post_status($product_id);
                $product_views = get_post_meta($product_id, 'views', true);

                echo '<tr>';
                echo '<td>' . esc_html($product_title) . '</td>';
                echo '<td>' . esc_html($product_excerpt) . '</td>';
                echo '<td><input type="text" class="product-price" data-product-id="' . esc_attr($product_id) . '" value="' . esc_attr($product_price) . '"></td>';
                echo '<td>' . esc_html($product_date) . '</td>';
                echo '<td>' . $product_image . '</td>';
                echo '<td>' . esc_html($product_status) . '</td>';
                echo '<td>' . esc_html($product_views) . '</td>';
                echo '<td><a href="' . esc_url(add_query_arg('product_id', $product_id, home_url('/edit-product'))) . '">' . __('Edit', 'multi-vendor-plugin') . '</a></td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="8">' . __('No products found', 'multi-vendor-plugin') . '</td></tr>';
        }

        echo '</tbody></table>';

        wp_reset_postdata();

        return ob_get_clean();
    }

    public function update_product_price()
    {
        check_ajax_referer('update_product_price_nonce', 'nonce');

        if (isset($_POST['product_id']) && isset($_POST['product_price'])) {
            $product_id = intval($_POST['product_id']);
            $product_price = sanitize_text_field($_POST['product_price']);

            if (current_user_can('edit_post', $product_id)) {
                update_post_meta($product_id, '_price', $product_price);
                wp_send_json_success(array('message' => __('Price updated', 'multi-vendor-plugin')));
            } else {
                wp_send_json_error(array('message' => __('You do not have permission to edit this product', 'multi-vendor-plugin')));
            }
        } else {
            wp_send_json_error(array('message' => __('Invalid data', 'multi-vendor-plugin')));
        }
    }
}

new VendorProductList();
