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
{if isset($products) && $products}
    {*define numbers of product per line in other page for desktop*}
    {if $page_name !='index' && $page_name !='product'}
        {assign var='nbItemsPerLine' value=4}
        {assign var='nbItemsPerLineTablet' value=2}
        {assign var='nbItemsPerLineMobile' value=2}
    {else}
        {assign var='nbItemsPerLine' value=4}
        {assign var='nbItemsPerLineTablet' value=2}
        {assign var='nbItemsPerLineMobile' value=2}
    {/if}
    {*define numbers of product per line in other page for tablet*}
    {assign var='nbLi' value=$products|@count}
    {math equation="nbLi/nbItemsPerLine" nbLi=$nbLi nbItemsPerLine=$nbItemsPerLine assign=nbLines}
    {math equation="nbLi/nbItemsPerLineTablet" nbLi=$nbLi nbItemsPerLineTablet=$nbItemsPerLineTablet assign=nbLinesTablet}

    <!-- Products list -->
    <ul{if isset($id) && $id} id="{$id}"{/if} class="product_list grid row row-eq-height{if isset($class) && $class} {$class}{/if}">
        {foreach from=$products item=product name=products}
            {math equation="(total%perLine)" total=$smarty.foreach.products.total perLine=$nbItemsPerLine assign=totModulo}
            {math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineTablet assign=totModuloTablet}
            {math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineMobile assign=totModuloMobile}
            {if $totModulo == 0}{assign var='totModulo' value=$nbItemsPerLine}{/if}
            {if $totModuloTablet == 0}{assign var='totModuloTablet' value=$nbItemsPerLineTablet}{/if}
            {if $totModuloMobile == 0}{assign var='totModuloMobile' value=$nbItemsPerLineMobile}{/if}


            <li class="ajax_block_product{if $page_name == 'index' || $page_name == 'product'} col-xs-12 col-sm-4 col-md-3{else} col-xs-12 col-sm-6 col-md-4{/if}{if $smarty.foreach.products.iteration%$nbItemsPerLine == 0} last-in-line{elseif $smarty.foreach.products.iteration%$nbItemsPerLine == 1} first-in-line{/if}{if $smarty.foreach.products.iteration > ($smarty.foreach.products.total - $totModulo)} last-line{/if}{if $smarty.foreach.products.iteration%$nbItemsPerLineTablet == 0} last-item-of-tablet-line{elseif $smarty.foreach.products.iteration%$nbItemsPerLineTablet == 1} first-item-of-tablet-line{/if}{if $smarty.foreach.products.iteration%$nbItemsPerLineMobile == 0} last-item-of-mobile-line{elseif $smarty.foreach.products.iteration%$nbItemsPerLineMobile == 1} first-item-of-mobile-line{/if}{if $smarty.foreach.products.iteration > ($smarty.foreach.products.total - $totModuloMobile)} last-mobile-line{/if}">

                <div class="product-container" itemscope itemtype="http://schema.org/Product">
                    <div class="product-image-container" itemprop="image">
                        <a class="product_img_link" href="{$product.link|escape:'html':'UTF-8'}" title="{$product.name|escape:'html':'UTF-8'}" itemprop="url">
                            <img class="replace-2x img-responsive" src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html':'UTF-8'}" alt="{if !empty($product.legend)}{$product.legend|escape:'html':'UTF-8'}{else}{$product.name|escape:'html':'UTF-8'}{/if}" title="{if !empty($product.legend)}{$product.legend|escape:'html':'UTF-8'}{else}{$product.name|escape:'html':'UTF-8'}{/if}" width="{$homeSize.width}" height="{$homeSize.height}" itemprop="image" />
                        </a>

                        {if (!$PS_CATALOG_MODE AND ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))}
                            {if isset($product.new) && $product.new == 1}
                                <a class="new-box" href="{$product.link|escape:'html':'UTF-8'}">
                                    <span class="new-label">{l s='New'}</span>
                                </a>
                            {/if}
                            {if isset($product.on_sale) && $product.on_sale && isset($product.show_price) && $product.show_price && !$PS_CATALOG_MODE}
                                <a class="sale-box" href="{$product.link|escape:'html':'UTF-8'}">
                                    <span class="sale-label">{l s='Sale!'}</span>
                                </a>
                            {/if}
                        {/if}
                    </div>

                    <div class="product-description-container">
                        <h3 itemprop="name">
                            <a href="{$product.link|escape:'html':'UTF-8'}" title="{$product.name|escape:'html':'UTF-8'}" itemprop="url" >
                                {$product.name|truncate:60:'...'|escape:'html':'UTF-8'}<br/>{if isset($product.manufacturer_name)}<span class="brand">{l s='by'}{l s=' '}{$product.manufacturer_name|escape:'htmlall':'UTF-8'}</span>{/if}
                            </a>
                        </h3>

                        <div class="pt-2">
                            {hook h='displayProductListReviews' product=$product}
                        </div>
{*
                        <p class="product-desc" itemprop="description">
                            <a href="{$product.link|escape:'html':'UTF-8'}" title="{$product.name|escape:'html':'UTF-8'}" itemprop="url" >
                                {$product.description_short|strip_tags:'UTF-8'|truncate:90:'...'}
                            </a>
                        </p>
*}
                    </div>

                    {if (!$PS_CATALOG_MODE AND ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))}
                        <div itemprop="offers" itemscope itemtype="http://schema.org/Offer" class="content_price">
                            {if isset($product.show_price) && $product.show_price && !isset($restricted_country_mode)}
                                <a href="{$product.link|escape:'html':'UTF-8'}" title="{$product.name|escape:'html':'UTF-8'}" itemprop="url" >
                                    {if ($product.unit_price_ratio != 0)} 
                                        {$unit_price = round($product.price_without_reduction / $product.unit_price_ratio,6)}
                                    {else}
                                         {if (isset($product.unit_price))} {$unit_price =  $product.unit_price}
                                           {else}{$unit_price = 0}
                                         {/if}
                                    {/if} 
                                    {if ($unit_price > 0) && ($unit_price > $product.price_without_reduction)} 
                                     {$displayPrice = $product.price_without_reduction}
                                     {else}
                                       {$displayPrice = $unit_price}
                                     {/if}             
                                     {if ($unit_price == 0)} 
                                        {$displayPrice = $product.price_without_reduction}
                                    {/if}

                                    <span itemprop="price" class="price product-price">
                                        {convertPrice price=$displayPrice}
                                    </span>
                                    <meta itemprop="priceCurrency" content="{$currency->iso_code}" />

                                    {if isset($product.specific_prices) && $product.specific_prices && isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0}
                                        {if $product.specific_prices.reduction_type == 'percentage'}
                                            <span class="price-percent-reduction">-{$product.specific_prices.reduction * 100}%</span>
                                        {/if}
                                    {/if}

                                    {hook h="displayProductPriceBlock" product=$product type="price"}
                                    {hook h="displayProductPriceBlock" product=$product type="unit_price"}
                                </a>
                            {/if}
                        </div>
                    {/if}

                    <div class="button-container">
                        {if ($product.id_product_attribute == 0 || (isset($add_prod_display) && ($add_prod_display == 1))) && $product.available_for_order && !isset($restricted_country_mode) && $product.minimal_quantity <= 1 && $product.customizable != 2 && !$PS_CATALOG_MODE}
                            {if (!isset($product.customization_required) || !$product.customization_required) && ($product.allow_oosp || $product.quantity > 0)}
                                {if isset($static_token)}
                                    <a class="ajax_add_to_cart_button" href="{$link->getPageLink('cart',true, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;token={$static_token}", false)|escape:'html':'UTF-8'}" title="{l s='Buy'}" data-id-product="{$product.id_product|intval}">
                                        <span><i class="icon-shopping-cart"></i></span>
                                    </a>
                                {else}
                                    <a class="ajax_add_to_cart_button" href="{$link->getPageLink('cart',true, NULL, 'add=1&amp;id_product={$product.id_product|intval}', false)|escape:'html':'UTF-8'}" title="{l s='Buy'}" data-id-product="{$product.id_product|intval}">
                                        <span><i class="icon-shopping-cart"></i></span>
                                    </a>
                                {/if}
                            {else}
                                <span class="ajax_add_to_cart_button disabled">
                                    <span><i class="icon-shopping-cart"></i></span>
                                </span>
                            {/if}
                        {/if}

                        <a itemprop="url" href="{$product.link|escape:'html':'UTF-8'}" title="{l s='View product details'}">
                            <span><i class="icon-list"></i></span>
                        </a>
                        <div class="c"></div>
                    </div>

                    {if $page_name != 'index'}
                        <div class="functional-buttons clearfix">
                            {hook h='displayProductListFunctionalButtons' product=$product}

                            {if isset($comparator_max_item) && $comparator_max_item}
                                <div class="compare">
                                    <a class="add_to_compare" href="{$product.link|escape:'html':'UTF-8'}" data-id-product="{$product.id_product}">{l s='Add to Compare'}</a>
                                </div>
                            {/if}
                        </div>
                    {/if}
                </div>
            </li>
        {/foreach}
    </ul>

    {addJsDefL name=min_item}{l s='Please select at least one product' js=1}{/addJsDefL}
    {addJsDefL name=max_item}{l s='You cannot add more than %d product(s) to the product comparison' sprintf=$comparator_max_item js=1}{/addJsDefL}
    {addJsDef comparator_max_item=$comparator_max_item}
    {addJsDef comparedProductsIds=$compared_products}
{/if}
