jQuery(document).ready(function($) {
  var numberOfMonths = parseInt(mobicredOptions.numberOfMonths);

  function updateMobiCredInstallment(price) {
    var monthlyInstallment = price / numberOfMonths;
    $('#mobicred-installment').text(numberOfMonths + ' installments of R' + (monthlyInstallment.toFixed(2)));
  }

  $('<img src="' + mobicredOptions.logoUrl + '" alt="MobiCred Logo" />').prependTo('#mobicred-container');
var initialPrice = parseFloat($('.single-product .summary .price .woocommerce-Price-amount:last bdi').text().replace(/[^0-9\.]+/g, ""));


  updateMobiCredInstallment(initialPrice);
  $('#mobicred-container').show();

  if ($('body').hasClass('single-product') && $('form.variations_form').length > 0) {
    var variationsData = JSON.parse($('form.variations_form').attr('data-product_variations'));

    var priceData = {};
    for (var i = 0; i < variationsData.length; i++) {
      var variation = variationsData[i];
      var variationPrice = variation.display_price / numberOfMonths;
      priceData[variation.variation_id] = 'R' + variationPrice.toFixed(2);
    }

    $('body').on('change', 'input.variation_id', function() {
      var selectedVariationId = $('input.variation_id').val();
      if (selectedVariationId !== '') {
        var selectedPrice = priceData[selectedVariationId];
        $('#mobicred-installment').text(numberOfMonths + ' installments of ' + selectedPrice);
      }
    });
  }
});
