<form id="vendor-registration-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
    <p>
        <label for="username"><?php _e('نام کاربری فروشنده', 'multi-vendor-plugin'); ?></label>
        <input type="text" name="username" id="username" required>
        <span id="username-check" class="check-status"></span>
    </p>
    <p>
        <label for="email"><?php _e('ایمیل فروشنده', 'multi-vendor-plugin'); ?></label>
        <input type="email" name="email" id="email" required>
    </p>
    <p>
        <label for="password"><?php _e('رمز ورود', 'multi-vendor-plugin'); ?></label>
        <input type="password" name="password" id="password" required>
    </p>
    <p>
        <label for="shop_name"><?php _e('نام فروشگاه', 'multi-vendor-plugin'); ?></label>
        <input type="text" name="shop_name" id="shop_name" required>
        <span id="shop-name-check" class="check-status"></span>
    </p>
    <p>
        <label for="address"><?php _e('آدرس', 'multi-vendor-plugin'); ?></label>
        <input type="text" name="address" id="address" required>
    </p>
    <p>
        <label for="categories"><?php _e('دسته بندی ها فروشندگان', 'multi-vendor-plugin'); ?></label>
        <select name="categories" id="categories" required>
            <?php
            $categories = get_terms(
                array(
                    'taxonomy' => 'product_cat',
                    'hide_empty' => false,
                )
            );
            foreach ($categories as $category) {
                echo '<option value="' . esc_attr($category->term_id) . '">' . esc_html($category->name) . '</option>';
            }
            ?>
        </select>
    </p>
    <p>
        <label for="province"><?php _e('استان', 'multi-vendor-plugin'); ?></label>
        <select name="province" id="province" required>
            <option value=""><?php _e('Select Province', 'multi-vendor-plugin'); ?></option>
            <?php foreach ($provinces as $province): ?>
                <option value="<?php echo esc_attr($province->id); ?>"><?php echo esc_html($province->title); ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label for="city"><?php _e('شهر', 'multi-vendor-plugin'); ?></label>
        <select name="city" id="city" required>
            <option value=""><?php _e('Select City', 'multi-vendor-plugin'); ?></option>
        </select>
    </p>
    <p>
        <label for="phone"><?php _e('شماره ثابت', 'multi-vendor-plugin'); ?></label>
        <input type="text" name="phone" id="phone" required>
    </p>
    <p>
        <label for="mobile"><?php _e('شماره موبایل', 'multi-vendor-plugin'); ?></label>
        <input type="text" name="mobile" id="mobile" required>
    </p>
    <p>
        <label for="website"><?php _e('وبسایت', 'multi-vendor-plugin'); ?></label>
        <input type="url" name="website" id="website">
    </p>
    <p>
        <label for="instagram"><?php _e('صفحه اینستاگرام', 'multi-vendor-plugin'); ?></label>
        <input type="url" name="instagram" id="instagram">
    </p>
    <p>
        <label for="telegram"><?php _e('کانال تلگرام', 'multi-vendor-plugin'); ?></label>
        <input type="url" name="telegram" id="telegram">
    </p>
    <p>
        <label for="product_style"><?php _e('سبک محصولات', 'multi-vendor-plugin'); ?></label>
        <select name="product_style" id="product_style" required>
            <option value="production"><?php _e('تولیدی', 'multi-vendor-plugin'); ?></option>
            <option value="import"><?php _e('واردات', 'multi-vendor-plugin'); ?></option>
            <option value="combined"><?php _e('ترکیبی', 'multi-vendor-plugin'); ?></option>
        </select>
    </p>
    <p>
        <label for="location"><?php _e('موقعیت فروشگاه روی نقشه', 'multi-vendor-plugin'); ?></label>
        <input type="text" name="location" id="location" placeholder="موقعیت خود را با کشیدن مارکر روی نقشه انتخاب کنید">
        <div id="map" style="height: 400px;"></div> <!-- اینجا نقشه گوگل قرار می‌گیرد -->
    </p>
    <input type="hidden" name="action" value="vendor_register">
    <?php wp_nonce_field('vendor_register', 'vendor_register_nonce'); ?>
    <p>
        <input type="submit" value="<?php _e('ثبت نام کنید', 'multi-vendor-plugin'); ?>" id="register-button">
    </p>
</form>
