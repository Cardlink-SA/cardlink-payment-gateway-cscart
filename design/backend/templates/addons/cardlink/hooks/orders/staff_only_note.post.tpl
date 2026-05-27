{if $cardlink_status_full.raw}
    {assign var="capture_transactions" value=$cardlink_status_full.capture_transactions}
    <div class="cardlink-history-wrapper" style="margin-top: 20px;">
        <h4 class="subheader">{__("cardlink.transaction_history")}</h4>
        <div class="table-responsive-wrapper">
            <table class="table table-striped table-hover table-condensed">
                <thead>
                <tr>
                    <th>{__("date")}</th>
                    <th>{__("type")}</th>
                    <th>{__("amount")}</th>
                    <th>{__("status")}</th>
                    <th>{__("cardlink.settlement")}</th>
                    <th>{__("cardlink.tx_id")}</th>
                    <th>{__("tools")}</th>
                </tr>
                </thead>
                <tbody>
                {foreach from=$cardlink_status_full.raw item="tx" key="tx_key"}
                    {assign var="tx_action" value=""}
                    {assign var="tx_remaining_amount" value=0}
                    {assign var="tx_modal_id" value=""}
                    {foreach from=$capture_transactions item="capture_tx"}
                        {if $capture_tx.tx_id == $tx.tx_id}
                            {assign var="tx_action" value=$capture_tx.action}
                            {assign var="tx_remaining_amount" value=$capture_tx.remaining_amount}
                            {assign var="tx_modal_id" value="content_cardlink_tx_`$order_info.order_id`_`$capture_tx@iteration`"}
                        {/if}
                    {/foreach}
                    <tr>
                        <td style="white-space: nowrap;">
                            <small>{$tx.tx_date|date_format:"%d/%m/%Y %H:%M"}</small>
                        </td>
                        <td>
                            <strong>{$tx.tx_type}</strong>
                        </td>
                        <td>
                            {include file="common/price.tpl" value=$tx.amount}
                        </td>
                        <td>
                            {if $tx.status == "CAPTURED" || $tx.status == "AUTHORIZED"}
                                <span class="label label-success">{$tx.status}</span>
                            {elseif $tx.status == "ERROR" || $tx.status == "DENIED"}
                                <span class="label label-important" title="{$tx.description}">{$tx.status}</span>
                            {elseif $tx.status == "REVERSED" || $tx.status == "REFUNDED"}
                                <span class="label label-info">{$tx.status}</span>
                            {else}
                                <span class="label">{$tx.status}</span>
                            {/if}
                        </td>
                        <td>
                            {assign var="settl" value=$tx.attributes["SETTLEMENT STATUS"]}
                            {if $settl == "READY"}
                                <span class="label label-warning" style="background-color: #f39c12;">{__("cardlink.settlement_ready")}</span>
                            {elseif $settl == "1" || $settl == "SETTLED" || $tx.is_settled}
                                <span class="label label-success">{__("cardlink.settlement_settled")}</span>
                            {else}
                                <span class="muted">{$settl|default:"-"}</span>
                            {/if}
                        </td>
                        <td>
                            <small class="muted">{$tx.tx_id}</small>
                        </td>
                        <td>
                            {if $tx_action && $tx_remaining_amount > 0}
                                <a class="cm-dialog-opener cm-dialog-auto-size" data-ca-target-id="{$tx_modal_id}" href="#">
                                    {if $tx_action == "refund"}{__("cardlink.refund_amount")}{else}{__("cardlink.void_transaction")}{/if}
                                </a>
                            {else}
                                <span class="muted">-</span>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>

    {foreach from=$capture_transactions item="capture_tx"}
        {if $capture_tx.action && $capture_tx.remaining_amount > 0}
            <div id="content_cardlink_tx_{$order_info.order_id}_{$capture_tx@iteration}" class="hidden" title="{if $capture_tx.action == "refund"}{__("cardlink.refund_amount")}{else}{__("cardlink.void_transaction")}{/if}">
                <form action="{""|fn_url}" method="post" class="form-horizontal">
                    <input type="hidden" name="order_id" value="{$order_info.order_id}" />
                    <input type="hidden" name="action" value="{$capture_tx.action}" />
                    <input type="hidden" name="tx_id" value="{$capture_tx.tx_id}" />

                    <div class="control-group">
                        <label class="control-label">{__("amount")}:</label>
                        <div class="controls">
                            {if $capture_tx.action == "refund"}
                                <input type="text" name="amount" value="{$capture_tx.remaining_amount}" class="input-medium" />
                                <p class="muted">{__("cardlink.max_row_action_amount")}: {$capture_tx.remaining_amount}</p>
                            {else}
                                <input type="text" name="amount" value="{$capture_tx.remaining_amount}" class="input-medium" readonly="readonly" />
                                <p class="muted">{__("cardlink.full_void_only")}: {$capture_tx.remaining_amount}</p>
                            {/if}
                        </div>
                    </div>

                    <div class="buttons-container">
                        {if $capture_tx.action == "refund"}
                            {include file="buttons/button.tpl" but_text=__("cardlink.execute_refund") but_name="dispatch[orders.cardlink_direct]" but_role="submit-link" but_meta="btn-primary cm-confirm"}
                        {else}
                            {include file="buttons/button.tpl" but_text=__("cardlink.void_transaction") but_name="dispatch[orders.cardlink_direct]" but_role="submit-link" but_meta="btn-primary cm-confirm"}
                        {/if}
                    </div>
                </form>
            </div>
        {/if}
    {/foreach}
{/if}
