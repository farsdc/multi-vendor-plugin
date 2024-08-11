<?php

if (!class_exists('MultiVendorPlugin')) {
    class MultiVendorPlugin
    {
        public function __construct()
        {
            add_action('init', array($this, 'create_vendor_role'));
            new VendorRegistration();
            new VendorDashboard();
            new ProductManagement();
        }

        public function create_vendor_role()
        {
            add_role('vendor', 'Vendor', array(
                'read' => true,
                'edit_posts' => true,
                'delete_posts' => false,
                'publish_posts' => false, // محصولات جدید باید ابتدا تایید شوند
                'upload_files' => true,
            )
            );
        }
    }
}
