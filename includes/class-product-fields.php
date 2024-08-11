<?php

if (!class_exists('ProductFields')) {
    class ProductFields
    {
        public function __construct()
        {
            add_action('woocommerce_product_options_general_product_data', array($this, 'add_custom_product_fields'));
            add_action('woocommerce_process_product_meta', array($this, 'save_custom_product_fields'));
        }

        public function add_custom_product_fields()
        {
            global $woocommerce, $post;
            echo '<div class="options_group">';
            woocommerce_wp_text_input(
                array(
                    'id' => '_custom_product_field',
                    'label' => __('Custom Product Field', 'woocommerce'),
                    'placeholder' => 'Custom Product Field',
                    'desc_tip' => 'true',
                    'description' => __('Enter the custom product field value.', 'woocommerce'),
                )
            );
            echo '</div>';
        }

        public function save_custom_product_fields($post_id)
        {
            $custom_product_field = $_POST['_custom_product_field'];
            if (!empty($custom_product_field)) {
                update_post_meta($post_id, '_custom_product_field', esc_attr($custom_product_field));
            }
        }
    }
}
