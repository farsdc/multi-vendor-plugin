<?php
// جلوگیری از دسترسی مستقیم به فایل
if (!defined('ABSPATH')) {
    exit;
}

// فقط برای نقش 'vendor' نمایش داده شود
if (!current_user_can('vendor')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}
?>

<h1><?php _e('Vendor Dashboard', 'multi-vendor-plugin'); ?></h1>
<p><?php _e('Welcome to your dashboard. Here you can manage your products.', 'multi-vendor-plugin'); ?></p>
<a href="<?php echo esc_url(home_url('/add-product')); ?>"><?php _e('Add New Product', 'multi-vendor-plugin'); ?></a>
<?php echo do_shortcode('[vendor_product_list]'); ?>