<?php
// جلوگیری از دسترسی مستقیم به فایل
if (!defined('ABSPATH')) {
    exit;
}

$user_id = get_current_user_id();
$shop_name = get_the_author_meta('vendor_shop_name', $user_id);
$address = get_the_author_meta('vendor_address', $user_id);
$logo = get_the_author_meta('vendor_logo', $user_id);
$employees = get_the_author_meta('vendor_employees', $user_id);
$categories = get_the_author_meta('vendor_categories', $user_id);
?>
<h1><?php echo esc_html($shop_name); ?></h1>
<p><strong><?php _e('Address', 'multi-vendor-plugin'); ?>:</strong> <?php echo esc_html($address); ?></p>
<p><strong><?php _e('Number of Employees', 'multi-vendor-plugin'); ?>:</strong> <?php echo esc_html($employees); ?></p>
<p><strong><?php _e('Categories', 'multi-vendor-plugin'); ?>:</strong> <?php echo esc_html($categories); ?></p>
<img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr($shop_name); ?>">
<?php echo do_shortcode('[vendor_product_list]'); ?>