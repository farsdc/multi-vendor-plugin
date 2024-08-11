jQuery(document).ready(function ($) {
  // Initialize DataTables
  $("#vendor-product-list").DataTable({
    paging: true,
    searching: true,
    ordering: true,
    order: [[0, "asc"]],
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, "All"],
    ],
    columnDefs: [{ orderable: false, targets: [2, 4, 7] }],
  });

  // AJAX request to update product price
  $(".product-price").on("change", function () {
    var productId = $(this).data("product-id");
    var productPrice = $(this).val();
    var nonce = multi_vendor_plugin.nonce;

    $.ajax({
      url: multi_vendor_plugin.ajax_url,
      type: "POST",
      data: {
        action: "update_product_price",
        product_id: productId,
        product_price: productPrice,
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
        alert("An error occurred while updating the price.");
      },
    });
  });
});
