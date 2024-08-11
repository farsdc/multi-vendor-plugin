Dropzone.autoDiscover = false;

jQuery(document).ready(function ($) {
  if (!Dropzone.instances.length) {
    var productImageDropzone = new Dropzone("#product_image_dropzone", {
      url: multi_vendor_plugin.ajax_url,
      paramName: "file",
      maxFiles: 1,
      acceptedFiles: "image/*",
      addRemoveLinks: true,
      sending: function (file, xhr, formData) {
        formData.append("action", "upload_product_image");
        formData.append(
          "save_product_nonce",
          $('input[name="save_product_nonce"]').val()
        );
      },
      success: function (file, response) {
        console.log("image = " + response.data.attachment_ids); // اشکال‌زدایی
        if (response.success) {
          $("#product_image_id").val(response.data.attachment_ids);
        } else {
          console.log("error: " + response.data.message);
        }
      },
      removedfile: function (file) {
        file.previewElement.remove();
        $("#product_image_id").val("");
      },
    });

    var productGalleryDropzone = new Dropzone("#product_gallery_dropzone", {
      url: multi_vendor_plugin.ajax_url,
      paramName: "file",
      acceptedFiles: "image/*",
      addRemoveLinks: true,
      uploadMultiple: true,
      parallelUploads: 10,
      sending: function (file, xhr, formData) {
        formData.append("action", "upload_product_image");
        formData.append(
          "save_product_nonce",
          $('input[name="save_product_nonce"]').val()
        );
      },
      success: function (file, response) {
        console.log("gallery _id = " + response.data.attachment_ids); // اشکال‌زدایی
        if (response.success) {
          var galleryIds = $("#product_gallery_ids").val().split(",");
          galleryIds.push(response.data.attachment_ids);
          $("#product_gallery_ids").val(galleryIds.join(","));
        } else {
          console.log("error: " + response.data.message);
        }
      },
      removedfile: function (file) {
        file.previewElement.remove();
        var galleryIds = $("#product_gallery_ids").val().split(",");
        var index = galleryIds.indexOf(file.attachment_id);
        if (index > -1) {
          galleryIds.splice(index, 1);
        }
        $("#product_gallery_ids").val(galleryIds.join(","));
      },
    });
  }

  var attributeIndex = 1;
  $("#add_attribute").click(function () {
    $("#product_attributes").append(
      '<div class="product_attribute"><input type="text" name="product_attributes[' +
        attributeIndex +
        '][name]" placeholder="Attribute Name"><input type="text" name="product_attributes[' +
        attributeIndex +
        '][value]" placeholder="Attribute Value"><button type="button" class="remove_attribute">Remove</button></div>'
    );
    attributeIndex++;
  });

  $(document).on("click", ".remove_attribute", function () {
    $(this).parent().remove();
  });

  $("#product_category").change(function () {
    var category_id = $(this).val();
    if (category_id) {
      $.post(
        multi_vendor_plugin.ajax_url,
        {
          action: "get_category_custom_fields",
          category_id: category_id,
        },
        function (response) {
          $("#category_custom_fields_container").html(response);
        }
      );
    } else {
      $("#category_custom_fields_container").html("");
    }
  });
});
