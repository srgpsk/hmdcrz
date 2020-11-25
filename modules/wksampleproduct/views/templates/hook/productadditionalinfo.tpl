{*
* 2010-2020 Webkul.
*
* NOTICE OF LICENSE
*
* All rights is reserved,
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
*}

<div class="wk-sample-block">
    <div id="wk_sp_max_qty_error_js" class="alert alert-danger" style="display:none;">
        {l s='You have added maximum quantity available for this sample product in cart. Please proceed or delete that cart then you can buy the standard product' mod='wksampleproduct'}
    </div>
    <div id="wk_sp_sml_prod_error" class="alert alert-warning" style="display:none;">
        <i class="icon-warning-sign"></i>
        {l s='You have added this sample product in cart. Please proceed or delete that cart then you can buy the standard product' mod='wksampleproduct'}
    </div>
    <div id="wk_sp_std_prod_error" class="alert alert-warning" style="display:none;">
        <i class="icon-warning-sign"></i>
        {l s='You have added this standard product in cart. Please proceed or delete that cart then you can buy the sample product' mod='wksampleproduct'}
    </div>
    <div id="wk_sp_max_qty_error" class="alert alert-danger" style="display: none;">
        {l s='You can buy maximum ' mod='wksampleproduct'}{$sample.max_cart_qty|escape:'htmlall':'UTF-8'} {l s='samples of this product.' mod='wksampleproduct'}
    </div>
    <span class="control-label">
        <label>{l s='Sample Price' mod='wksampleproduct'}:</label>
        {if isset($samplePrice)}
            {$samplePrice|escape:'htmlall':'UTF-8'}
            {if (($sample.price_type == 4) && ($sample.price_tax == 0)) || ($isTaxExclDisplay)}
                ({l s='Tax excluded' mod='wksampleproduct'})
            {else}
                ({l s='Tax included' mod='wksampleproduct'})
            {/if}
        {else}
            {l s='Free' mod='wksampleproduct'}
        {/if}
    </span>
    <p>{$sample.description|escape:'htmlall':'UTF-8'}</p>
    {if $displayQuantitySpin}
        <p id="wk_sp_quantity_wanted_p">
            <label for="wk_sp_quantity_wanted">{l s='Quantity' mod='wksampleproduct'}</label>
            <input type="number" min="1" name="wkqty" id="wk_sp_quantity_wanted" class="text" value="1">
            <a href="#" data-field-qty="wkqty" class="btn btn-default button-minus wk_product_quantity_down">
                <span><i class="icon-minus"></i></span>
            </a>
            <a href="#" data-field-qty="wkqty" class="btn btn-default button-plus wk_product_quantity_up">
                <span><i class="icon-plus"></i></span>
            </a>
            <span class="clearfix"></span>
        </p>
    {else}
        <input type="hidden" name="wkqty" id="wkquantity_wanted" value="1">
    {/if}
    <button class="btn btn-primary add-to-cart" id="wksamplebuybtn" data-id-product="{$idProduct|escape:'htmlall':'UTF-8'}" data-id-customer="{$idCustomer|escape:'htmlall':'UTF-8'}" data-id-product-attr="0" data-cart-url="{$cartPageURL|escape:'htmlall':'UTF-8'}" {if isset($maxSampleAdded)}style="display:none;"{/if} {if !$addToCartEnabled}disabled{/if} style="{if $sampleBtnBgColor}background:{$sampleBtnBgColor};border:none;{/if}{if $sampleBtnTextColor}color:{$sampleBtnTextColor};{/if}"   >
        <i class="icon-shopping-cart"></i>
        {if empty($sample.button_label)}{l s='Buy Sample' mod='wksampleproduct'}{else}{$sample.button_label|escape:'htmlall':'UTF-8'}{/if}
    </button>
    <span id="product-availability" {if $addToCartEnabled}style="display:none;"{/if}>
        <i class="icon-ban"></i>
        {l s='Sample Out-of-Stock' mod='wksampleproduct'}
    </span>
</div>
