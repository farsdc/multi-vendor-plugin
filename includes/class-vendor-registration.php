<?php

if (!class_exists('VendorRegistration')) {
    class VendorRegistration
    {
        public function __construct()
        {
            add_shortcode('vendor_registration_form', array($this, 'vendor_registration_form'));
            add_action('admin_post_nopriv_vendor_register', array($this, 'handle_vendor_registration'));
            add_action('admin_post_vendor_register', array($this, 'handle_vendor_registration'));
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
            add_action('wp_ajax_get_cities', array($this, 'get_cities'));
            add_action('wp_ajax_nopriv_get_cities', array($this, 'get_cities'));
        }

        public function enqueue_scripts()
        {
            wp_enqueue_script('vendor-registration-js', plugin_dir_url(__FILE__) . '../assets/js/vendor-registration.js', array('jquery'), null, true);
            wp_localize_script('vendor-registration-js', 'multi_vendor_plugin', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('vendor_registration_nonce')
            )
            );
        }

        public function vendor_registration_form()
        {
            ob_start();
            $provinces = $this->get_provinces();
            include plugin_dir_path(__FILE__) . '../templates/vendor-registration-form.php';
            return ob_get_clean();
        }

        public function handle_vendor_registration()
        {
            if (isset($_POST['vendor_register_nonce']) && wp_verify_nonce($_POST['vendor_register_nonce'], 'vendor_register')) {
                $username = sanitize_user($_POST['username']);
                $email = sanitize_email($_POST['email']);
                $password = sanitize_text_field($_POST['password']);
                $shop_name = sanitize_text_field($_POST['shop_name']);
                $shop_name = strtolower(str_replace(' ', '-', $shop_name)); // تبدیل فاصله‌ها به خط تیره
                $shop_name = preg_replace('/[^a-z0-9\-]/', '', $shop_name); // حذف کاراکترهای غیرمجاز
        
                $userdata = array(
                    'user_login' => $username,
                    'user_email' => $email,
                    'user_pass' => $password,
                    'role' => 'vendor',
                );
                $user_id = wp_insert_user($userdata);
        
                if (!is_wp_error($user_id)) {
                    update_user_meta($user_id, 'vendor_shop_name', $shop_name);
                    update_user_meta($user_id, 'vendor_address', sanitize_text_field($_POST['address']));
                    update_user_meta($user_id, 'vendor_categories', intval($_POST['categories']));
                    update_user_meta($user_id, 'vendor_phone', sanitize_text_field($_POST['phone']));
                    update_user_meta($user_id, 'vendor_mobile', sanitize_text_field($_POST['mobile']));
                    update_user_meta($user_id, 'vendor_website', esc_url($_POST['website']));
                    update_user_meta($user_id, 'vendor_instagram', esc_url($_POST['instagram']));
                    update_user_meta($user_id, 'vendor_telegram', esc_url($_POST['telegram']));
                    update_user_meta($user_id, 'vendor_product_style', sanitize_text_field($_POST['product_style']));
                    update_user_meta($user_id, 'vendor_location', sanitize_text_field($_POST['location']));
                    update_user_meta($user_id, 'vendor_province', sanitize_text_field($_POST['province']));
                    update_user_meta($user_id, 'vendor_city', sanitize_text_field($_POST['city']));
                    update_user_meta($user_id, 'vendor_about', sanitize_textarea_field($_POST['about']));
                    update_user_meta($user_id, 'user_status', 'pending'); // اضافه کردن وضعیت "pending"
        
                    wp_redirect(home_url('/registration-success'));
                    exit;
                }
            }
            wp_redirect(home_url('/registration-failed'));
            exit;
        }

        public function get_provinces()
        {
            global $wpdb;
            $provinces = $wpdb->get_results("SELECT id, title FROM province_cities WHERE parent = 0 ORDER BY title ASC");
            return $provinces;
        }

        public function get_cities()
        {
            if (!isset($_POST['province_id'])) {
                wp_send_json_error(array('message' => 'Invalid request'));
            }

            global $wpdb;
            $province_id = intval($_POST['province_id']);
            $cities = $wpdb->get_results($wpdb->prepare("SELECT id, title FROM province_cities WHERE parent = %d ORDER BY title ASC", $province_id));
            wp_send_json_success(array('cities' => $cities));
        }
    }
}

new VendorRegistration();
