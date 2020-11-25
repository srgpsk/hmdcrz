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

$(document).ready(function(){
    displayPriceTypeVal($("#wk_sample_price_type").val());
    $(document).on("change", "#wk_sample_price_type", function(){
        var id_type = $(this).val();
        displayPriceTypeVal(id_type);
    });
    if ($('input[type=radio][name="sample_file_active"]:checked').val() == 0) {
        $('#wk_sp_virtual_sample_input').fadeOut();
    }
    $('input[type=radio][name="sample_file_active"]').on('change', function() {
        if ($(this).val() == 1) {
            $('#wk_sp_virtual_sample_input').fadeIn();
        } else {
            $('#wk_sp_virtual_sample_input').fadeOut();
        }
    });
    $('#uploaded_sample_file-selectbutton').click(function(e) {
        $('#uploaded_sample_file').trigger('click');
    });

    $('#uploaded_sample_file-name').click(function(e) {
        $('#uploaded_sample_file').trigger('click');
    });

    $('#uploaded_sample_file-name').on('dragenter', function(e) {
        e.stopPropagation();
        e.preventDefault();
    });

    $('#uploaded_sample_file-name').on('dragover', function(e) {
        e.stopPropagation();
        e.preventDefault();
    });

    $('#uploaded_sample_file-name').on('drop', function(e) {
        e.preventDefault();
        var files = e.originalEvent.dataTransfer.files;
        $('#uploaded_sample_file')[0].files = files;
        $(this).val(files[0].name);
    });

    $('#uploaded_sample_file').change(function(e) {
        if ($(this)[0].files !== undefined)
        {
            var files = $(this)[0].files;
            var name  = '';

            $.each(files, function(index, value) {
                name += value.name+', ';
            });

            $('#uploaded_sample_file-name').val(name.slice(0, -2));
            if (files[0].size/1000000 > sampleAttachmentMaxSize) {
                alert(sampleMaxSizeError);
                location.reload();
            }
        }
        else // Internet Explorer 9 Compatibility
        {
            var name = $(this).val().split(/[\\/]/);
            $('#uploaded_sample_file-name').val(name[name.length-1]);
        }
    });

    $('#delete_sample_file').on('click', function(e) {
        idProduct = $(this).attr('data-id');
        $('#delete_sample_file_hidden').val(idProduct);
    });
});

function displayPriceTypeVal(id_type)
{
    if (id_type == 2) {
        $(".wk_sample_amount").show();
        $("#wk_sample_sign").show();
        $("#wk_sample_percent").hide();
        $("#wk_sp_amount_label").show();
        $("#wk_sp_percent_label").hide();
        $(".wk_sample_custom_price").hide();
        $(".wk_sample_price_tax").show();
    } else if (id_type == 3) {
        $("#wk_sp_amount_label").hide();
        $("#wk_sp_percent_label").show();
        $(".wk_sample_amount").show();
        $("#wk_sample_sign").hide();
        $("#wk_sample_percent").show();
        $(".wk_sample_custom_price").hide();
        $(".wk_sample_price_tax").hide();
    } else if (id_type == 4) {
        $("#wk_sp_amount_label").hide();
        $("#wk_sp_percent_label").hide();
        $(".wk_sample_custom_price").show();
        $(".wk_sample_amount").hide();
        $(".wk_sample_price_tax").show();
    } else if (id_type == 1) {
        $("#wk_sp_amount_label").hide();
        $("#wk_sp_percent_label").hide();
        $(".wk_sample_amount").hide();
        $(".wk_sample_custom_price").hide();
        $(".wk_sample_price_tax").hide();
    } else {
        $("#wk_sp_amount_label").hide();
        $("#wk_sp_percent_label").hide();
        $(".wk_sample_amount").hide();
        $(".wk_sample_custom_price").hide();
        $(".wk_sample_price_tax").hide();
    }
}
