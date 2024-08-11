<?php
function vendor_store_shortcode($atts) {
    $atts = shortcode_atts(array(
        'vendor_slug' => '',
    ), $atts, 'vendor_store');

    $vendor_slug = $atts['vendor_slug'];
    if (!$vendor_slug) {
        global $wp_query;
        $vendor_slug = get_query_var('vendor_store');
    }

    $vendor = get_user_by('slug', $vendor_slug);
    if ($vendor && in_array('vendor', $vendor->roles)) {
        ob_start();
        ?>
        <div class="vendor-store">
            <div class="vendor-header">
                <div class="vendor-logo">
                    <img src="<?php echo esc_url(get_the_author_meta('vendor_logo', $vendor->ID)); ?>" alt="<?php echo esc_attr($vendor->display_name); ?>" style="max-width: 100%;">
                </div>
                <div class="vendor-details">
                    <h1><?php echo esc_html($vendor->display_name); ?></h1>
                    <div class="vendor-about">
                        <h2><?php _e('درباره فروشگاه', 'multi-vendor-plugin'); ?></h2>
                        <p><?php echo esc_html(get_the_author_meta('vendor_about', $vendor->ID)); ?></p>
                    </div>
                    <div class="vendor-contact">
                        <h2><?php _e('تماس با فروشگاه', 'multi-vendor-plugin'); ?></h2>
                        <p><?php _e('شماره ثابت', 'multi-vendor-plugin'); ?>: <?php echo esc_html(get_the_author_meta('vendor_phone', $vendor->ID)); ?></p>
                        <p><?php _e('شماره موبایل', 'multi-vendor-plugin'); ?>: <?php echo esc_html(get_the_author_meta('vendor_mobile', $vendor->ID)); ?></p>
                        <p><?php _e('آدرس فروشگاه', 'multi-vendor-plugin'); ?>: <?php echo esc_html(get_the_author_meta('vendor_address', $vendor->ID)); ?></p>
                        <p><?php _e('آدرس وبسایت فروشگاه :', 'multi-vendor-plugin'); ?>: <a href="<?php echo esc_url(get_the_author_meta('vendor_website', $vendor->ID)); ?>"><?php echo esc_html(get_the_author_meta('vendor_website', $vendor->ID)); ?></a></p>
                    </div>
                </div>
            </div>
            <div class="vendor-products">
                <h3><?php _e('محصولات فروشنده', 'multi-vendor-plugin'); ?></h3>
                <div class="vendor-product-filters">
                    <input type="text" id="vendor-product-search" placeholder="<?php _e('Search Products...', 'multi-vendor-plugin'); ?>">
                    <select id="vendor-product-sort">
                        <option value=""><?php _e('Sort by', 'multi-vendor-plugin'); ?></option>
                        <option value="price_asc"><?php _e('Price: Low to High', 'multi-vendor-plugin'); ?></option>
                        <option value="price_desc"><?php _e('Price: High to Low', 'multi-vendor-plugin'); ?></option>
                        <option value="date"><?php _e('Newest', 'multi-vendor-plugin'); ?></option>
                    </select>
                </div>
                <div id="vendor-products-container"></div>
                <div id="vendor-products-pagination"></div>
            </div>
        </div>
        <script>
            // جاوا اسکریپت برای مرتب‌سازی و فیلتر کردن محصولات
            var vendorID = <?php echo $vendor->ID; ?>;
        </script>
        <?php
        return ob_get_clean();
    } else {
        return '<p>' . __('فروشنده پیدا نشد', 'multi-vendor-plugin') . '</p>';
    }
}
add_shortcode('vendor_store', 'vendor_store_shortcode');
