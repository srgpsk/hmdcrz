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
    if ($('#WK_GLOBAL_SAMPLE_on').is(':checked')) {
        $('.wk_global_sample_block').fadeIn();
    } else {
        $('.wk_global_sample_block').fadeOut();
    }
    $('input[name="WK_GLOBAL_SAMPLE"]').on('change', function() {
        if ($('#WK_GLOBAL_SAMPLE_on').is(':checked')) {
            $('.wk_global_sample_block').fadeIn();
        } else {
            $('.wk_global_sample_block').fadeOut();
        }
        configureGlobalSamplePrice($('#WK_GLOBAL_SAMPLE_PRICE_TYPE').val());
    });
    configureGlobalSamplePrice($('#WK_GLOBAL_SAMPLE_PRICE_TYPE').val());
    $('#WK_GLOBAL_SAMPLE_PRICE_TYPE').on('change', function() {
        configureGlobalSamplePrice($(this).val());
    });

    function configureGlobalSamplePrice(priceType)
    {
        if ((priceType == 1) || (priceType == 5)) {
            $('.wk_price_type_amount').hide();
            $('.wk_price_type_customprice').hide();
            $('.wk_price_type_tax').hide();
            $('.wk_price_type_percent').hide();
        } else if (priceType == 2) {
            $('.wk_price_type_amount').show();
            $('.wk_price_type_customprice').hide();
            $('.wk_price_type_tax').show();
            $('.wk_price_type_percent').hide();
        } else if (priceType == 3) {
            $('.wk_price_type_amount').hide();
            $('.wk_price_type_customprice').hide();
            $('.wk_price_type_tax').hide();
            $('.wk_price_type_percent').show();
        } else if (priceType == 4) {
            $('.wk_price_type_amount').hide();
            $('.wk_price_type_customprice').show();
            $('.wk_price_type_tax').show();
            $('.wk_price_type_percent').hide();
        }
    }
});