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

<div class="panel" style="background: #FFF3D7;">
    <div class="panel-heading">
        <i class="icon-money"></i>
        {l s='Sample Product' mod='wksampleproduct'}
        <span class="badge">{$sampleCount|escape:'htmlall':'UTF-8'}</span>
    </div>
    <div class="">
        <table class="table">
            <thead>
                <tr>
                    <th>{l s='Product' mod='wksampleproduct'}</th>
                    <th>{l s='Quantity' mod='wksampleproduct'}</th>
                    <th>
                        <span class="title_box">{l s='Total Price' mod='wksampleproduct'}</span>
                        <small class="text-muted">({l s='Tax included' mod='wksampleproduct'})</small>
                    </th>
                    <th>{l s='Action' mod='wksampleproduct'}</th>
                </tr>
            </thead>
            <tbody>
                {foreach $sample as $product}
                    <tr>
                        <td>{$product.product_name|escape:'htmlall':'UTF-8'}</td>
                        <td><span class="product_quantity_show badge">{$product.product_quantity|escape:'htmlall':'UTF-8'}</span></td>
                        <td>{$product.sample_price|escape:'htmlall':'UTF-8'}</td>
                        <td>
                            <div class="btn-group">
                                <a href="{$link->getAdminLink('AdminProducts')|escape:'html':'UTF-8'}&amp;id_product={$product.product_id|intval}&amp;key_tab=ModuleWksampleproduct&amp;updateproduct&amp;token={getAdminToken tab='AdminProducts'}" class="btn btn-default" title="View">
                                    <i class="icon-search-plus"></i>
                                    {l s='View' mod='wksampleproduct'}
                                </a>
                            </div>
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</div>
