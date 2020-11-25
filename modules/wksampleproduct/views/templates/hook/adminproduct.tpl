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

<div class="panel col-lg-12 right-panel">
    <h3>{l s='Sample Product' mod='wksampleproduct'}</h3>
    <div class="form-group">
        <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
            <div class="text-right">
                <label class="boldtext control-label">{l s='Offer Sample' mod='wksampleproduct'}</label>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-7 col-lg-7">
            <span class="switch prestashop-switch fixed-width-lg">
				<input type="radio" value="1" id="sample_active_on" name="sample_active" {if $sample['active'] == 1} checked="checked"{/if}>
				<label for="sample_active_on">{l s='Yes' mod='wksampleproduct'}</label>
				<input type="radio" value="0" id="sample_active_off" name="sample_active" {if $sample['active'] == 0} checked="checked"{/if}>
				<label for="sample_active_off">{l s='No' mod='wksampleproduct'}</label>
				<a class="slide-button btn"></a>
			</span>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-3">
            {l s='Maximum quantity in cart' mod='wksampleproduct'}
        </label>
        <div class="col-lg-3">
            <input type="number" class="form-control" name="max_cart_qty" value="{$sample['max_cart_qty']|escape:'htmlall':'UTF-8'}"></input>
            <small class="form-text text-muted"><em>{l s='Leave empty if no limitation' mod='wksampleproduct'}</em></small>
        </div>
    </div>
    {if $isVirtual}
        <div id="wk_sp_virtual_file" {if !$shouldUpload}style="display:none;"{/if}>
            <div class="form-group">
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
                    <div class="text-right">
                        <label class="boldtext control-label">{l s='Does this sample have an associated file?' mod='wksampleproduct'}</label>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-7 col-lg-7">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" value="1" id="sample_file_active_on" name="sample_file_active" {if $isExists == 1} checked="checked"{/if}>
                        <label for="sample_file_active_on">{l s='Yes' mod='wksampleproduct'}</label>
                        <input type="radio" value="0" id="sample_file_active_off" name="sample_file_active" {if $isExists == 0} checked="checked"{/if}>
                        <label for="sample_file_active_off">{l s='No' mod='wksampleproduct'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
            <div id="wk_sp_virtual_sample_input" class="form-group">
                <label class="control-label col-lg-3">
                    {l s='Upload sample' mod='wksampleproduct'}
                </label>
                <div class="col-lg-5">
                    <div class="form-group">
                        <div class="col-sm-12">
                            <input id="uploaded_sample_file" type="file" name="uploaded_sample_file" class="hide">
                            <div class="dummyfile input-group">
                                <span class="input-group-addon"><i class="icon-file"></i></span>
                                <input id="uploaded_sample_file-name" type="text" name="filename" readonly="">
                                <span class="input-group-btn">
                                    <button id="uploaded_sample_file-selectbutton" type="button" name="submitAddAttachments" class="btn btn-default">
                                        <i class="icon-folder-open"></i> {l s='Add file' mod='wksampleproduct'}
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <small class="form-text text-muted">
                        <em>
                            {if $isExists > 0 && isset($sampleFileName)}
                                <strong class="text-success">{l s='Uploaded file: ' mod='wksampleproduct'}{$sampleFileName|escape:'htmlall':'UTF-8'}</strong>
                            {else}
                                {l s='Upload file to be ordered as virtual product sample' mod='wksampleproduct'}
                            {/if}
                        </em>
                    </small>
                </div>
                {if $isExists > 0}
                    <div class="col-lg-3">
                        <input type="hidden" name="deleteSampleFile" value="0" id="delete_sample_file_hidden">
                        <button type="submit" name="submitAddproductAndStay" class="btn btn-danger" id="delete_sample_file" data-id="{$idProduct|escape:'htmlall':'UTF-8'}" value="{$idProduct|escape:'htmlall':'UTF-8'}">
                            {l s='Delete' mod='wksampleproduct'}
                        </button>
                    </div>
                {/if}
                {* <div class="col-lg-3">
                    <input type="file" class="form-control" name="uploaded_sample_file"></input>
                    <small class="form-text text-muted"><em>{l s='Upload file to be ordered as virtual product sample' mod='wksampleproduct'}</em></small>
                </div> *}
            </div>
        </div>
    {/if}
    <div class="form-group">
        <label class="control-label col-lg-3">
            {l s='Product Price' mod='wksampleproduct'}
        </label>
        <div class="col-lg-3">
            <p class="form-control-static">{$productPrice|escape:'htmlall':'UTF-8'}</p>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-3">
            {l s='Price Type' mod='wksampleproduct'}
        </label>
        <div class="col-lg-3">
            <select class="form-control" name="wk_sample_price_type" id="wk_sample_price_type">
                <option value="1" {if $sample['price_type'] == 1}selected{/if}>{l s='Product Standard Price' mod='wksampleproduct'}</option>
                <option value="2" {if $sample['price_type'] == 2}selected{/if}>{l s='Deduct fix amount from product price' mod='wksampleproduct'}</option>
                <option value="3" {if $sample['price_type'] == 3}selected{/if}>{l s='A percentage of product price' mod='wksampleproduct'}</option>
                <option value="4" {if $sample['price_type'] == 4}selected{/if}>{l s='Custom Price' mod='wksampleproduct'}</option>
                <option value="5" {if $sample['price_type'] == 5}selected{/if}>{l s='Free Sample' mod='wksampleproduct'}</option>
            </select>
        </div>
    </div>
    <div class="form-group wk_sample_amount">
        <label id="wk_sp_amount_label" class="control-label col-lg-3">
            {l s='Amount' mod='wksampleproduct'}
        </label>
        <label id="wk_sp_percent_label" class="control-label col-lg-3">
            {l s='Percentage' mod='wksampleproduct'}
        </label>
        <div class="col-lg-3">
            <div class="input-group">
                <span class="input-group-addon" id="wk_sample_percent">%</span>
                <span class="input-group-addon" id="wk_sample_sign">{$sign|escape:'htmlall':'UTF-8'}</span>
                <input type="text" id="form_hooks_sample_amount" name="sample_amount" class="form-control" value="{$sample['amount']|escape:'htmlall':'UTF-8'}">
            </div>
        </div>
    </div>
    <div class="form-group wk_sample_custom_price">
        <label class="control-label col-lg-3">
            {l s='Set Price' mod='wksampleproduct'}
        </label>
        <div class="col-lg-3">
            <input type="text" name="wk_sample_price" class="form-control" value="{$sample['price']|escape:'htmlall':'UTF-8'}"></input>
        </div>
    </div>
    <div class="form-group wk_sample_price_tax">
        <label class="control-label col-lg-3">
            {l s='Tax' mod='wksampleproduct'}
        </label>
        <div class="col-lg-3">
            <select class="form-control" name="wk_sample_price_tax" id="wk_sample_price_tax">
                <option value="0" {if $sample['price_tax'] == 0}selected{/if}>{l s='Tax excluded' mod='wksampleproduct'}</option>
                <option value="1" {if $sample['price_tax'] == 1}selected{/if}>{l s='Tax included' mod='wksampleproduct'}</option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-3">
            {l s='Sample Button Label' mod='wksampleproduct'}
        </label>
        <div class="col-lg-3">
            <input type="text" class="form-control" name="sample_btn_label" value="{if $sample['button_label']}{$sample['button_label']|escape:'htmlall':'UTF-8'}{else}{l s='Buy Sample' mod='wksampleproduct'}{/if}"></input>
            <small class="form-text text-muted"><em>{l s='Default label name \'Buy Sample\', applied if empty' mod='wksampleproduct'}</em></small>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-3">
            {l s='Description' mod='wksampleproduct'}
        </label>
        <div class="col-lg-9">
            <textarea name="wk_sample_desc" class="autoload_rte form-control">{$sample['description']|escape:'htmlall':'UTF-8'}</textarea>
        </div>
    </div>
    {*<div class="form-group">
        <label class="control-label col-lg-3">
            {l s='Carrier Availibility' mod='wksampleproduct'}
        </label>
        <div class="col-lg-3">
            <select class="form-control" name="">
                <option value="">{l s='Fixed' mod='wksampleproduct'}</option>
                <option value="">{l s='Precentage' mod='wksampleproduct'}</option>
                <option value="">{l s='Product Actual Price' mod='wksampleproduct'}</option>
            </select>
        </div>
    </div>*}
    <div class="panel-footer wk-panel-footer">
		<a href="{$link->getAdminLink('AdminProducts')|escape:'html':'UTF-8'}" class="btn btn-default"><i class="process-icon-cancel"></i> {l s='Cancel' mod='wksampleproduct'}</a>
        <button type="submit" name="submitAddproduct" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='wksampleproduct'}</button>
        <button type="submit" name="submitAddproductAndStay" class="btn btn-default pull-right"><i 	class="process-icon-save"></i> {l s='Save And Stay' mod='wksampleproduct'}</button>
	</div>
</div>

<script type="text/javascript" src="{$sampleJSUrl|escape:'html':'UTF-8'}">
