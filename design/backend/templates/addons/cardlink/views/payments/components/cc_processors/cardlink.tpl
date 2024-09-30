<div class="control-group">
    <label class="control-label" for="ab_acquirer">{__("cardlink.acquirer")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][acquirer]" id="ab_acquirer">
            <option value="0"{if $processor_params.acquirer eq "0"} selected="selected"{/if}>Cardlink Checkout</option>
            <option value="1"{if $processor_params.acquirer eq "1"} selected="selected"{/if}>Nexi Checkout</option>
            <option value="2"{if $processor_params.acquirer eq "2"} selected="selected"{/if}>Worldline Greece Checkout</option>
        </select>
    </div>


</div>

<div class="control-group">
	<label class="control-label" for="ab_merchant_id">{__("merchant_id")}:</label>
	<div class="controls">
		<input type="text" name="payment_data[processor_params][merchant_id]" id="ab_merchant_id" value="{$processor_params.merchant_id}" class="input-text" size="60" />
	</div>
</div>

<div class="control-group">
    <label class="control-label" for="ab_shared_secret">{__("shared_secret")}:</label>
    <div class="controls">
        <input type="password" name="payment_data[processor_params][shared_secret]" id="ab_shared_secret" value="{$processor_params.shared_secret}"   size="60">
    </div>
</div>


<div class="control-group">
    <label class="control-label" for="ab_css_url">{__("cardlink.custom_css")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][css_url]" id="ab_css_url" value="{$processor_params.css_url}">
        <p class="well well-small help-block">{__("cardlink.custom_css_tip")}</p>
    </div>
</div>

<div class="control-group">
	<label class="control-label" for="ab_currency">{__("currency")}:</label>
	<div class="controls">
		<select name="payment_data[processor_params][currency]" id="ab_currency">
			<option value="EUR"{if $processor_params.currency == "EUR"} selected="selected"{/if}>{__("currency_code_eur")}</option>
		</select>
	</div>
</div>

<div class="control-group">
    <label class="control-label" for="ab_transaction_type">{__("cardlink.pre_authorize")}:</label>
    <div class="controls">
        <input type="hidden" name="payment_data[processor_params][transaction_type]" value="no" />
        <input type="checkbox" name="payment_data[processor_params][transaction_type]" value="yes" {if $processor_params.transaction_type eq "yes"} checked {/if} id="ab_transaction_type"/>
        <p class="well well-small help-block">{__("cardlink.pre_authorize_tip")}</p>
    </div>
</div>

<div class="control-group" id="token_wrap">
    <label class="control-label" for="ab_tokenization">{__("cardlink.store_cards")}:</label>
    <div class="controls">
        <input type="hidden" name="payment_data[processor_params][tokenization]" value="no" />
        <input type="checkbox" onchange="if(jQuery(this).is(':checked')){ jQuery('#iframe_wrap').hide(); jQuery('#ab_popup').removeAttr('checked') }else{ jQuery('#iframe_wrap').show() }" name="payment_data[processor_params][tokenization]" value="yes" {if $processor_params.tokenization eq "yes"} checked {/if} id="ab_tokenization"/>
        <p class="well well-small help-block">{__("cardlink.store_cards_tip")}</p>
    </div>
</div>

<div class="control-group" id="iframe_wrap">
    <label class="control-label" for="ab_popup">{__("cardlink.enable_iframe")}:</label>
    <div class="controls">
        <input type="hidden" name="payment_data[processor_params][iframe_mode]" value="N" />
        <input onchange="if(jQuery(this).is(':checked')){ jQuery('#token_wrap').hide(); jQuery('#ab_tokenization').removeAttr('checked') }else{ jQuery('#token_wrap').show() }" type="checkbox" name="payment_data[processor_params][iframe_mode]" value="Y" {if $processor_params.iframe_mode eq "Y"} checked {/if} id="ab_popup"/>
        <p class="well well-small help-block">{__("cardlink.enable_iframe_tip")}</p>
    </div>
</div>

{*<div class="control-group">*}
{*    <label class="control-label" for="ab_language">{__("cardlink.language")}:</label>*}
{*    <div class="controls">*}
{*        <select name="payment_data[processor_params][language]" id="ab_language">*}
{*            <option value="en"{if $processor_params.language eq "en"} selected="selected"{/if}>{__("cardlink.english")}</option>*}
{*            <option value="el"{if $processor_params.language eq "el"} selected="selected"{/if}>{__("cardlink.greek")}</option>*}
{*        </select>*}
{*    </div>*}
{*</div>*}

<div class="control-group">
    <label class="control-label" for="ab_mode">{__("cardlink.test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][mode]" id="ab_mode">
            <option value="test"{if $processor_params.mode eq "test"} selected="selected"{/if}>{__("test")}</option>
            <option value="live"{if $processor_params.mode eq "live"} selected="selected"{/if}>{__("live")}</option>
        </select>
    </div>
</div>



<div class="control-group">
    <label class="control-label" for="ab_installments">{__("cardlink.installments")}:</label>
    <div class="controls">
        <table class="table table-middle">
            <thead>
            <tr>
                <th >{__("cardlink.min_amount")}</th>
                <th >{__("cardlink.number_of_installments")}</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            {if $processor_params.installments|count}
                {foreach from=$processor_params.installments key="k" item="set"}
                    <tr id="box_{$k}" class="table-row">
                        <td>
                            <input class="input-small" type="text" value="{$set.amount}" name="payment_data[processor_params][installments][{$k}][amount]">
                        </td>
                        <td>
                            <input class="input-small" type="number" min="1" max="60" value="{$set.doseis}" name="payment_data[processor_params][installments][{$k}][doseis]">
                        </td>
                        <td>
                            {include file="buttons/multiple_buttons.tpl" only_delete="Y" hide_clone='true' item_id="{$k}"}
                            {assign var="tag_level" value=$tag_level|default:"1"}
                            {include file="buttons/add_empty_item.tpl" but_onclick="Tygh.$('#box_' + this.id).cloneNode($tag_level); `$on_add`" item_id=$k}
                        </td>
                    </tr>
                {/foreach}
            {else}
                <tr id="box_1" class="table-row">
                    <td>
                        <input class="input-small" type="text" value="{$set.amount}" name="payment_data[processor_params][installments][1][amount]">
                    </td>
                    <td>
                        <input class="input-small" type="number"  min="1" max="60" value="{$set.doseis}" name="payment_data[processor_params][installments][1][doseis]">
                    </td>
                    <td>
                        {include file="buttons/multiple_buttons.tpl" only_delete="Y" hide_clone='true' item_id="1"}
                        {assign var="tag_level" value=$tag_level|default:"1"}
                        {include file="buttons/add_empty_item.tpl" but_onclick="Tygh.$('#box_' + this.id).cloneNode($tag_level); `$on_add`" item_id=1}
                    </td>
                </tr>
            {/if}
            </tbody>
        </table>
    </div>
</div>