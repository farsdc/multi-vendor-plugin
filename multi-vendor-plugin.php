<?php
/*
Plugin Name: Multi Vendor Plugin
Description: A custom multi-vendor plugin for WooCommerce.
Version: 1.0
Author: مهدی نجاریان 09362005446
Text Domain: multi-vendor-plugin
Domain Path: /languages
*/

// جلوگیری از دسترسی مستقیم به فایل
if (!defined('ABSPATH')) {
    exit;
}

// بارگذاری فایل زبان
function multi_vendor_plugin_load_textdomain()
{
    load_plugin_textdomain('multi-vendor-plugin', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'multi_vendor_plugin_load_textdomain');

// شامل کردن فایل‌های کلاس
require_once plugin_dir_path(__FILE__) . 'includes/class-multi-vendor-plugin.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-vendor-registration.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-vendor-dashboard.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-product-management.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-vendor-product-list.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-vendor-management.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-vendor-profile.php';

// اجرای پلاگین
if (class_exists('MultiVendorPlugin')) {
    new MultiVendorPlugin();
}

add_action('product_cat_add_form_fields', 'add_category_custom_fields', 10, 2);
add_action('product_cat_edit_form_fields', 'edit_category_custom_fields', 10, 2);
add_action('edited_product_cat', 'save_category_custom_fields', 10, 2);
add_action('create_product_cat', 'save_category_custom_fields', 10, 2);

function add_category_custom_fields($taxonomy) {
    ?>
    <div class="form-field">
        <label for="category_custom_fields"><?php _e('Custom Fields', 'multi-vendor-plugin'); ?></label>
        <textarea name="category_custom_fields" id="category_custom_fields" rows="5" cols="40"></textarea>
        <p class="description"><?php _e('Enter the custom fields for this category. Format: Field Name|Field Type|Field Values (e.g. "Color|radio|Red,Green,Blue", "Size|checkbox|Small,Medium,Large", "Type|select|Option1,Option2")', 'multi-vendor-plugin'); ?></p>
    </div>
    <?php
}

function edit_category_custom_fields($term, $taxonomy) {
    $custom_fields = get_term_meta($term->term_id, 'category_custom_fields', true);
    ?>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="category_custom_fields"><?php _e('Custom Fields', 'multi-vendor-plugin'); ?></label></th>
        <td>
            <textarea name="category_custom_fields" id="category_custom_fields" rows="5" cols="50"><?php echo esc_textarea($custom_fields); ?></textarea>
            <p class="description"><?php _e('Enter the custom fields for this category. Format: Field Name|Field Type|Field Values (e.g. "Color|radio|Red,Green,Blue", "Size|checkbox|Small,Medium,Large", "Type|select|Option1,Option2")', 'multi-vendor-plugin'); ?></p>
        </td>
    </tr>
    <?php
}

function save_category_custom_fields($term_id) {
    if (isset($_POST['category_custom_fields'])) {
        $custom_fields = sanitize_textarea_field($_POST['category_custom_fields']);
        update_term_meta($term_id, 'category_custom_fields', $custom_fields);
    }
}

function enqueue_dropzone_assets()
{
    wp_enqueue_style('dropzone-css', plugin_dir_url(__FILE__) . 'assets/files/dropzone.min.css');
    wp_enqueue_script('dropzone-js', plugin_dir_url(__FILE__) . 'assets/files/dropzone.min.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_dropzone_assets',20);


function enqueue_add_product_script() {
    wp_enqueue_script('add-product-js', plugin_dir_url(__FILE__) . 'assets/js/add-product.js', array('jquery', 'dropzone-js'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'enqueue_add_product_script',21);





function vendor_store_enqueue_scripts() {
    wp_enqueue_style('vendor-store-css', plugin_dir_url(__FILE__) . 'assets/css/vendor-store.css');
    wp_enqueue_script('vendor-script', plugin_dir_url(__FILE__) . 'assets/js/vendor-store.js', array('jquery'), null, true);
    
    // ایجاد nonce و ارسال آن به جاوااسکریپت
    wp_localize_script('vendor-script', 'vendorAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('vendor_nonce')
    ));
}

add_action('wp_enqueue_scripts', 'vendor_store_enqueue_scripts',22);

            
function fetch_vendor_products() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'vendor_nonce')) {
        wp_send_json_error(array('message' => 'Invalid nonce'));
        return;
    }

    $vendor_id = intval($_POST['vendor_id']);
    $page = intval($_POST['page']);
    $search = sanitize_text_field($_POST['search']);
    $sort = sanitize_text_field($_POST['sort']);

    $args = array(
        'post_type' => 'product',
        'author' => $vendor_id,
        'posts_per_page' => 20,
        'paged' => $page,
        's' => $search,
    );

    if ($sort == 'price_asc') {
        $args['orderby'] = 'meta_value_num';
        $args['meta_key'] = '_price';
        $args['order'] = 'ASC';
    } elseif ($sort == 'price_desc') {
        $args['orderby'] = 'meta_value_num';
        $args['meta_key'] = '_price';
        $args['order'] = 'DESC';
    } elseif ($sort == 'date') {
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        ob_start();
        while ($query->have_posts()) : $query->the_post();
            ?>
            <div class="product-item">
                <h2><?php the_title(); ?></h2>
                <a href="<?php the_permalink(); ?>">
                    <?php if (has_post_thumbnail()) : ?>
                        <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'medium'); ?>" alt="<?php the_title(); ?>">
                    <?php endif; ?>
                </a>
                <p class="price"><?php echo wc_price(get_post_meta(get_the_ID(), '_price', true)); ?></p>
                <p class="description">
                    <?php echo wp_trim_words(get_the_content(), 100, '...'); ?>
                </p>
                <p class="category">
                    <?php echo get_the_term_list(get_the_ID(), 'product_cat', '', ', ', ''); ?>
                </p>
            </div>
            <?php
        endwhile;
        
        $products_html = ob_get_clean();

        $pagination_html = paginate_links(array(
            'total' => $query->max_num_pages,
            'current' => $page,
            'format' => '?page=%#%',
            'prev_text' => __('« Previous', 'multi-vendor-plugin'),
            'next_text' => __('Next »', 'multi-vendor-plugin'),
            'type' => 'list',
            'before_page_number' => '<li>',
            'after_page_number' => '</li>',
        ));

        wp_send_json_success(array(
            'products' => $products_html,
            'pagination' => $pagination_html,
        ));
    } else {
        wp_send_json_error(array('message' => __('No products found', 'multi-vendor-plugin')));
    }

    wp_die();
}
add_action('wp_ajax_fetch_vendor_products', 'fetch_vendor_products');
add_action('wp_ajax_nopriv_fetch_vendor_products', 'fetch_vendor_products');


add_shortcode('vendor_store_page', 'vendor_store_page_shortcode');

function vendor_store_page_shortcode() {
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/vendor-page-template.php';
    return ob_get_clean();
}
