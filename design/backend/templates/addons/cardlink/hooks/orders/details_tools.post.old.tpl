{if $order_info.payment_method.payment_id == ""|fn_cardlink_get_payment_id && $order_info.payment_info.transaction_id}


    <li class="divider"></li>
    {* 1. Περίπτωση AUTHORIZED: Επιτρέπεται Capture (Οριστικοποίηση) ή Void (Ακύρωση) *}
    {if $cardlink_status_full.status == "AUTHORIZED" || ($cardlink_status_full.status == "CAPTURED" && $cardlink_status_full.remaining_to_capture > 0)}
        <li>
            <a class="cm-dialog-opener cm-dialog-auto-size" data-ca-target-id="content_cardlink_capture_{$order_info.order_id}" href="#">
                <i class="icon-ok"></i> {if $cardlink_status_full.total_captured > 0}
                    {__("cardlink.capture_remaining")} ({$cardlink_status_full.remaining_to_capture}€)
                {else}
                    {__("cardlink.capture_amount")}
                {/if}
            </a>
        </li>
    {/if}

    {* 2. Περίπτωση CAPTURED: Επιτρέπεται Refund (Επιστροφή) *}
    {if $cardlink_status == "CAPTURED"}

        {* ΠΕΡΙΠΤΩΣΗ Α: Η εκκαθάριση ΔΕΝ έχει γίνει ακόμα (settlstatus 10) *}
        {if $cardlink_settlement == "READY" || $cardlink_settlement == "10"}
            <li>
            {* Αν έχουν γίνει Captures, το Reversal ΔΕΝ θα δουλέψει *}
            {if $cardlink_status_full.total_captured > 0}
                <li class="disabled">
                    <a href="#" onclick="alert('{__("cardlink.void_not_available")}'); return false;">
                        <i class="icon-ban-circle"></i> Ακύρωση μη διαθέσιμη
                    </a>
                </li>
            {else}
                <a class="cm-confirm" href="{"orders.cardlink_direct?order_id=`$order_info.order_id`&action=void"|fn_url}">
                    <i class="icon-remove"></i> {__("cardlink.void_transaction")}
                </a>
                {/if}
            </li>
            <li class="disabled">
                <a><small style="color:orange;">{__("cardlink.refund_not_available_yet")}</small></a>
            </li>

            {* ΠΕΡΙΠΤΩΣΗ Β: Η εκκαθάριση ΟΛΟΚΛΗΡΩΘΗΚΕ (settlstatus 1) *}
        {else}
            <li>
                <a class="cm-dialog-opener cm-dialog-auto-size" data-ca-target-id="content_cardlink_refund_{$order_info.order_id}" href="#">
                    <i class="icon-undo"></i> {__("cardlink.refund_amount")}
                </a>
            </li>
        {/if}

    {/if}

    {* 3. Περίπτωση REVERSED / REFUNDED *}
    {if $cardlink_status == "REVERSED" || $cardlink_status == "REFUNDED"}
        <li class="disabled"><a><i class="icon-lock"></i> {__("cardlink.transaction_closed")} ({$cardlink_status})</a></li>
    {/if}

    {* Modals (όπως τα είχες) *}
    <div id="content_cardlink_capture_{$order_info.order_id}" class="hidden" title="{__("cardlink.capture_amount")}">
        <form action="{""|fn_url}" method="post" class="form-horizontal">
            <input type="hidden" name="order_id" value="{$order_info.order_id}" />
            <input type="hidden" name="action" value="capture" />
            <div class="control-group">
                <label class="control-label">{__("amount")} ({$cardlink_status_full.remaining_to_capture}):</label>
                <div class="controls">
                    <input type="text" name="amount" value="{$cardlink_status_full.remaining_to_capture}" class="input-medium" />
                </div>
            </div>
            <div class="buttons-container">
                {include file="buttons/button.tpl" but_text=__("cardlink.execute_capture") but_name="dispatch[orders.cardlink_direct]" but_role="submit-link" but_meta="btn-primary"}
            </div>
        </form>
    </div>

    <div id="content_cardlink_refund_{$order_info.order_id}" class="hidden" title="{__("cardlink.refund_amount")}">
        <form action="{""|fn_url}" method="post" class="form-horizontal">
            <input type="hidden" name="order_id" value="{$order_info.order_id}" />
            <input type="hidden" name="action" value="refund" />
            <div class="control-group">
                <label class="control-label">{__("amount")}:</label>
                <div class="controls">
                    <input type="text" name="amount" value="{$order_info.total}" class="input-medium" />
                </div>
            </div>
            <div class="buttons-container">
                {include file="buttons/button.tpl" but_text=__("cardlink.execute_refund") but_name="dispatch[orders.cardlink_direct]" but_role="submit-link" but_meta="btn-primary"}
            </div>
        </form>
    </div>
{/if}