{if $order_info.payment_method.payment_id == ""|fn_cardlink_get_payment_id && $order_info.payment_info.transaction_id}

    {assign var="cardlink_status" value=$cardlink_status_full.status|default:$cardlink_status}
    {assign var="captured_amount" value=$cardlink_status_full.total_captured|default:0}
    {assign var="remaining_amount" value=$cardlink_status_full.remaining_to_capture|default:0}
    {assign var="capture_transactions" value=$cardlink_status_full.capture_transactions}
    {assign var="has_multiple_captures" value=$cardlink_status_full.has_multiple_captures|default:false}
    {assign var="order_level_action" value=$cardlink_status_full.order_level_action|default:""}
    {assign var="order_level_tx_id" value=$cardlink_status_full.order_level_tx_id|default:""}
    {assign var="order_level_amount" value=$cardlink_status_full.order_level_amount|default:0}
    {assign var="capture_base_amount" value=$remaining_amount}
    {if !$capture_base_amount || $capture_base_amount <= 0}
        {assign var="capture_base_amount" value=$order_info.total}
    {/if}

    <li class="divider"></li>

    {if $cardlink_status == "AUTHORIZED"}
        <li>
            <a class="cm-dialog-opener cm-dialog-auto-size" data-ca-target-id="content_cardlink_capture_full_{$order_info.order_id}" href="#">
                <i class="icon-ok"></i> {__("cardlink.capture_full_amount")}
            </a>
        </li>

        <li>
            <a class="cm-dialog-opener cm-dialog-auto-size" data-ca-target-id="content_cardlink_capture_partial_{$order_info.order_id}" href="#">
                <i class="icon-pencil"></i> {__("cardlink.capture_partial_amount")}
            </a>
        </li>

        <li>
            <a class="cm-confirm" href="{"orders.cardlink_direct?order_id=`$order_info.order_id`&action=void&amount=`$order_info.total`&tx_id=`$order_info.payment_info.transaction_id`"|fn_url}">
                <i class="icon-remove"></i> {__("cardlink.void_transaction")}
            </a>
        </li>
    {/if}

    {if ($cardlink_status == "AUTHORIZED" || $cardlink_status == "CAPTURED") && $remaining_amount > 0 && $captured_amount > 0}
        <li>
            <a class="cm-dialog-opener cm-dialog-auto-size" data-ca-target-id="content_cardlink_capture_partial_{$order_info.order_id}" href="#">
                <i class="icon-ok"></i> {__("cardlink.capture_remaining")} ({include file="common/price.tpl" value=$remaining_amount span_id="cardlink_capture_remaining_`$order_info.order_id`"})
            </a>
        </li>
    {/if}

    {if $cardlink_status == "CAPTURED" && $captured_amount > 0 && !$has_multiple_captures && $order_level_action}
        <li>
            <a class="cm-dialog-opener cm-dialog-auto-size" data-ca-target-id="content_cardlink_{$order_level_action}_{$order_info.order_id}" href="#">
                <i class="icon-{if $order_level_action == "refund"}undo{else}remove{/if}"></i>
                {if $order_level_action == "refund"}
                    {__("cardlink.refund_amount")} ({include file="common/price.tpl" value=$order_level_amount span_id="cardlink_order_refund_`$order_info.order_id`"})
                {else}
                    {__("cardlink.void_transaction")} ({include file="common/price.tpl" value=$order_level_amount span_id="cardlink_order_void_`$order_info.order_id`"})
                {/if}
            </a>
        </li>
    {/if}

    {if $cardlink_status == "CAPTURED" && $captured_amount > 0 && $has_multiple_captures}
        <li class="disabled">
            <a><i class="icon-list"></i> {__("cardlink.use_transaction_history_actions")}</a>
        </li>
    {/if}

    {if $cardlink_status == "REVERSED" || $cardlink_status == "REFUNDED"}
        <li class="disabled">
            <a><i class="icon-lock"></i> {__("cardlink.transaction_closed")} ({$cardlink_status})</a>
        </li>
    {/if}

    <div id="content_cardlink_capture_full_{$order_info.order_id}" class="hidden" title="{__("cardlink.capture_full_amount")}">
        <form action="{""|fn_url}" method="post" class="form-horizontal">
            <input type="hidden" name="order_id" value="{$order_info.order_id}" />
            <input type="hidden" name="action" value="capture" />

            <div class="control-group">
                <label class="control-label">{__("amount")}:</label>
                <div class="controls">
                    <input type="text" name="amount" value="{$capture_base_amount}" class="input-medium" readonly="readonly" />
                    <p class="muted">{__("cardlink.full_capture_only")}: {$capture_base_amount}</p>
                </div>
            </div>

            <div class="buttons-container">
                {include file="buttons/button.tpl" but_text=__("cardlink.execute_capture") but_name="dispatch[orders.cardlink_direct]" but_role="submit-link" but_meta="btn-primary"}
            </div>
        </form>
    </div>

    <div id="content_cardlink_capture_partial_{$order_info.order_id}" class="hidden" title="{__("cardlink.capture_partial_amount")}">
        <form action="{""|fn_url}" method="post" class="form-horizontal">
            <input type="hidden" name="order_id" value="{$order_info.order_id}" />
            <input type="hidden" name="action" value="capture" />

            <div class="control-group">
                <label class="control-label">{__("amount")}:</label>
                <div class="controls">
                    <input type="text" name="amount" value="{$capture_base_amount}" class="input-medium" />
                    <p class="muted">{__("cardlink.max_row_action_amount")}: {$capture_base_amount}</p>
                </div>
            </div>

            <div class="buttons-container">
                {include file="buttons/button.tpl" but_text=__("cardlink.execute_capture") but_name="dispatch[orders.cardlink_direct]" but_role="submit-link" but_meta="btn-primary"}
            </div>
        </form>
    </div>

    {if $order_level_action}
        <div id="content_cardlink_{$order_level_action}_{$order_info.order_id}" class="hidden" title="{if $order_level_action == "refund"}{__("cardlink.refund_amount")}{else}{__("cardlink.void_transaction")}{/if}">
            <form action="{""|fn_url}" method="post" class="form-horizontal">
                <input type="hidden" name="order_id" value="{$order_info.order_id}" />
                <input type="hidden" name="action" value="{$order_level_action}" />
                <input type="hidden" name="tx_id" value="{$order_level_tx_id}" />

                <div class="control-group">
                    <label class="control-label">{__("amount")}:</label>
                    <div class="controls">
                        {if $order_level_action == "refund"}
                            <input type="text" name="amount" value="{$order_level_amount}" class="input-medium" />
                            <p class="muted">{__("cardlink.max_row_action_amount")}: {$order_level_amount}</p>
                        {else}
                            <input type="text" name="amount" value="{$order_level_amount}" class="input-medium" readonly="readonly" />
                            <p class="muted">{__("cardlink.full_void_only")}: {$order_level_amount}</p>
                        {/if}
                    </div>
                </div>

                <div class="buttons-container">
                    {if $order_level_action == "refund"}
                        {include file="buttons/button.tpl" but_text=__("cardlink.execute_refund") but_name="dispatch[orders.cardlink_direct]" but_role="submit-link" but_meta="btn-primary cm-confirm"}
                    {else}
                        {include file="buttons/button.tpl" but_text=__("cardlink.void_transaction") but_name="dispatch[orders.cardlink_direct]" but_role="submit-link" but_meta="btn-primary cm-confirm"}
                    {/if}
                </div>
            </form>
        </div>
    {/if}

{/if}
