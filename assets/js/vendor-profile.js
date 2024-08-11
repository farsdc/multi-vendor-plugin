Dropzone.autoDiscover = false;

jQuery(document).ready(function ($) {
  var vendorLogoDropzone = new Dropzone("#vendor-logo-dropzone", {
    url: multi_vendor_plugin.ajax_url,
    paramName: "file",
    maxFiles: 1,
    acceptedFiles: "image/*",
    addRemoveLinks: true,
    sending: function (file, xhr, formData) {
      formData.append("action", "upload_vendor_logo");
      formData.append("nonce", multi_vendor_plugin.nonce);
    },
    success: function (file, response) {
      if (response && response.success) {
        $("#vendor_logo").val(response.data.url);
        $("#vendor-logo-preview").attr("src", response.data.url);
      } else {
        alert(
          response.data ? response.data.message : "An unknown error occurred."
        );
      }
    },
    error: function (file, response) {
      alert(
        response && response.message
          ? response.message
          : "An error occurred while uploading the file."
      );
    },
    removedfile: function (file) {
      file.previewElement.remove();
      $("#vendor_logo").val("");
      $("#vendor-logo-preview").attr("src", "");
    },
  });

  $("#vendor-profile-form").on("submit", function (e) {
    e.preventDefault();

    var formData = $(this).serialize();

    $.post(multi_vendor_plugin.ajax_url, formData, function (response) {
      if (response.success) {
        alert("Profile updated successfully.");
      } else {
        alert(
          response.data
            ? response.data.message
            : "An error occurred while updating the profile."
        );
      }
    });
  });
});
