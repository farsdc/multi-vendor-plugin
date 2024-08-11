jQuery(document).ready(function($) {
    function fetchVendorProducts(page = 1, search = '', sort = '') {
        $.ajax({
            url: vendorAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'fetch_vendor_products',
                vendor_id: vendorID,
                page: page,
                search: search,
                sort: sort,
                nonce: vendorAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#vendor-products-container').html(response.data.products);
                    $('#vendor-products-pagination').html(response.data.pagination);
                } else {
                    $('#vendor-products-container').html('<p>' + response.data.message + '</p>');
                    $('#vendor-products-pagination').html('');
                }
            }
        });
    }

    $('#vendor-product-search').on('keyup', function() {
        var search = $(this).val();
        fetchVendorProducts(1, search, $('#vendor-product-sort').val());
    });

    $('#vendor-product-sort').on('change', function() {
        fetchVendorProducts(1, $('#vendor-product-search').val(), $(this).val());
    });

    $(document).on('click', '.vendor-pagination a', function(e) {
        e.preventDefault();
        var page = $(this).data('page');
        fetchVendorProducts(page, $('#vendor-product-search').val(), $('#vendor-product-sort').val());
    });

    // Load initial products
    fetchVendorProducts();
});
