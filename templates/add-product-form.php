<h3><?php _e('اضافه کردن محصول جدید به خوش استایل', 'multi-vendor-plugin'); ?></h3>
<form id="add-product-form" method="post" enctype="multipart/form-data">
    <?php wp_nonce_field('save_product', 'save_product_nonce'); ?>

    <p>
        <label for="product_title"><?php _e('Product Title', 'multi-vendor-plugin'); ?></label>
        <input type="text" id="product_title" name="product_title" required>
    </p>
    <p>
        <label for="product_description"><?php _e('Product Description', 'multi-vendor-plugin'); ?></label>
        <textarea id="product_description" name="product_description" required></textarea>
    </p>
    <p>
        <label for="product_price"><?php _e('Product Price', 'multi-vendor-plugin'); ?></label>
        <input type="number" id="product_price" name="product_price" required>
    </p>
    <p>
        <label for="product_category"><?php _e('Product Category', 'multi-vendor-plugin'); ?></label>
        <?php
        wp_dropdown_categories(
            array(
                'taxonomy' => 'product_cat',
                'hide_empty' => false,
                'name' => 'product_category',
                'id' => 'product_category',
                'class' => 'postform',
                'show_option_none' => __('Select a category', 'multi-vendor-plugin'),
            )
        );
        ?>
    </p>
    <div id="category_custom_fields_container"></div>
    <p>
        <label for="product_image"><?php _e('Product Image', 'multi-vendor-plugin'); ?></label>
    <div id="product_image_dropzone" class="dropzone"></div>
    <input type="hidden" name="product_image_id" id="product_image_id">
    </p>
    <p>
        <label for="product_gallery"><?php _e('Product Gallery', 'multi-vendor-plugin'); ?></label>
    <div id="product_gallery_dropzone" class="dropzone"></div>
    <input type="hidden" name="product_gallery_ids" id="product_gallery_ids">
    </p>
    <p>
        <label><?php _e('Product Attributes', 'multi-vendor-plugin'); ?></label>
    <div id="product_attributes">
        <div class="product_attribute">
            <input type="text" name="product_attributes[0][name]" placeholder="Attribute Name">
            <input type="text" name="product_attributes[0][value]" placeholder="Attribute Value">
        </div>
    </div>
    <button type="button" id="add_attribute">Add Attribute</button>
    </p>
    <p>
        <input type="submit" value="Add Product">
    </p>
</form>