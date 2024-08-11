<?php

if (!class_exists('VendorManagement')) {
    class VendorManagement
    {
        public function __construct()
        {
            add_action('admin_menu', array($this, 'add_vendor_management_page'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
            add_action('wp_ajax_update_vendor_status', array($this, 'update_vendor_status'));
        }

        public function add_vendor_management_page()
        {
            add_menu_page(
                __('Vendor Management', 'multi-vendor-plugin'),
                __('Vendor Management', 'multi-vendor-plugin'),
                'manage_options',
                'vendor-management',
                array($this, 'render_vendor_management_page'),
                'dashicons-admin-users',
                56
            );
        }

        public function enqueue_scripts($hook)
        {
            if ($hook !== 'toplevel_page_vendor-management') {
                return;
            }

            wp_enqueue_script('vendor-management-js', plugin_dir_url(__FILE__) . '../assets/js/vendor-management.js', array('jquery'), null, true);
            wp_localize_script('vendor-management-js', 'multi_vendor_plugin', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('update_vendor_status_nonce')
            )
            );
        }

        public function render_vendor_management_page()
        {
            $args = array(
                'role' => 'vendor'
            );

            $user_query = new WP_User_Query($args);
            $vendors = $user_query->get_results();

            echo '<div class="wrap">';
            echo '<h1>' . __('Vendor Management', 'multi-vendor-plugin') . '</h1>';
            echo '<table class="wp-list-table widefat fixed striped users">';
            echo '<thead><tr>';
            echo '<th scope="col" id="username" class="manage-column column-username">' . __('Username', 'multi-vendor-plugin') . '</th>';
            echo '<th scope="col" id="mobile" class="manage-column column-mobile">' . __('Mobile', 'multi-vendor-plugin') . '</th>';
            echo '<th scope="col" id="logo" class="manage-column column-logo">' . __('Logo', 'multi-vendor-plugin') . '</th>';
            echo '<th scope="col" id="shop_name" class="manage-column column-shop_name">' . __('Shop Name', 'multi-vendor-plugin') . '</th>';
            echo '<th scope="col" id="address" class="manage-column column-address">' . __('Address', 'multi-vendor-plugin') . '</th>';
            echo '<th scope="col" id="status" class="manage-column column-status">' . __('Status', 'multi-vendor-plugin') . '</th>';
            echo '</tr></thead>';
            echo '<tbody id="the-list">';

            if (!empty($vendors)) {
                foreach ($vendors as $vendor) {
                    $vendor_logo = get_user_meta($vendor->ID, 'vendor_logo', true);
                    $vendor_shop_name = get_user_meta($vendor->ID, 'vendor_shop_name', true);
                    $vendor_address = get_user_meta($vendor->ID, 'vendor_address', true);
                    $vendor_mobile = get_user_meta($vendor->ID, 'vendor_mobile', true);
                    $vendor_status = get_user_meta($vendor->ID, 'user_status', true);

                    echo '<tr>';
                    echo '<td class="username column-username has-row-actions">' . esc_html($vendor->user_login) . '</td>';
                    echo '<td class="mobile column-mobile">' . esc_html($vendor_mobile) . '</td>';
                    echo '<td class="logo column-logo"><img src="' . esc_url($vendor_logo) . '" width="50" height="50"></td>';
                    echo '<td class="shop_name column-shop_name">' . esc_html($vendor_shop_name) . '</td>';
                    echo '<td class="address column-address">' . esc_html($vendor_address) . '</td>';
                    echo '<td class="status column-status">';
                    echo '<select class="vendor-status" data-vendor-id="' . esc_attr($vendor->ID) . '">';
                    echo '<option value="1"' . selected($vendor_status, 1, false) . '>' . __('Pending', 'multi-vendor-plugin') . '</option>';
                    echo '<option value="0"' . selected($vendor_status, 0, false) . '>' . __('Approved', 'multi-vendor-plugin') . '</option>';
                    echo '</select>';
                    echo '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="6">' . __('No vendors found', 'multi-vendor-plugin') . '</td></tr>';
            }

            echo '</tbody></table>';
            echo '</div>';
        }

        public function update_vendor_status()
        {
            check_ajax_referer('update_vendor_status_nonce', 'nonce');

            if (isset($_POST['vendor_id']) && isset($_POST['vendor_status'])) {
                $vendor_id = intval($_POST['vendor_id']);
                $vendor_status = intval($_POST['vendor_status']);

                update_user_meta($vendor_id, 'user_status', $vendor_status);

                wp_send_json_success(array('message' => __('Vendor status updated', 'multi-vendor-plugin')));
            } else {
                wp_send_json_error(array('message' => __('Invalid data', 'multi-vendor-plugin')));
            }
        }
    }
}

new VendorManagement();
