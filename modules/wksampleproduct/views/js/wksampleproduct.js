/**
* 2010-2020 Webkul.
*
* NOTICE OF LICENSE
*
* All right is reserved,
* Please go through this link for complete license : https://store.webkul.com/license.html
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to https://store.webkul.com/customisation-guidelines/ for more information.
*
*  @author    Webkul IN <support@webkul.com>
*  @copyright 2010-2020 Webkul IN
*  @license   https://store.webkul.com/license.html
*/
$(document).ready(function() {
    $('#wk_sp_max_qty_error_js').hide();
    $('.wk_sp_sample_action').parent().parent().parent().find('.cart_quantity_down').removeClass('disabled');
    $('.primary_block .pb-right-column #buy_block .content_prices, .primary_block .pb-right-column #buy_block .box-cart-bottom').hide();
    $('#attributes').on('click', function() {
        setTimeout(function() {
            checkSampleInCart();
        }, 1000);
    });
    setTimeout(function() {
        checkSampleInCart();
    }, 1000);
    // $(document).on('change', '#idCombination', function() {
    //     console.log('asd');
    //     checkSampleInCart();
    // });
});
$( document ).ajaxComplete(function(event, request, settings) {
    if (settings.url.indexOf(('samplespecificprice')) == -1) {
        checkSampleInCart();
    }
});
function checkSampleInCart()
{
    if (typeof idPsProduct != "undefined" && (idPsProduct > 0)) {
        $.ajax({
            type: 'POST',
            headers: {
                "cache-control": "no-cache"
            },
            url: sampleSpecificPriceURL +'?rand=' + new Date().getTime(),
            async: false,
            cache: false,
            dataType: "json",
            data: {
                action: 'checkSampleQuantityInCart',
                ajax: 1,
                idProduct :  idPsProduct,
                idAttr: $('#idCombination').val()
            },
            success: function(response) {
                $('#wk_sp_max_qty_error').hide();
                allowedQuantity = response.allowedQty;
                // $('.wk-sample-block').replaceWith(response.sampleTemplate);
                if (response.sampleAdded) {
                    $('.primary_block .pb-right-column #buy_block .content_prices, #center_column .primary_block .pb-right-column #buy_block .box-cart-bottom').hide();
                    $('#wk_sp_sml_prod_error').fadeIn();
                } else {
                    $('.primary_block .pb-right-column #buy_block .content_prices, .primary_block .pb-right-column #buy_block .box-cart-bottom').show();
                    $('#wk_sp_sml_prod_error').fadeOut();
                }
                if (response.standardInCart) {
                    $('#wksamplebuybtn').hide();
                    $('#wk_sp_std_prod_error').fadeIn();
                    $('.primary_block .pb-right-column #buy_block .content_prices, .primary_block .pb-right-column #buy_block .box-cart-bottom').show();
                } else {
                    $('#wksamplebuybtn').show();
                    $('#wk_sp_std_prod_error').fadeOut();
                }
                $('#wk_sp_max_qty_error_js').hide();
            },
        });
    }
}

$(document).off('click', '.wk_product_quantity_up').on('click', '.wk_product_quantity_up', function(e) {
    e.preventDefault();
    $('#wk_sp_max_qty_error_js').hide();
    if ($("#wk_sp_quantity_wanted").val() >= allowedQuantity) {
        $('#wk_sp_max_qty_error').show();
    } else {
        $('#wk_sp_max_qty_error').hide();
        $("#wk_sp_quantity_wanted").val((parseInt($("#wk_sp_quantity_wanted").val()) + 1));
    }
});

$(document).off('click', '.wk_product_quantity_down').on('click', '.wk_product_quantity_down', function(e) {
    e.preventDefault();
    $('#wk_sp_max_qty_error_js').hide();
    if ($("#wk_sp_quantity_wanted").val() > (allowedQuantity + 1)) {
        $('#wk_sp_max_qty_error').show();
    } else {
        $('#wk_sp_max_qty_error').hide();
        $("#wk_sp_quantity_wanted").val((parseInt($("#wk_sp_quantity_wanted").val()) - 1));
    }
});

$(document).off('click', '#wksamplebuybtn').on('click', '#wksamplebuybtn', function(e) {
    e.preventDefault();
    $('#wk_sp_max_qty_error_js').hide();
    $('#wk_sp_std_prod_error').hide();
    $('#wk_sp_sml_prod_error').hide();
    $('#wk_sp_max_qty_error').hide();
    var prod_qty = $("#wk_sp_quantity_wanted").val();
    if ((maxSampleQty > 0) && (prod_qty > allowedQuantity)) {
        $('#wk_sp_max_qty_error_js').fadeIn();
    } else {
        var id_prod = $(this).attr('data-id-product');
        var id_attr = $('#idCombination').val();
        var cart_url = $(this).attr('data-cart-url')+"&qty="+prod_qty;
        $('#'+id_prod+'-'+id_attr+'-loader').removeClass();
        if (id_prod) {
            $.ajax({
                type: 'POST',
                headers: {
                    "cache-control": "no-cache"
                },
                url: sampleSpecificPriceURL +'?rand=' + new Date().getTime(),
                async: false,
                cache: false,
                dataType: "json",
                data: {
                    id_product :  id_prod,
                    id_attr : isNaN(parseInt(id_attr)) ? 0 : parseInt(id_attr),
                },
                success: function(response) {
                    if (('status' in response) && (response.status == 'ok')) {
                        checkSampleInCart();
                        $('#wksamplebuybtn').removeAttr('disabled');
                        ajaxCart.add(id_prod, isNaN(parseInt(id_attr)) ? 0 : parseInt(id_attr), true, null, prod_qty, null);
                    } else if ('hasError' in response) {
                        var errors = '';
                        for(var error in response.msg)
                            //IE6 bug fix
                            if(error !== 'indexOf')
                                errors += $('<div />').html(response.msg[error]).text() + "\n";
                        if (!!$.prototype.fancybox) {
                            $.fancybox.open([
                            {
                                type: 'inline',
                                autoScale: true,
                                minHeight: 30,
                                content: '<p class="fancybox-error">' + errors + '</p>'
                            }],
                            {
                                padding: 0
                            });
                        } else {
                            alert(errors);
                        }
                    }
                },
            });
        }
    }
});
