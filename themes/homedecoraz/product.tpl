{*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{include file="$tpl_dir./errors.tpl"}
{if $errors|@count == 0}
    {if !isset($priceDisplayPrecision)}
        {assign var='priceDisplayPrecision' value=2}
    {/if}
    {if !$priceDisplay || $priceDisplay == 2}
        {assign var='productPrice' value=$product->getPrice(true, $smarty.const.NULL, $priceDisplayPrecision)}
        {assign var='productPriceWithoutReduction' value=$product->getPriceWithoutReduct(false, $smarty.const.NULL)}
    {elseif $priceDisplay == 1}
        {assign var='productPrice' value=$product->getPrice(false, $smarty.const.NULL, $priceDisplayPrecision)}
        {assign var='productPriceWithoutReduction' value=$product->getPriceWithoutReduct(true, $smarty.const.NULL)}
    {/if}


    <div itemscope itemtype="http://schema.org/Product">
        <div class="primary_block row">
            {if isset($adminActionDisplay) && $adminActionDisplay}
                <div id="admin-action">
                    <p>
                        {l s='This product is not visible to your customers.'}
                        <input type="hidden" id="admin-action-product-id" value="{$product->id}" />
                        <input type="submit" value="{l s='Publish'}" name="publish_button" class="exclusive" />
                        <input type="submit" value="{l s='Back'}" name="lnk_view" class="exclusive" />
                    </p>
                    <p id="admin-action-result"></p>
                </div>
            {/if}

            {if isset($confirmation) && $confirmation}
                <p class="confirmation">
                    {$confirmation}
                </p>
            {/if}

{* left infos *}
            <div id="views_block" class="pb-left-column col-xs-12 col-sm-6 col-md-7">
            <div class="scroll-fixed">
            <div class="container flex breadcrumb-wrap">
                {if $page_name ='product'}
                    {include file="$tpl_dir./breadcrumb.tpl"}
                {/if}
            </div>
            <div class="product-image-block clearfix">
            {* product img *}
                <div id="image-block" class="clearfix">
                    {if $product->new}
                        <span class="new-box">
                            <span class="new-label">{l s='New'}</span>
                        </span>
                    {/if}
                    {if $product->on_sale}
                        <span class="sale-box no-print">
                            <span class="sale-label">{l s='Sale!'}</span>
                        </span>
                    {elseif $product->specificPrice && $product->specificPrice.reduction && $productPriceWithoutReduction > $productPrice}
                        <span class="discount">{l s='Reduced price!'}</span>
                    {/if}
                    {if isset($HOOK_PRODUCT_ACTIONS) && $HOOK_PRODUCT_ACTIONS}{$HOOK_PRODUCT_ACTIONS}{/if}


                    {if $have_image}
                        <span id="view_full_size">
                            {if $jqZoomEnabled && $have_image && !$content_only}
                                <a class="jqzoom" title="{if !empty($cover.legend)}{$cover.legend|escape:'html':'UTF-8'}{else}{$product->name|escape:'html':'UTF-8'}{/if}" rel="gal1" href="{$link->getImageLink($product->link_rewrite, $cover.id_image, 'thickbox_default')|escape:'html':'UTF-8'}" itemprop="url">
                                    <img itemprop="image" src="{$link->getImageLink($product->link_rewrite, $cover.id_image, 'large_default')|escape:'html':'UTF-8'}" title="{if !empty($cover.legend)}{$cover.legend|escape:'html':'UTF-8'}{else}{$product->name|escape:'html':'UTF-8'}{/if}" alt="{if !empty($cover.legend)}{$cover.legend|escape:'html':'UTF-8'}{else}{$product->name|escape:'html':'UTF-8'}{/if}"/>
                                </a>
                            {else}
                                <img id="bigpic" itemprop="image" src="{$link->getImageLink($product->link_rewrite, $cover.id_image, 'large_default')|escape:'html':'UTF-8'}" title="{if !empty($cover.legend)}{$cover.legend|escape:'html':'UTF-8'}{else}{$product->name|escape:'html':'UTF-8'}{/if}" alt="{if !empty($cover.legend)}{$cover.legend|escape:'html':'UTF-8'}{else}{$product->name|escape:'html':'UTF-8'}{/if}" width="{$largeSize.width}" height="{$largeSize.height}"/>
                                {if !$content_only}
                                    <span class="span_link no-print">{l s='View larger'}</span>
                                {/if}
                            {/if}
                        </span>
                    {else}
                        <span id="view_full_size">
                            <img itemprop="image" src="{$img_prod_dir}{$lang_iso}-default-large_default.jpg" id="bigpic" alt="" title="{$product->name|escape:'html':'UTF-8'}" width="{$largeSize.width}" height="{$largeSize.height}"/>
                            {if !$content_only}
                                <span class="span_link">
                                    {l s='View larger'}
                                </span>
                            {/if}
                        </span>
                    {/if}
                </div>
            
                {* thumbnails *}
                    {if isset($images) && count($images) > 1}
                        <div id="thumbs" class="clearfix">
                            <ul class="vertical-scroller">
                                {if isset($images)}
                                    {foreach from=$images item=image name=thumbnails}
                                        {assign var=imageIds value="`$product->id`-`$image.id_image`"}
                                        {if !empty($image.legend)}
                                            {assign var=imageTitle value=$image.legend|escape:'html':'UTF-8'}
                                        {else}
                                            {assign var=imageTitle value=$product->name|escape:'html':'UTF-8'}
                                        {/if}
 
                                        <li id="thumbnail_{$image.id_image}" class="">
                                            <a{if $jqZoomEnabled && $have_image && !$content_only} href="javascript:void(0);" rel="{literal}{{/literal}gallery: 'gal1', smallimage: '{$link->getImageLink($product->link_rewrite, $imageIds, 'large_default')|escape:'html':'UTF-8'}',largeimage: '{$link->getImageLink($product->link_rewrite, $imageIds, 'thickbox_default')|escape:'html':'UTF-8'}'{literal}}{/literal}"{else} href="{$link->getImageLink($product->link_rewrite, $imageIds, 'thickbox_default')|escape:'html':'UTF-8'}" data-fancybox-group="other-views" class="fancybox{if $image.id_image == $cover.id_image} shown{/if}"{/if} title="{$imageTitle}">
                                                <img class="img-responsive" id="thumb_{$image.id_image}" src="{$link->getImageLink($product->link_rewrite, $imageIds, 'cart_default')|escape:'html':'UTF-8'}" alt="{$imageTitle}" title="{$imageTitle}" height="{$cartSize.height}" width="{$cartSize.width}" itemprop="image" />
                                            </a>
                                        </li>
                                    {/foreach}
                                {/if}
                            </ul>
                        </div>
                    {/if}
                {* end thumbnails *}
            </div>
            <div class="product-share-wrap">
                {include file="$tpl_dir./product-share.tpl"}
            </div>
</div>
            </div>
{* end left-infos *}


            {hook h='customPopup'}

{* right-infos *}
            <div class="pb-right-column col-xs-12 col-sm-6 col-md-5">
                <div class="section-shortinfo">
                    <div class="row">
                        <div class="col-xs-12 col-md-12">
                            {if $product->online_only}
                                <p class="online_only">{l s='Online only'}</p>
                            {/if}

                            <h1 itemprop="name">{$product->name|escape:'html':'UTF-8'}</h1>
                            {if isset($HOOK_EXTRA_RIGHT) && $HOOK_EXTRA_RIGHT}
                                {$HOOK_EXTRA_RIGHT}
                            {/if}
                            <p id="manufacturer" {if !$manufacturerName}style="display: none;"{/if} item>
                            <a href="{$categoryLink|escape:'html':'UTF-8'}"><span>View More By <u itemprop="brand">{$manufacturerName|escape:'htmlall':'UTF-8'}</u></span></a>
                            </p>

                            {if $product->reference}
                                <p id="product_reference">
                                    <label>{l s='Reference:'} </label>
                                    <span class="editable" itemprop="sku">{if !isset($groups)}{$product->reference|escape:'html':'UTF-8'}{/if}</span>
                                </p>
                            {/if}

                            
                        </div>
                        <div class="content_prices clearfix">
                            {if $product->show_price && !isset($restricted_country_mode) && !$PS_CATALOG_MODE}
                                <div class="price">
                                
                                    <p class="our_price_display" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                                        {if $product->quantity > 0}
                                            <link itemprop="availability" href="http://schema.org/InStock"/>
                                        {/if}
                                         {if $priceDisplay >= 0 && $priceDisplay <= 2}
                                           {if $Chktile == 1 }
                                            <span id="our_price_display" class="price-color" itemprop="price" content="{$displayPrice}">
                                              {if number_format($displayPrice,2) == number_format($product->base_price,2)}
                                                {assign "soldByVal" ""}
                                                    {foreach from=$features item=feature}
                                                        {if isset($feature.name) && $feature.name == "Sold By" && isset($feature.values.0)}
                                                            {assign "soldByVal" {"/"|cat:$feature.values.0}}
                                                        {/if}
                                                    {/foreach}
                                                        {convertPrice price=$displayPrice}{$soldByVal}
                                                    {/if}
                                                    {if $product->unit_price !== 0 && number_format($displayPrice,2) == number_format($unit_price,2) && number_format($unit_price,2) != number_format($product->base_price,2)}
                                                    {convertPrice price=$displayPrice}/{$product->unity}
                                                    {/if}
                                                </span>
                                            {else}
                                                {assign "soldByVal" ""}
                                                    {foreach from=$features item=feature}
                                                        {if isset($feature.name) && $feature.name == "Sold By" && isset($feature.values.0)}
                                                            {assign "soldByVal" {"/"|cat:$feature.values.0}}
                                                        {/if}
                                                    {/foreach}
                                                <span id="our_price_display" class="price-color" itemprop="price" content="{$productPrice}">{convertPrice price=$productPrice}{$soldByVal}</span>
                                            {/if}
                                            <meta itemprop="priceCurrency" content="{$currency->iso_code}" />
                                            <meta itemprop="priceValidUntil" content="" />
                                            <meta itemprop="url" content="https://{$smarty.server.HTTP_HOST}{$smarty.server.REQUEST_URI}" />
                                            {hook h="displayProductPriceBlock" product=$product type="price"}
                                        {/if}

                                        {if $offerPercentage != ''}
                                             <span id="our_price_display" class="original-price" itemprop="price" content="{$product->msrp}"><span>{convertPrice price=$product->msrp}</span> ({$offerPercentage}% Off)</span>
                                             <span itemprop="priceValidUntil"></span>
                                             
                                                <br>
                                           
                                        {/if}
                                        <a href="https://www.homedecoraz.com/tradepro" class="price-link">Shopping for a business? Get Trade Pricing</a>
                                       
                                    </p>
                                    <p id="old_price"{if (!$product->specificPrice || !$product->specificPrice.reduction) && $group_reduction == 0} class="hidden"{/if}>
                                        {if $priceDisplay >= 0 && $priceDisplay <= 2}
                                            {hook h="displayProductPriceBlock" product=$product type="old_price"}
                                            <span id="old_price_display">
                                                {if $productPriceWithoutReduction > $productPrice}
                                                    {convertPrice price=$productPriceWithoutReduction}
                                                {/if}
                                            </span>
                                        {/if}
                                    </p>

                                    {if $priceDisplay == 2}
                                        <br />
                                        <span id="pretaxe_price">
                                            <span id="pretaxe_price_display">{convertPrice price=$product->getPrice(false, $smarty.const.NULL)}</span>
                                            {l s='tax excl.'}
                                        </span>
                                    {/if}
                                </div>

                                {if $packItems|@count && $productPrice < $product->getNoPackPrice()}
                                    <p class="pack_price">{l s='Instead of'} <span style="text-decoration: line-through;">{convertPrice price=$product->getNoPackPrice()}</span></p>
                                {/if}
                                {if $product->ecotax != 0}
                                    <p class="price-ecotax">{l s='Including'} <span id="ecotax_price_display">{if $priceDisplay == 2}{$ecotax_tax_exc|convertAndFormatPrice}{else}{$ecotax_tax_inc|convertAndFormatPrice}{/if}</span> {l s='for ecotax'}
                                        {if $product->specificPrice && $product->specificPrice.reduction}
                                            <br />{l s='(not impacted by the discount)'}
                                        {/if}
                                    </p>
                                {/if}
                            {/if}

                            {hook h="displayProductPriceBlock" product=$product type="weight"}

                            <div class="clear"></div>
                        </div>
                       
                    </div>

                    {* short description *}
                    {if $product->description_short || $packItems|@count > 0}
                        <div id="short_description_block">
                            {if $product->description_short}
                                <div id="short_description_content" class="align_justify" itemprop="description">{$product->description_short}</div>
                            {/if}

                            {if $packItems|@count > 0}
                                <div class="short_description_pack" itemprop="description">
                                    <h3>{l s='Pack content'}</h3>
                                    {foreach from=$packItems item=packItem}
                                        <div class="pack_content">
                                            {$packItem.pack_quantity} x <a href="{$link->getProductLink($packItem.id_product, $packItem.link_rewrite, $packItem.category)|escape:'html':'UTF-8'}">{$packItem.name|escape:'html':'UTF-8'}</a>
                                            <p>{$packItem.description_short}</p>
                                        </div>
                                    {/foreach}
                                </div>
                            {/if}
                        </div>
                    {/if}
                    {* short description *}

                    {* attributes *}
                    {if isset($groups)}
                        <div id="attributes">
                            <div class="clearfix"></div>
                            {foreach from=$groups key=id_attribute_group item=group}
                                {if $group.attributes|@count}
                                    <fieldset class="attribute_fieldset">
                                        <div style="display: inline-block;vertical-align: top; margin-bottom:7px;">
                                            <span class="attribute_label" {if $group.group_type != 'color' && $group.group_type != 'radio'}for="group_{$id_attribute_group|intval}"{/if}>{$group.name|escape:'html':'UTF-8'}:&nbsp;</span>
                                    {*
                                    <span id="theColorname">{$theDefaultColor}</span>
                                    *}
                                        </div>

                                        {assign var="groupName" value="group_$id_attribute_group"}
                                        <div class="attribute_list">
                                            {if ($group.group_type == 'select')}
                                                <select name="{$groupName}" id="group_{$id_attribute_group|intval}" class="form-control attribute_select no-print">
                                                    {foreach from=$group.attributes key=id_attribute item=group_attribute}
                                                        <option value="{$id_attribute|intval}"{if (isset($smarty.get.$groupName) && $smarty.get.$groupName|intval == $id_attribute) || $group.default == $id_attribute} selected="selected"{/if} title="{$group_attribute|escape:'html':'UTF-8'}">{$group_attribute|escape:'html':'UTF-8'}</option>
                                                    {/foreach}
                                                </select>
                                            {elseif ($group.group_type == 'color')}
                                                <ul id="color_to_pick_list" class="clearfix">
                                                    {assign var="default_colorpicker" value=""}
                                                    {foreach from=$group.attributes key=id_attribute item=group_attribute}
                                                        {assign var='img_color_exists' value=file_exists($col_img_dir|cat:$id_attribute|cat:'.jpg')}
                                                        <li{if $group.default == $id_attribute} class="selected"{/if}>
                                                            <a href="{$link->getProductLink($product)|escape:'html':'UTF-8'}" id="color_{$id_attribute|intval}" name="{$colors.$id_attribute.name|escape:'html':'UTF-8'}" class="color_pick{if ($group.default == $id_attribute)} selected{/if}"{if !$img_color_exists && isset($colors.$id_attribute.value) && $colors.$id_attribute.value} style="background:{$colors.$id_attribute.value|escape:'html':'UTF-8'};"{/if} title="{$colors.$id_attribute.name|escape:'html':'UTF-8'}" onclick="colorPickerClick(this);getProductAttribute();$('#theColorname').text('{$colors.$id_attribute.name}');{if $colors|@count > 0}$('#wrapResetImages').show('slow');{/if}">
                                                                {if $img_color_exists}
                                                                    <img src="{$img_col_dir}{$id_attribute|intval}.jpg" alt="{$colors.$id_attribute.name|escape:'html':'UTF-8'}" title="{$colors.$id_attribute.name|escape:'html':'UTF-8'}" width="50" height="50" />
                                                                {/if}
                                                            </a>
                                                        </li>
                                                        {if ($group.default == $id_attribute)}
                                                            {$default_colorpicker = $id_attribute}
                                                        {/if}
                                                    {/foreach}
                                                </ul>
                                                <input type="hidden" class="color_pick_hidden" name="{$groupName|escape:'html':'UTF-8'}" value="{$default_colorpicker|intval}" />
                                            {elseif ($group.group_type == 'radio')}
                                                <ul>
                                                    {foreach from=$group.attributes key=id_attribute item=group_attribute}
                                                        <li>
                                                            <input type="radio" class="attribute_radio" name="{$groupName|escape:'html':'UTF-8'}" value="{$id_attribute}" {if ($group.default == $id_attribute)} checked="checked"{/if} />
                                                            <span>{$group_attribute|escape:'html':'UTF-8'}</span>
                                                        </li>
                                                    {/foreach}
                                                </ul>
                                            {/if}
                                        </div>
                                    </fieldset>
                                {/if}
                            {/foreach}
                        </div>
                    {/if}
{* end attributes *}
                    
                    {if $Chktile != 1 || isset($combinations) && $combinations || $soldBy == 'piece'}
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" placeholder="Enter Quantity Needed" name="qty" id="quantity_wanted" class="form-control" />
                            
                            <div class="btn-outer">
                                <div{if (!$allow_oosp && $product->quantity <= 0) || !$product->available_for_order || (isset($restricted_country_mode) && $restricted_country_mode) || $PS_CATALOG_MODE} class="unvisible"{/if}>
                                    <p id="add_to_cart" class="buttons_bottom_block no-print">
                                       <button type="submit" name="Submit" class="exclusive">
                                           <span>{if $content_only && (isset($product->customization_required) && $product->customization_required)}{l s='Customize'}{else}<i class="icon-shopping-cart"></i> <span>{l s='Add to cart'}</span>{/if}</span>
                                       </button>
                                    </p>
                                </div>
                            </div>





                    	</div>
                    </div>
                    {else}
                        <div {if (!$allow_oosp && $product->quantity <= 0) || !$product->available_for_order || (isset($restricted_country_mode) && $restricted_country_mode) || $PS_CATALOG_MODE} class="unvisible" {else} class="SquareFootageOptions" {/if}>
                           <table width="100%" class="SquareFootageOptions-details">
                              <tbody>                                
                                 <tr>
                                    <td colspan="3">
                                        <table width="100%" class="qty-wrap">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                         <div class="SquareFootageOptions-inputWrap u-clearFix">
                                                          <div class="SquareFootageOptions-input">
                                                             <div class="pl-TextInput-wrapper">
                                                                <div class="pl-TextInput-fieldWrap">
                                                                    <label class="pl-TextInput-field" for="square_footage">
                                                                        <span>Enter Square Feet Needed</span>
                                                                        <input type="text" id="square_footage" name="square_footage" placeholder="Enter" class="form-control" value="">
                                                                    </label>
                                                                </div>
                                                             </div>
                                                          </div>
                                                       </div>
                                                    </td>
                                                    <td class="align-middle">
                                                        <a class="price-link" id="calculateFootage">How many square<br> feet do I need?</a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                 </tr>
                                 <tr>
                                     <td><span class="SquareFootageOptions-unit">Unit</span> Needed</td>
                                     <td><span class="FloatLabel is-active pl-TextInput-label">Coverage Per <span class="SquareFootageOptions-unit-singular">Unit</span></span></td>
                                    <td>Total Sqft Included</td>
                                </tr>
                                <tr>
                                    <td class="SquareFootageOptions-details-number" id="numberOfBox"></td>
                                    <td class="SquareFootageOptions-details-number">{$coverage} sqft</td>
                                    <td class="SquareFootageOptions-details-number" id="totalSqFt"></td>
                                </tr>
                                 <tr>
                                      <td class="SquareFootageOptions-details-description"><strong>Total Price</strong></td>
                                      <td></td>
                                    <td class="SquareFootageOptions-details-number" id="totalPrice"></td>
                                 </tr>
                              </tbody>
                           </table>
                        </div>
                        <div class="box-cart-bottom">
                            <div{if (!$allow_oosp && $product->quantity <= 0) || !$product->available_for_order || (isset($restricted_country_mode) && $restricted_country_mode) || $PS_CATALOG_MODE} class="unvisible"{/if}>
                                <p id="add_to_cart" class="buttons_bottom_block no-print">
                                    <button type="submit" name="Submit" class="exclusive">
                                        <span>{if $content_only && (isset($product->customization_required) && $product->customization_required)}{l s='Customize'}{else}{*<i class="icon-shopping-cart"></i>*} <span>{l s='Add to cart'}</span>{/if}</span>
                                    </button>
                                </p>
                            </div>
                        </div>
                        {/if}


{* number of item in stock *}
                    {if ($display_qties == 1 && !$PS_CATALOG_MODE && $PS_STOCK_MANAGEMENT && $product->available_for_order)}
                        <p id="pQuantityAvailable"{if $product->quantity <= 0} style="display: none;"{/if}>
                            <span id="quantityAvailable">{$product->quantity|intval}</span>
                            <span {if $product->quantity > 1} style="display: none;"{/if} id="quantityAvailableTxt">{l s='Item'}</span>
                            <span {if $product->quantity == 1} style="display: none;"{/if} id="quantityAvailableTxtMultiple">{l s='Items'}</span>
                        </p>
                    {/if}
{* availability *}
                    {if $PS_STOCK_MANAGEMENT}
                        <p id="availability_statut"{if ($product->quantity <= 0 && !$product->available_later && $allow_oosp) || ($product->quantity > 0 && !$product->available_now) || !$product->available_for_order || $PS_CATALOG_MODE} style="display: none;"{/if}>
                            {*<span id="availability_label">{l s='Availability:'}</span>*}
                            <span itemprop="availability" id="availability_value"{if $product->quantity <= 0 && !$allow_oosp} class="warning_inline"{/if}>{if $product->quantity <= 0}{if $allow_oosp}{$product->available_later}{else}{l s='This product is no longer in stock'}{/if}{else}{/if}</span>
                        </p>
                        {hook h="displayProductDeliveryTime" product=$product}
                        <p class="warning_inline" id="last_quantities"{if ($product->quantity > $last_qties || $product->quantity <= 0) || $allow_oosp || !$product->available_for_order || $PS_CATALOG_MODE} style="display: none"{/if} >{l s='Warning: Last items in stock!'}</p>
                    {/if}
                    <p id="availability_date"{if ($product->quantity > 0) || !$product->available_for_order || $PS_CATALOG_MODE || !isset($product->available_date) || $product->available_date < $smarty.now|date_format:'%Y-%m-%d'} style="display: none;"{/if}>
                        <span id="availability_date_label">{l s='ON BACKORDER, ARRIVING '}</span>
                        <span id="availability_date_value">{date('j F Y',strtotime($product->available_date))}<!-- {dateFormat date=$product->available_date full=false} --></span>
                    </p>

{* Out of stock hook *}
                    {if $product->quantity < 1}
                        <div id="oosHook">
                            {$HOOK_PRODUCT_OOS}
                        </div>
                    {/if}
                </div>


                <div class="section-buy">
<!-- add to cart form -->
                    {if ($product->show_price && !isset($restricted_country_mode)) || isset($groups) || $product->reference || (isset($HOOK_PRODUCT_ACTIONS) && $HOOK_PRODUCT_ACTIONS)}
                        <form id="buy_block"{if $PS_CATALOG_MODE && !isset($groups) && $product->quantity > 0} class="hidden"{/if} action="{$link->getPageLink('cart')|escape:'html':'UTF-8'}" method="post">
                            <p class="hidden">
                                <input type="hidden" name="token" value="{$static_token}" />
                                <input type="hidden" name="id_product" value="{$product->id|intval}" id="product_page_product_id" />
                                <input type="hidden" name="add" value="1" />
                                <input type="hidden" name="id_product_attribute" id="idCombination" value="" />
                                <input type="hidden" id="coverageHidden" value="{$coverage}"/>
                            </p>
                            
                            <div class="col-xs-12 col-md-12 productbox-question">
                                <p>
                                    <strong>{l s='Questions about this product?'}</strong><br />
                                    
                                </p>
                                <p class="phone">
                                    <a href="tel:18662320288" title="{l s='Questions? Call us for advice or more details.'}"><i class="fa fa-fw icon-phone"></i> 1-866-232-0288</a>
                                    <span>{l s='Mon. - Fri.: 9 AM - 5 PM EST'}</span>
                                </p>
                                <div>
                                    <div id="dialog-form" title="Tile Calculator" style="display:none;">
                                        <form>
                                            <fieldset>
                                                    
                                                  <div class="dimensions" id="calculator-dimensions-section">
                                                    <div id='validate-calc' style='display:none;'></div>
                                                    <div class="input-box length-vals">
                                                        <label>Length:</label>
                                                        <span class="ft"><input name="calculator_length_ft" id="ft1" class="validate-number" type="text"></span>
                                                        <span class="in"><input name="calculator_length_in" id="in1" class="validate-number" type="text"></span>
                                                    </div>
                                                    <div class="input-box width-vals">
                                                        <label>Width:</label>
                                                        <span class="ft"><input name="calculator_width_ft" id="ft2" class="validate-number" type="text"></span>
                                                        <span class="in"><input name="calculator_width_in"  id="in2" class="validate-number" type="text"></span>
                                                    </div>
                                                </div>
                                               <!--  <label class="pl-TextInput-field">Length<input type="text" name="widthUnit0" id="ft1"><span class="FloatLabel is-active pl-TextInput-label">Ft</span></label>
                                                <label class="pl-TextInput-field"><input type="text" name="widthUnit0" id="in1"><span class="FloatLabel is-active pl-TextInput-label">In</span></label>
                                                <label class="pl-TextInput-field">Width<input type="text" name="widthUnit0" id="ft2"><span class="FloatLabel is-active pl-TextInput-label">Ft</span></label>
                                                <label class="pl-TextInput-field"><input type="text" name="widthUnit0" id="in2"><span class="FloatLabel is-active pl-TextInput-label">In</span></label><br> -->
                                               {*  <input type="text" name="waste_percentage" placeholder="" id="wasteperc" value="10"> *}
                                               
                                                <!--<div class="u-size12of12 Grid-item Grid-item--row Grid-item--wrap" style="padding: 20px 20px 20px 0">
                                                     {*  <span>Subtotal</span>
                                                      <span id="subTotal" style="float: right"> 0 sq. ft</span><br> *}
                                                      {* <span class="FlooringAreaCalc-rowName">Waste</span> *}
                                                      {* <span id="waste" style="float: right">0 sq. ft</span><br> *}
                                                      <span class="FlooringAreaCalc-rowName">Total</span>
                                                      <span id="total" style="float: right">0 sq. ft</span><br>
                                                      <span class="FlooringAreaCalc-rowName">Units Required</span>
                                                      <span id="unitsReq" style="float: right">0</span><br>
                                                      <input type="hidden" value="" id="unitsReqHidden">
                                                      <input type="hidden" value="" id="SqIncludedhidden">
                                                      <input type="hidden" value="" id="totalHidden">
                                                      <span class="FlooringAreaCalc-rowName">Sq ft included</span>
                                                      <span id="SqIncluded" style="float: right">0 sq. ft</span>
                                                </div>-->
                                                <input type="hidden" value="" id="unitsReqHidden">
                                                      <input type="hidden" value="" id="SqIncludedhidden">
                                                      <input type="hidden" value="" id="totalHidden">
                                                 <div class="user-text"></div>
                                                <button type="button" id="updateProductSqFeet">Update Square Feet</button>
                                            </fieldset>
                                        </form>
                                    </div>
                                </div>
                            </div>

                               
                            <div class="wrapper center-block">
                                <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                                    {if isset($product) && $product->description}
                                    <div class="panel panel-default">
                                        <div class="panel-heading active" role="tab" id="headingOne">
                                            <h4 class="panel-title">
                                                <a class="collapsed" role="button" data-toggle="collapse" href="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                                Product Description
                                                </a>
                                            </h4>
                                        </div>
                                        <div id="collapseOne" class="panel-collapse in" role="tabpanel" aria-labelledby="headingOne" style="height: auto;">
                                            <div class="panel-body">
                                            <!-- More info -->
                                                {if $product->description}
                                                    <section id="ptab-info" class="page-product-box" style="display:block;">
                                                    {*  <h2 class="page-product-heading">{l s='Description'}</h2> *}
                                                    <div class="rte">{$product->description}</div>
                                                    </section>
                                                {/if}
                                            <!--end  More info -->
                                            </div>
                                        </div>
                                    </div>
                                    {/if}
                                    {if isset($features) && $features}
                                        <div class="panel panel-default">
                                            <div class="panel-heading" role="tab" id="headingTwo">
                                              <h4 class="panel-title">
                                                <a class="collapsed" role="button" data-toggle="collapse" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                                 Product Specification
                                                </a>
                                              </h4>
                                            </div>
                                            <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
                                              <div class="panel-body">
                                              <!-- Data sheet -->
                                                                 
                                                                      <section id="ptab-data" class="page-product-box" style="display:block;">
                                                                          <table class="table-data-sheet">
                                                                              {foreach from=$features item=feature}
                                                                                  <tr class="{cycle values="even,odd"}">
                                                                                      {if isset($feature.value)}
                                                                                          <td>{$feature.name|escape:'html':'UTF-8'}</td>
                                                                                          <td>{$feature.value|escape:'html':'UTF-8'}</td>
                                                                                      {/if}
                                                                                  </tr>
                                                                              {/foreach}
                                                                          </table>
                                                                      </section>
                                                                 
                                              <!--end Data sheet -->

                                              </div>
                                            </div>
                                        </div>
                                    {/if}
                                    <div class="panel panel-default">
                                        <div class="panel-heading" role="tab" id="headingThree">
                                          <h4 class="panel-title">
                                            <a class="collapsed" role="button" data-toggle="collapse" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                              Reviews
                                            </a>
                                          </h4>
                                        </div>
                                        <div id="collapseThree" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingThree">
                                          <div class="panel-body">
                                            <!--HOOK_PRODUCT_TAB -->
                                                        <section id="ptab-reviews" class="page-product-box" style="display:block;">
                                                            {$HOOK_PRODUCT_TAB}
                                                            {if isset($HOOK_PRODUCT_TAB_CONTENT) && $HOOK_PRODUCT_TAB_CONTENT}{$HOOK_PRODUCT_TAB_CONTENT}{/if}
                                                        </section>
                                    <!--end HOOK_PRODUCT_TAB -->
                                          </div>
                                        </div>
                                    </div>

                            </div>
                            </div> 
                            </div>
<!-- end box-info-product -->

{hook h='hdazCalculatorHook'}

                        </form>
                    {/if}
{* end add to cart form *}
                </div>
            </div>
{* end right infos *}

        </div>
{* end primary_block *}

        {if !$content_only}

<!-- description & features -->
            {if (isset($product) && $product->description) || (isset($features) && $features) || (isset($accessories) && $accessories) || (isset($HOOK_PRODUCT_TAB) && $HOOK_PRODUCT_TAB) || (isset($attachments) && $attachments) || isset($product) && $product->customizable}

                {if (isset($quantity_discounts) && count($quantity_discounts) > 0)}
<!-- quantity discount -->
                    <section class="page-product-box">
                        <h3 class="page-product-heading">{l s='Volume discounts'}</h3>
                        <div id="quantityDiscount">
                            <table class="std table-product-discounts">
                                <thead>
                                    <tr>
                                        <th>{l s='Quantity'}</th>
                                        <th>{if $display_discount_price}{l s='Price'}{else}{l s='Discount'}{/if}</th>
                                        <th>{l s='You Save'}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach from=$quantity_discounts item='quantity_discount' name='quantity_discounts'}
                                        <tr id="quantityDiscount_{$quantity_discount.id_product_attribute}" class="quantityDiscount_{$quantity_discount.id_product_attribute}" data-discount-type="{$quantity_discount.reduction_type}" data-discount="{$quantity_discount.real_value|floatval}" data-discount-quantity="{$quantity_discount.quantity|intval}">
                                            <td>
                                                {$quantity_discount.quantity|intval}
                                            </td>
                                            <td>
                                                {if $quantity_discount.price >= 0 || $quantity_discount.reduction_type == 'amount'}
                                                    {if $display_discount_price}
                                                        {convertPrice price=$productPrice-$quantity_discount.real_value|floatval}
                                                    {else}
                                                        {convertPrice price=$quantity_discount.real_value|floatval}
                                                    {/if}
                                                {else}
                                                    {if $display_discount_price}
                                                        {convertPrice price = $productPrice-($productPrice*$quantity_discount.reduction)|floatval}
                                                    {else}
                                                        {$quantity_discount.real_value|floatval}%
                                                    {/if}
                                                {/if}
                                            </td>
                                            <td>
                                                <span>{l s='Up to'}</span>
                                                {if $quantity_discount.price >= 0 || $quantity_discount.reduction_type == 'amount'}
                                                    {$discountPrice=$productPrice-$quantity_discount.real_value|floatval}
                                                {else}
                                                    {$discountPrice=$productPrice-($productPrice*$quantity_discount.reduction)|floatval}
                                                {/if}
                                                {$discountPrice=$discountPrice*$quantity_discount.quantity}
                                                {$qtyProductPrice = $productPrice*$quantity_discount.quantity}
                                                {convertPrice price=$qtyProductPrice-$discountPrice}
                                            </td>
                                        </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                        </div>
                    </section>
                {/if}
