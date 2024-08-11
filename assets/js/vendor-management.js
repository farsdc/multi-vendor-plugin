jQuery(document).ready(function ($) {
  $(".vendor-status").on("change", function () {
    var vendorId = $(this).data("vendor-id");
    var vendorStatus = $(this).val();
    var nonce = multi_vendor_plugin.nonce;

    $.ajax({
      url: multi_vendor_plugin.ajax_url,
      type: "POST",
      data: {
        action: "update_vendor_status",
        vendor_id: vendorId,
        vendor_status: vendorStatus,
        nonce: nonce,
      },
      success: function (response) {
        if (response.success) {
          alert(response.data.message);
        } else {
          alert(response.data.message);
        }
      },
      error: function () {
        alert("An error occurred while updating the vendor status.");
      },
    });
  });
});
