<?php

if (!class_exists('ProductManagement')) {
    class ProductManagement
    {
        public function __construct()
        {
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
            add_action('template_redirect', array($this, 'handle_add_product_form'));
            add_action('wp_ajax_get_category_custom_fields', array($this, 'get_category_custom_fields'));
            add_action('wp_ajax_nopriv_get_category_custom_fields', array($this, 'get_category_custom_fields'));
            add_action('wp_ajax_upload_product_image', array($this, 'handle_upload_product_image'));
            add_action('wp_ajax_nopriv_upload_product_image', array($this, 'handle_upload_product_image'));
            add_shortcode('edit_product', array($this, 'edit_product_form'));
            add_action('template_redirect', array($this, 'handle_edit_product_form'));
        }

        public function enqueue_scripts()
        {
            wp_enqueue_script('jquery');
            wp_enqueue_media();
           // wp_enqueue_script('dropzone', 'https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js', array('jquery'), '5.9.3', true);
           // wp_enqueue_style('dropzone-css', 'https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css', array(), '5.9.3');
            //wp_enqueue_script('add-product-script', '/wp-content/plugins/multi-vendor-plugin/assets/js/add-product.js', array('jquery', 'dropzone'), null, true);
            wp_localize_script(
                'add-product-script',
                'multi_vendor_plugin',
                array(
                    'ajax_url' => admin_url('admin-ajax.php')
                )
            );
        }

        public function handle_upload_product_image()
        {
            check_ajax_referer('save_product', 'save_product_nonce');

            require_once (ABSPATH . 'wp-admin/includes/image.php');
            require_once (ABSPATH . 'wp-admin/includes/file.php');
            require_once (ABSPATH . 'wp-admin/includes/media.php');

            if (!function_exists('wp_handle_upload')) {
                require_once (ABSPATH . 'wp-admin/includes/file.php');
            }

            $is_gallery = is_array($_FILES['file']['name']);
            $files = $is_gallery ? $_FILES['file'] : array('name' => array($_FILES['file']['name']), 'type' => array($_FILES['file']['type']), 'tmp_name' => array($_FILES['file']['tmp_name']), 'error' => array($_FILES['file']['error']), 'size' => array($_FILES['file']['size']));

            // Initialize an array to store attachment IDs
            $attachment_ids = [];

            // Loop through each file and handle the upload
            foreach ($files['name'] as $key => $value) {
                if ($files['name'][$key]) {
                    $file = [
                        'name' => $files['name'][$key],
                        'type' => $files['type'][$key],
                        'tmp_name' => $files['tmp_name'][$key],
                        'error' => $files['error'][$key],
                        'size' => $files['size'][$key]
                    ];

                    $upload_overrides = ['test_form' => false];

                    $movefile = wp_handle_upload($file, $upload_overrides);

                    if ($movefile && !isset($movefile['error'])) {
                        $filename = $movefile['file'];

                        // The ID of the post this attachment is for.
                        $parent_post_id = 0;

                        // Check the type of file. We'll use this as the 'post_mime_type'.
                        $filetype = wp_check_filetype(basename($filename), null);

                        // Get the path to the upload directory.
                        $wp_upload_dir = wp_upload_dir();

                        // Prepare an array of post data for the attachment.
                        $attachment = [
                            'guid' => $wp_upload_dir['url'] . '/' . basename($filename),
                            'post_mime_type' => $filetype['type'],
                            'post_title' => sanitize_file_name(basename($filename)),
                            'post_content' => '',
                            'post_status' => 'inherit'
                        ];

                        // Insert the attachment.
                        $attachment_id = wp_insert_attachment($attachment, $filename, $parent_post_id);

                        // Generate the metadata for the attachment, and update the database record.
                        $attach_data = wp_generate_attachment_metadata($attachment_id, $filename);
                        wp_update_attachment_metadata($attachment_id, $attach_data);

                        // Add the attachment ID to the array
                        $attachment_ids[] = $attachment_id;
                    } else {
                        wp_send_json_error(['message' => $movefile['error']]);
                        return;
                    }
                }
            }

            // Send the array of attachment IDs as the response
            wp_send_json_success(['attachment_ids' => $attachment_ids]);
        }

        public function handle_add_product_form()
        {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product_nonce']) && wp_verify_nonce($_POST['save_product_nonce'], 'save_product')) {
                require_once (ABSPATH . 'wp-admin/includes/image.php');
                require_once (ABSPATH . 'wp-admin/includes/file.php');
                require_once (ABSPATH . 'wp-admin/includes/media.php');

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
                    update_post_meta($product_id, '_vendor_id', get_current_user_id());
                    update_post_meta($product_id, '_stock_status', 'outofstock'); 

                    if (isset($_POST['product_category'])) {
                        wp_set_object_terms($product_id, (int) $_POST['product_category'], 'product_cat');
                    }

                    // ذخیره تصویر اصلی محصول
                    if (isset($_POST['product_image_id']) && !empty($_POST['product_image_id'])) {
                        update_post_meta($product_id, '_thumbnail_id', intval($_POST['product_image_id']));
                    }

                    // ذخیره گالری تصاویر
                    if (!empty($_POST['product_gallery_ids'])) {
                        $gallery_ids = array_map('intval', explode(',', $_POST['product_gallery_ids']));
                        update_post_meta($product_id, '_product_image_gallery', implode(',', $gallery_ids));
                    }

                    // ذخیره ویژگی‌های محصول
                    $attributes = array();

                    // ویژگی‌های محصول از فرم
                    if (!empty($_POST['product_attributes'])) {
                        foreach ($_POST['product_attributes'] as $attribute) {
                            $attr_name = sanitize_text_field($attribute['name']);
                            $attr_value = sanitize_text_field($attribute['value']);
                            $attributes[sanitize_title($attr_name)] = array(
                                'name' => $attr_name,
                                'value' => $attr_value,
                                'is_visible' => 1,
                                'is_taxonomy' => 0
                            );
                        }
                    }

                    // ویژگی‌های دسته‌بندی
                    if (!empty($_POST['category_custom_fields'])) {
                        foreach ($_POST['category_custom_fields'] as $key => $value) {
                            // Check if the field is a checkbox
                            if (is_array($value)) {
                                $value = implode(', ', array_map('sanitize_text_field', $value));
                            } else {
                                $value = sanitize_text_field($value);
                            }
                            $attributes[sanitize_title($key)] = array(
                                'name' => sanitize_text_field($key),
                                'value' => $value,
                                'is_visible' => 1,
                                'is_taxonomy' => 0
                            );
                        }
                    }

                    if (!empty($attributes)) {
                        update_post_meta($product_id, '_product_attributes', $attributes);
                    }

                    wp_redirect(home_url('/vendor-dashboard'));
                    exit;
                }
            }
        }


        public function get_category_custom_fields()
        {
            if (isset($_POST['category_id'])) {
                $category_id = intval($_POST['category_id']);
                $custom_fields = get_term_meta($category_id, 'category_custom_fields', true);

                if (!empty($custom_fields)) {
                    $fields = explode(PHP_EOL, $custom_fields);
                    foreach ($fields as $field) {
                        list($field_name, $field_type, $field_values) = explode('|', $field);
                        $values = explode(',', $field_values);
                        switch (trim($field_type)) {
                            case 'radio':
                                echo '<p><label>' . esc_html($field_name) . '</label>';
                                foreach ($values as $value) {
                                    echo '<input type="radio" name="category_custom_fields[' . esc_attr($field_name) . ']" value="' . esc_attr($value) . '">' . esc_html($value) . ' ';
                                }
                                echo '</p>';
                                break;
                            case 'checkbox':
                                foreach ($values as $value) {
                                    echo '<p><label>' . esc_html($field_name) . '</label><input type="checkbox" name="category_custom_fields[' . esc_attr($field_name) . '][]" value="' . esc_attr($value) . '">' . esc_html($value) . '</p>';
                                }
                                break;
                            case 'select':
                                echo '<p><label>' . esc_html($field_name) . '</label><select name="category_custom_fields[' . esc_attr($field_name) . ']">';
                                foreach ($values as $value) {
                                    echo '<option value="' . esc_attr($value) . '">' . esc_html($value) . '</option>';
                                }
                                echo '</select></p>';
                                break;
                            case 'textarea':
                                echo '<p><label>' . esc_html($field_name) . '</label><textarea name="category_custom_fields[' . esc_attr($field_name) . ']"></textarea></p>';
                                break;
                            default:
                                echo '<p><label>' . esc_html($field_name) . '</label><input type="text" name="category_custom_fields[' . esc_attr($field_name) . ']"></p>';
                                break;
                        }
                    }
                }
            }
            wp_die();
        }

        public function edit_product_form()
        {
            if (!is_user_logged_in()) {
                wp_redirect(wp_login_url());
                exit;
            }

            if (!isset($_GET['product_id']) || !is_numeric($_GET['product_id'])) {
                return __('Invalid product ID', 'multi-vendor-plugin');
            }

            $product_id = intval($_GET['product_id']);

            if (!$this->current_user_can_edit($product_id)) {
                return __('You do not have permission to edit this product', 'multi-vendor-plugin');
            }

            $product = get_post($product_id);

            if (!$product || $product->post_type !== 'product') {
                return __('Invalid product', 'multi-vendor-plugin');
            }

            ob_start();

            ?>

            <h1><?php _e('Edit Product', 'multi-vendor-plugin'); ?></h1>
            <form id="edit-product-form" method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('save_product', 'save_product_nonce'); ?>
                <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>">

                <p>
                    <label for="product_title"><?php _e('Product Title', 'multi-vendor-plugin'); ?></label>
                    <input type="text" id="product_title" name="product_title" value="<?php echo esc_attr($product->post_title); ?>" required>
                </p>
                <p>
                    <label for="product_description"><?php _e('Product Description', 'multi-vendor-plugin'); ?></label>
                    <textarea id="product_description" name="product_description" required><?php echo esc_textarea($product->post_content); ?></textarea>
                </p>
                <p>
                    <label for="product_price"><?php _e('Product Price', 'multi-vendor-plugin'); ?></label>
                    <input type="number" id="product_price" name="product_price" value="<?php echo esc_attr(get_post_meta($product_id, '_price', true)); ?>" required>
                </p>
                <p>
                    <input type="submit" value="<?php _e('Save Product', 'multi-vendor-plugin'); ?>">
                </p>
            </form>

            <?php

            return ob_get_clean();
        }

        public function handle_edit_product_form()
        {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product_nonce']) && wp_verify_nonce($_POST['save_product_nonce'], 'save_product')) {
                if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
                    wp_redirect(home_url('/vendor-dashboard'));
                    exit;
                }

                $product_id = intval($_POST['product_id']);

                if (!$this->current_user_can_edit($product_id)) {
                    wp_redirect(home_url('/vendor-dashboard'));
                    exit;
                }

                $product_data = array(
                    'ID' => $product_id,
                    'post_title' => sanitize_text_field($_POST['product_title']),
                    'post_content' => sanitize_textarea_field($_POST['product_description']),
                );

                $product_id = wp_update_post($product_data);

                if (!is_wp_error($product_id)) {
                    update_post_meta($product_id, '_price', sanitize_text_field($_POST['product_price']));
                }

                wp_redirect(home_url('/vendor-dashboard'));
                exit;
            }
        }

        private function current_user_can_edit($product_id)
        {
            $product = get_post($product_id);
            $current_user = wp_get_current_user();

            // Check if the current user is the author of the product
            if ($product->post_author == $current_user->ID) {
                return true;
            }

            // Optionally, add more checks here (e.g., for admin roles)

            return false;
        }



    }
}

