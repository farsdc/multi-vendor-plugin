<?php
// جلوگیری از دسترسی مستقیم به فایل
if (!defined('ABSPATH')) {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_data = array(
        'post_title' => sanitize_text_field($_POST['product_title']),
        'post_content' => sanitize_textarea_field($_POST['product_description']),
        'post_status' => 'pending',
        'post_type' => 'product',
        'post_author' => get_current_user_id(),
    );

    $product_id = wp_insert_post($product_data);

    if (!is_wp_error($product_id)) {
        update_post_meta($product_id, '_price', sanitize_text_field($_POST['product_price']));
        wp_set_object_terms($product_id, (int) $_POST['product_category'], 'product_cat');
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Product added successfully and is pending review.', 'multi-vendor-plugin') . '</p></div>';
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>' . __('Error adding product.', 'multi-vendor-plugin') . '</p></div>';
    }
}
?>

<h1><?php _e('Add New Product', 'multi-vendor-plugin'); ?></h1>
<form method="post">
    <p>
        <label for="product_title"><?php _e('Product Title', 'multi-vendor-plugin'); ?></label>
        <input type="text" id="product_title" name="product_title" required>
    </p>
    <p>
        <label for="product_description"><?php _e('Product Description', 'multi-vendor-plugin'); ?></label>
        <textarea id="product_description" name="product_description" required></textarea>
    </p>
    <p>
        <label for="product_price"><?php _e('Product Price', 'multi-vendor-plugin'); ?></label>
        <input type="number" id="product_price" name="product_price" required>
    </p>
    <p>
        <label for="product_category"><?php _e('Product Category', 'multi-vendor-plugin'); ?></label>
        <?php
        wp_dropdown_categories(
            array(
                'taxonomy' => 'product_cat',
                'hide_empty' => false,
                'name' => 'product_category',
                'id' => 'product_category',
                'class' => 'postform',
                'show_option_none' => __('Select a category', 'multi-vendor-plugin'),
            )
        );
        ?>
    </p>
    <p>
        <input type="submit" value="<?php _e('Add Product', 'multi-vendor-plugin'); ?>">
    </p>
</form>