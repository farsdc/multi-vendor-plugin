<?php 
if (!class_exists('VendorProfile')) {
    class VendorProfile
    {
        public function __construct()
        {
            add_action('init', array($this, 'add_rewrite_rules'));
            add_action('template_redirect', array($this, 'template_redirect'));
            add_action('show_user_profile', array($this, 'vendor_profile_fields'));
            add_action('edit_user_profile', array($this, 'vendor_profile_fields'));
            add_action('personal_options_update', array($this, 'save_vendor_profile_fields'));
            add_action('edit_user_profile_update', array($this, 'save_vendor_profile_fields'));
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
            add_action('wp_ajax_upload_vendor_logo', array($this, 'upload_vendor_logo'));
            add_action('wp_ajax_nopriv_upload_vendor_logo', array($this, 'upload_vendor_logo'));
            add_action('wp_ajax_save_vendor_profile', array($this, 'save_vendor_profile_ajax'));
            add_action('wp_ajax_nopriv_save_vendor_profile', array($this, 'save_vendor_profile_ajax'));
            add_shortcode('vendor_profile', array($this, 'vendor_profile_shortcode'));
            add_shortcode('vendor_list_by_current_category', array($this, 'vendor_list_by_current_category'));
            
        }
        
        public function add_rewrite_rules()
        {
           add_rewrite_rule('^vendor/([^/]*)/?', 'index.php?vendor_store=$matches[1]', 'top');
           add_rewrite_tag('%vendor_store%', '([^&]+)');
        }

       /* public function template_redirect()
        {
            $vendor_store = get_query_var('vendor_store');
            if ($vendor_store) {
                $user = get_user_by('slug', $vendor_store);
                if ($user && in_array('vendor', $user->roles)) {
                    include plugin_dir_path(__FILE__) . '../templates/vendor-store-template.php';
                    exit;
                }
            }
        }*/
        public function template_redirect() {
            $vendor_store = get_query_var('vendor_store');
    
            if ($vendor_store) {
                // آیدی صفحه‌ای که می‌خواهید شورتکد در آن بارگذاری شود
                $page_id = 2; // آیدی صفحه وردپرس که شورتکد داخل آن است
        
                // دریافت آدرس صفحه با استفاده از آیدی
                $page_url = get_permalink($page_id);
        
                // جلوگیری از ریدایرکت اگر از قبل در صفحه مقصد باشیم
                if (!is_page($page_id)) {
                    // اضافه کردن پارامتر vendor_store به URL
                    wp_redirect(add_query_arg('vendor_store', $vendor_store, $page_url), 301);
                    exit;
                }
            }
        }

        public function enqueue_scripts()
        {
            wp_enqueue_style('dropzone-css', plugin_dir_url(__FILE__) . '../assets/files/dropzone.min.css');
            wp_enqueue_script('dropzone-js', plugin_dir_url(__FILE__) . '../assets/files/dropzone.min.js', array('jquery'), null, true);
            wp_enqueue_script('vendor-profile-js', plugin_dir_url(__FILE__) . '../assets/js/vendor-profile.js', array('dropzone-js'), null, true);
            wp_localize_script('vendor-profile-js', 'multi_vendor_plugin', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('upload_vendor_logo_nonce')
            ));
        }

        public function vendor_profile_fields($user)
        {
            if (in_array('vendor', $user->roles)) {
                ?>
                <h3><?php _e('Vendor Information', 'multi-vendor-plugin'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th><label for="vendor_shop_name"><?php _e('Shop Name', 'multi-vendor-plugin'); ?></label></th>
                        <td>
                            <input type="text" name="vendor_shop_name" id="vendor_shop_name"
                                value="<?php echo esc_attr(get_the_author_meta('vendor_shop_name', $user->ID)); ?>"
                                class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th><label for="vendor_address"><?php _e('Address', 'multi-vendor-plugin'); ?></label></th>
                        <td>
                            <input type="text" name="vendor_address" id="vendor_address"
                                value="<?php echo esc_attr(get_the_author_meta('vendor_address', $user->ID)); ?>"
                                class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th><label for="vendor_logo"><?php _e('Logo', 'multi-vendor-plugin'); ?></label></th>
                        <td>
                            <input type="hidden" name="vendor_logo" id="vendor_logo"
                                value="<?php echo esc_attr(get_the_author_meta('vendor_logo', $user->ID)); ?>" />
                            <div id="vendor-logo-dropzone" class="dropzone"></div>
                            <img id="vendor-logo-preview" src="<?php echo esc_attr(get_the_author_meta('vendor_logo', $user->ID)); ?>"
                                style="max-width: 100px; margin-top: 10px;" />
                        </td>
                    </tr>
                    <tr>
                        <th><label for="vendor_employees"><?php _e('Number of Employees', 'multi-vendor-plugin'); ?></label></th>
                        <td>
                            <input type="number" name="vendor_employees" id="vendor_employees"
                                value="<?php echo esc_attr(get_the_author_meta('vendor_employees', $user->ID)); ?>"
                                class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th><label for="vendor_categories"><?php _e('Categories', 'multi-vendor-plugin'); ?></label></th>
                        <td>
                            <select name="vendor_categories" id="vendor_categories">
                                <?php
                                $categories = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => false));
                                foreach ($categories as $category) {
                                    echo '<option value="' . esc_attr($category->term_id) . '"' . selected(get_the_author_meta('vendor_categories', $user->ID), $category->term_id, false) . '>' . esc_html($category->name) . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="vendor_phone"><?php _e('Phone Number', 'multi-vendor-plugin'); ?></label></th>
                        <td>
                            <input type="text" name="vendor_phone" id="vendor_phone"
                                value="<?php echo esc_attr(get_the_author_meta('vendor_phone', $user->ID)); ?>"
                                class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th><label for="vendor_mobile"><?php _e('Mobile Number', 'multi-vendor-plugin'); ?></label></th>
                        <td>
                            <input type="text" name="vendor_mobile" id="vendor_mobile"
                                value="<?php echo esc_attr(get_the_author_meta('vendor_mobile', $user->ID)); ?>"
                                class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th><label for="vendor_website"><?php _e('Website', 'multi-vendor-plugin'); ?></label></th>
                        <td>
                            <input type="text" name="vendor_website" id="vendor_website"
                                value="<?php echo esc_attr(get_the_author_meta('vendor_website', $user->ID)); ?>"
                                class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th><label for="vendor_about"><?php _e('About Shop', 'multi-vendor-plugin'); ?></label></th>
                        <td>
                            <textarea name="vendor_about" id="vendor_about" rows="5" class="regular-text"><?php echo esc_textarea(get_the_author_meta('vendor_about', $user->ID)); ?></textarea>
                        </td>
                    </tr>
                </table>
                <?php
            }
        }

        public function save_vendor_profile_fields($user_id)
        {
            if (current_user_can('edit_user', $user_id)) {
                update_user_meta($user_id, 'vendor_shop_name', sanitize_text_field($_POST['vendor_shop_name']));
                update_user_meta($user_id, 'vendor_address', sanitize_text_field($_POST['vendor_address']));
                update_user_meta($user_id, 'vendor_logo', esc_url($_POST['vendor_logo']));
                update_user_meta($user_id, 'vendor_employees', sanitize_text_field($_POST['vendor_employees']));
                update_user_meta($user_id, 'vendor_categories', sanitize_text_field($_POST['vendor_categories']));
                update_user_meta($user_id, 'vendor_phone', sanitize_text_field($_POST['vendor_phone']));
                update_user_meta($user_id, 'vendor_mobile', sanitize_text_field($_POST['vendor_mobile']));
                update_user_meta($user_id, 'vendor_website', esc_url($_POST['vendor_website']));
                update_user_meta($user_id, 'vendor_about', sanitize_textarea_field($_POST['vendor_about']));
                $shop_name = sanitize_text_field($_POST['vendor_shop_name']);
                $slug = sanitize_title($shop_name);
                wp_update_user(array('ID' => $user_id, 'user_nicename' => $slug));
            }
        }

        public function save_vendor_profile_ajax()
        {
            check_ajax_referer('vendor_profile_nonce', 'nonce');

            if (!is_user_logged_in()) {
                wp_send_json_error(array('message' => 'You must be logged in to update your profile.'));
                return;
            }

            $user_id = get_current_user_id();

            if (current_user_can('edit_user', $user_id)) {
                $this->save_vendor_profile_fields($user_id);
                wp_send_json_success(array('message' => 'Profile updated successfully.'));
            } else {
                wp_send_json_error(array('message' => 'You do not have permission to update this profile.'));
            }
        }

        public function upload_vendor_logo()
        {
            check_ajax_referer('upload_vendor_logo_nonce', 'nonce');

            if (!function_exists('wp_handle_upload')) {
                require_once(ABSPATH . 'wp-admin/includes/file.php');
            }

            $uploadedfile = $_FILES['file'];
            $upload_overrides = array('test_form' => false);
            $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

            if ($movefile && !isset($movefile['error'])) {
                wp_send_json_success(array('url' => $movefile['url']));
            } else {
                $error_message = isset($movefile['error']) ? $movefile['error'] : 'An unknown error occurred during file upload.';
                wp_send_json_error(array('message' => $error_message));
            }
        }

        public function vendor_profile_shortcode()
        {
            ob_start();

            if (is_user_logged_in()) {
                $current_user = wp_get_current_user();
                if (in_array('vendor', $current_user->roles)) {
                    ?>
                    <form id="vendor-profile-form">
                        <h3><?php _e('Vendor Information', 'multi-vendor-plugin'); ?></h3>
                        <table class="form-table">
                            <tr>
                                <th><label for="vendor_shop_name"><?php _e('Shop Name', 'multi-vendor-plugin'); ?></label></th>
                                <td>
                                    <input type="text" name="vendor_shop_name" id="vendor_shop_name"
                                        value="<?php echo esc_attr(get_the_author_meta('vendor_shop_name', $current_user->ID)); ?>"
                                        class="regular-text" />
                                </td>
                            </tr>
                            <tr>
                                <th><label for="vendor_address"><?php _e('Address', 'multi-vendor-plugin'); ?></label></th>
                                <td>
                                    <input type="text" name="vendor_address" id="vendor_address"
                                        value="<?php echo esc_attr(get_the_author_meta('vendor_address', $current_user->ID)); ?>"
                                        class="regular-text" />
                                </td>
                            </tr>
                            <tr>
                                <th><label for="vendor_logo"><?php _e('Logo', 'multi-vendor-plugin'); ?></label></th>
                                <td>
                                    <input type="hidden" name="vendor_logo" id="vendor_logo" value="<?php echo esc_attr(get_the_author_meta('vendor_logo', $current_user->ID)); ?>" />
                                    <div id="vendor-logo-dropzone" class="dropzone"></div>
                                    <img id="vendor-logo-preview" src="<?php echo esc_attr(get_the_author_meta('vendor_logo', $current_user->ID)); ?>" style="max-width: 100px; margin-top: 10px;" />
                                </td>
                            </tr>
                            <tr>
                                <th><label for="vendor_employees"><?php _e('Number of Employees', 'multi-vendor-plugin'); ?></label></th>
                                <td>
                                    <input type="number" name="vendor_employees" id="vendor_employees"
                                        value="<?php echo esc_attr(get_the_author_meta('vendor_employees', $current_user->ID)); ?>"
                                        class="regular-text" />
                                </td>
                            </tr>
                            <tr>
                                <th><label for="vendor_categories"><?php _e('Categories', 'multi-vendor-plugin'); ?></th>
                                <td>
                                    <select name="vendor_categories" id="vendor_categories">
                                        <?php
                                        $categories = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => false));
                                        foreach ($categories as $category) {
                                            echo '<option value="' . esc_attr($category->term_id) . '"' . selected(get_the_author_meta('vendor_categories', $current_user->ID), $category->term_id, false) . '>' . esc_html($category->name) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="vendor_phone"><?php _e('Phone Number', 'multi-vendor-plugin'); ?></label></th>
                                <td>
                                    <input type="text" name="vendor_phone" id="vendor_phone"
                                        value="<?php echo esc_attr(get_the_author_meta('vendor_phone', $current_user->ID)); ?>"
                                        class="regular-text" />
                                </td>
                            </tr>
                            <tr>
                                <th><label for="vendor_mobile"><?php _e('Mobile Number', 'multi-vendor-plugin'); ?></label></th>
                                <td>
                                    <input type="text" name="vendor_mobile" id="vendor_mobile"
                                        value="<?php echo esc_attr(get_the_author_meta('vendor_mobile', $current_user->ID)); ?>"
                                        class="regular-text" />
                                </td>
                            </tr>
                            <tr>
                                <th><label for="vendor_website"><?php _e('Website', 'multi-vendor-plugin'); ?></label></th>
                                <td>
                                    <input type="text" name="vendor_website" id="vendor_website"
                                        value="<?php echo esc_attr(get_the_author_meta('vendor_website', $current_user->ID)); ?>"
                                        class="regular-text" />
                                </td>
                            </tr>
                            <tr>
                                <th><label for="vendor_about"><?php _e('About Shop', 'multi-vendor-plugin'); ?></label></th>
                                <td>
                                    <textarea name="vendor_about" id="vendor_about" rows="5" class="regular-text"><?php echo esc_textarea(get_the_author_meta('vendor_about', $current_user->ID)); ?></textarea>
                                </td>
                            </tr>
                        </table>
                        <input type="hidden" name="action" value="save_vendor_profile">
                        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('vendor_profile_nonce'); ?>">
                        <p>
                            <input type="submit" class="button button-primary" value="<?php _e('Save Profile', 'multi-vendor-plugin'); ?>">
                        </p>
                    </form>
                    <?php
                } else {
                    echo '<p>' . __('You are not a vendor.', 'multi-vendor-plugin') . '</p>';
                }
            } else {
                echo '<p>' . __('You need to be logged in to view this page.', 'multi-vendor-plugin') . '</p>';
            }

            return ob_get_clean();
        }

        public function vendor_list_by_current_category()
        {
            if (!is_tax('product_cat')) {
                return '<p>' . __('This shortcode should be used on a product category page.', 'multi-vendor-plugin') . '</p>';
            }

            $category = get_queried_object();

            if (!$category) {
                return '<p>' . __('Invalid category.', 'multi-vendor-plugin') . '</p>';
            }

            $vendors = get_users(
                array(
                    'role' => 'vendor',
                    'meta_query' => array(
                        array(
                            'key' => 'vendor_categories',
                            'value' => $category->term_id,
                            'compare' => 'LIKE',
                        ),
                    ),
                )
            );

            if (empty($vendors)) {
                return '<p>' . __('No vendors found in this category.', 'multi-vendor-plugin') . '</p>';
            }

            ob_start();

            echo '<table class="vendor-list-table">';
            echo '<tr>';
            echo '<th>' . __('Logo', 'multi-vendor-plugin') . '</th>';
            echo '<th>' . __('Shop Name', 'multi-vendor-plugin') . '</th>';
            echo '<th>' . __('Number of Products', 'multi-vendor-plugin') . '</th>';
            echo '<th>' . __('Products', 'multi-vendor-plugin') . '</th>';
            echo '</tr>';

            foreach ($vendors as $vendor) {
                $shop_name = get_the_author_meta('vendor_shop_name', $vendor->ID);
                $shop_url = home_url('/vendor/' . $vendor->user_nicename);
                $shop_logo = get_the_author_meta('vendor_logo', $vendor->ID);
                $products = get_posts(
                    array(
                        'post_type' => 'product',
                        'author' => $vendor->ID,
                        'posts_per_page' => 5,
                    )
                );
                $product_count = count_user_posts($vendor->ID, 'product');

                echo '<tr>';
                echo '<td><img src="' . esc_url($shop_logo) . '" style="max-width: 50px;"></td>';
                echo '<td><a href="' . esc_url($shop_url) . '">' . esc_html($shop_name) . '</a></td>';
                echo '<td>' . esc_html($product_count) . '</td>';
                echo '<td>';
                if (!empty($products)) {
                    echo '<ul>';
                    foreach ($products as $product) {
                        $product_title = get_the_title($product->ID);
                        $product_price = get_post_meta($product->ID, '_price', true);
                        $product_excerpt = wp_trim_words($product->post_excerpt, 30, '...');
                        echo '<li>';
                        echo '<strong>' . esc_html($product_title) . '</strong><br>';
                        echo __('Price', 'multi-vendor-plugin') . ': ' . esc_html($product_price) . '<br>';
                        echo esc_html($product_excerpt);
                        echo '</li>';
                    }
                    echo '</ul>';
                } else {
                    echo __('No products found.', 'multi-vendor-plugin');
                }
                echo '</td>';
                echo '</tr>';
            }

            echo '</table>';
            
            
            
           
            return ob_get_clean();
        }
    }
}

new VendorProfile();
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