<!-- end quantity discount -->


                {if isset($HOOK_PRODUCT_FOOTER) && $HOOK_PRODUCT_FOOTER}{$HOOK_PRODUCT_FOOTER}{/if}

            {/if}
        {/if}
    </div>

    <div class="primary_block row pull-right col-xs-12">
        <div class="blockproductscategory col-xs-12 col-sm-6 col-md-5 pull-right">{if isset($accessories) && $accessories}
            <h3 class="submenu">Setting Material</h3>
            <div class="container">
                <div class="setting-material row row-eq-height flex hor-scroller">
                      {foreach from=$accessories item=accessory name=accessories_list}
                          {if ($accessory.allow_oosp || $accessory.quantity_all_versions > 0 || $accessory.quantity > 0) && $accessory.available_for_order && !isset($restricted_country_mode)}
                              {assign var='accessoryLink' value=$link->getProductLink($accessory.id_product, $accessory.link_rewrite, $accessory.category)}
                              <div class="col-xs-4 col-sm-2 col-md-4">
                                  <div class="product_desc">
                                      <a href="{$accessoryLink|escape:'html':'UTF-8'}" title="{$accessory.legend|escape:'html':'UTF-8'}" class="product-image product_image">
                                          <img class="lazyOwl" src="{$link->getImageLink($accessory.link_rewrite, $accessory.id_image, 'small_default')|escape:'html':'UTF-8'}" alt="{$accessory.legend|escape:'html':'UTF-8'}" width="71" height="71"/>
                                      </a>
                                  </div>
                                  <p class="product_name">
                                      <a href="{$link->getProductLink($accessory.id_product, $accessory.link_rewrite, $accessory.category, $categoryProduct.ean13)|escape:'html'}" title="{$accessory.name|htmlspecialchars}">{$accessory.name|truncate:35:'...'|escape:'htmlall':'UTF-8'}</a>
                                  </p>
                                
                                  <p class="price_display">
                                      <span class="price">{convertPrice price=$accessory.price}</span>
                                  </p>
                                 
                                  {* <span id="product_reference"{if empty($product->reference) || !$product->reference} style="display: none;"{/if}>
                                      <span  class="editable" itemprop="sku">{if !isset($groups)}{$product->reference|escape:'html':'UTF-8|truncate:8'}{/if}</span>
                                  </span> *}
                              </div>
                          {/if}
                      {/foreach}
                  </div>
                </div>
              {/if}</div>
      </div>
 </div>
{strip}
{if isset($smarty.get.ad) && $smarty.get.ad}
{addJsDefL name=ad}{$base_dir|cat:$smarty.get.ad|escape:'html':'UTF-8'}{/addJsDefL}
{/if}
{if isset($smarty.get.adtoken) && $smarty.get.adtoken}
{addJsDefL name=adtoken}{$smarty.get.adtoken|escape:'html':'UTF-8'}{/addJsDefL}
{/if}
{addJsDef allowBuyWhenOutOfStock=$allow_oosp|boolval}
{addJsDef availableNowValue=$product->available_now|escape:'quotes':'UTF-8'}
{addJsDef availableLaterValue=$product->available_later|escape:'quotes':'UTF-8'}
{addJsDef attribute_anchor_separator=$attribute_anchor_separator|escape:'quotes':'UTF-8'}
{addJsDef attributesCombinations=$attributesCombinations}
{addJsDef currencySign=$currencySign|html_entity_decode:2:"UTF-8"}
{addJsDef currencyRate=$currencyRate|floatval}
{addJsDef currencyFormat=$currencyFormat|intval}
{addJsDef currencyBlank=$currencyBlank|intval}
{addJsDef currentDate=$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}
{if isset($combinations) && $combinations}
{addJsDef combinations=$combinations}
{addJsDef combinationsFromController=$combinations}
{addJsDef displayDiscountPrice=$display_discount_price}
{addJsDefL name='upToTxt'}{l s='Up to' js=1}{/addJsDefL}
{/if}
{if isset($combinationImages) && $combinationImages}
{addJsDef combinationImages=$combinationImages}
{/if}
{addJsDef customizationFields=$customizationFields}
{addJsDef default_eco_tax=$product->ecotax|floatval}
{addJsDef displayPrice=$priceDisplay|intval}
{addJsDef ecotaxTax_rate=$ecotaxTax_rate|floatval}
{addJsDef group_reduction=$group_reduction}
{if isset($cover.id_image_only)}
{addJsDef idDefaultImage=$cover.id_image_only|intval}
{else}
{addJsDef idDefaultImage=0}
{/if}
{addJsDef img_ps_dir=$img_ps_dir}
{addJsDef img_prod_dir=$img_prod_dir}
{addJsDef id_product=$product->id|intval}
{addJsDef jqZoomEnabled=$jqZoomEnabled|boolval}
{addJsDef maxQuantityToAllowDisplayOfLastQuantityMessage=$last_qties|intval}
{addJsDef minimalQuantity=$product->minimal_quantity|intval}
{addJsDef noTaxForThisProduct=$no_tax|boolval}
{addJsDef customerGroupWithoutTax=$customer_group_without_tax|boolval}
{addJsDef oosHookJsCodeFunctions=Array()}
{addJsDef productHasAttributes=isset($groups)|boolval}
{addJsDef productPriceTaxExcluded=($product->getPriceWithoutReduct(true)|default:'null' - $product->ecotax)|floatval}
{addJsDef productBasePriceTaxExcluded=($product->base_price - $product->ecotax)|floatval}
{addJsDef productBasePriceTaxExcl=($product->base_price|floatval)}
{addJsDef productReference=$product->reference|escape:'html':'UTF-8'}
{addJsDef productAvailableForOrder=$product->available_for_order|boolval}
{addJsDef productPriceWithoutReduction=$productPriceWithoutReduction|floatval}
{addJsDef productPrice=$productPrice|floatval}
{addJsDef productUnitPriceRatio=$product->unit_price_ratio|floatval}
{addJsDef productShowPrice=(!$PS_CATALOG_MODE && $product->show_price)|boolval}
{addJsDef PS_CATALOG_MODE=$PS_CATALOG_MODE}
{if $product->specificPrice && $product->specificPrice|@count}
{addJsDef product_specific_price=$product->specificPrice}
{else}
{addJsDef product_specific_price=array()}
{/if}
{if $display_qties == 1 && $product->quantity}
{addJsDef quantityAvailable=$product->quantity}
{else}
{addJsDef quantityAvailable=0}
{/if}
{addJsDef quantitiesDisplayAllowed=$display_qties|boolval}
{if $product->specificPrice && $product->specificPrice.reduction && $product->specificPrice.reduction_type == 'percentage'}
{addJsDef reduction_percent=$product->specificPrice.reduction*100|floatval}
{else}
{addJsDef reduction_percent=0}
{/if}
{if $product->specificPrice && $product->specificPrice.reduction && $product->specificPrice.reduction_type == 'amount'}
{addJsDef reduction_price=$product->specificPrice.reduction|floatval}
{else}
{addJsDef reduction_price=0}
{/if}
{if $product->specificPrice && $product->specificPrice.price}
{addJsDef specific_price=$product->specificPrice.price|floatval}
{else}
{addJsDef specific_price=0}
{/if}
{addJsDef specific_currency=($product->specificPrice && $product->specificPrice.id_currency)|boolval} {* TODO: remove if always false *}
{addJsDef stock_management=$stock_management|intval}
{addJsDef taxRate=$tax_rate|floatval}
{addJsDefL name=doesntExist}{l s='This combination does not exist for this product. Please select another combination.' js=1}{/addJsDefL}
{addJsDefL name=doesntExistNoMore}{l s='This product is no longer in stock' js=1}{/addJsDefL}
{addJsDefL name=doesntExistNoMoreBut}{l s='with those attributes but is available with others.' js=1}{/addJsDefL}
{addJsDefL name=fieldRequired}{l s='Please fill in all the required fields before saving your customization.' js=1}{/addJsDefL}
{addJsDefL name=uploading_in_progress}{l s='Uploading in progress, please be patient.' js=1}{/addJsDefL}
{addJsDefL name='product_fileDefaultHtml'}{l s='No file selected' js=1}{/addJsDefL}
{addJsDefL name='product_fileButtonHtml'}{l s='Choose File' js=1}{/addJsDefL}
{/strip}
{/if}


