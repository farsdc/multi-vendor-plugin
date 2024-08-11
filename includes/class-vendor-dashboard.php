<?php

if (!class_exists('VendorDashboard')) {
    class VendorDashboard
    {
        public function __construct()
        {
            add_shortcode('vendor_dashboard', array($this, 'vendor_dashboard_page'));
            add_shortcode('add_product_form', array($this, 'add_product_form'));
        }

        public function vendor_dashboard_page()
        {
            ob_start();
            include plugin_dir_path(__FILE__) . '../templates/vendor-dashboard.php';
            return ob_get_clean();
        }

        public function add_product_form()
        {
            ob_start();
            include plugin_dir_path(__FILE__) . '../templates/add-product-form.php';
            return ob_get_clean();
        }
        public function vendor_dashboard_menu()
        {
            add_menu_page(
                __('Vendor Dashboard', 'multi-vendor-plugin'),
                __('Vendor Dashboard', 'multi-vendor-plugin'),
                'read',
                'vendor-dashboard',
                array($this, 'vendor_dashboard_page'),
                'dashicons-store',
                20
            );

            add_submenu_page(
                'vendor-dashboard',
                __('Profile', 'multi-vendor-plugin'),
                __('Profile', 'multi-vendor-plugin'),
                'read',
                'vendor-profile',
                array($this, 'vendor_profile_page')
            );
        }

        public function vendor_profile_page()
        {
            echo do_shortcode('[vendor_profile]');
        }

    }
}
