jQuery(document).ready(function ($) {
  $("#province").change(function () {
    var province_id = $(this).val();

    $.post(
      multi_vendor_plugin.ajax_url,
      {
        action: "get_cities",
        province_id: province_id,
        nonce: multi_vendor_plugin.nonce,
      },
      function (response) {
        if (response.success) {
          var $citySelect = $("#city");
          $citySelect.empty();
          $citySelect.append('<option value="">' + "Select City" + "</option>");

          $.each(response.data.cities, function (index, city) {
            $citySelect.append(
              '<option value="' + city.id + '">' + city.title + "</option>"
            );
          });
        }
      }
    );
  });
  
});



// تعریف تابع در سطح سراسری
function initMap() {
    var initialLatLng = {lat: 35.6892, lng: 51.3890}; // مختصات اولیه، مثلاً تهران
    var map = new google.maps.Map(document.getElementById('map'), {
        zoom: 12,
        center: initialLatLng
    });

    var marker = new google.maps.Marker({
        position: initialLatLng,
        map: map,
        draggable: true
    });

    google.maps.event.addListener(marker, 'dragend', function(event) {
        document.getElementById('location').value = event.latLng.lat() + ',' + event.latLng.lng();
    });
}

jQuery(document).ready(function($) {
    // لود نقشه گوگل
    if ($('#map').length) {
        $.getScript('https://maps.googleapis.com/maps/api/js?key=AIzaSyDGpVL0qzzi8hVq2lc8Sav9HMsvEHmZPMw&callback=initMap');
    }
});
