{if ""|fn_cardlink_override_exists}
{if $cart.payment_id}
    {foreach $payment_methods as $payment}
        <div class="litecheckout__shipping-method litecheckout__field litecheckout__field--xsmall">

            <input type="radio"
                   name="selected_payment_method"
                   id="radio_{$payment.payment_id}"
                   data-ca-target-form="litecheckout_payments_form"
                   data-ca-url="checkout.checkout"
                   data-ca-result-ids="litecheckout_final_section,litecheckout_step_payment,shipping_rates_list,litecheckout_terms,checkout*"
                   class="litecheckout__shipping-method__radio cm-select-payment hidden"
                   value="{$payment.payment_id}"
                   {if $payment.payment_id == $cart.payment_id}checked{/if}
            />

            <label id="payments_{$payment.payment_id}"
                   class="litecheckout__shipping-method__wrapper js-litecheckout-toggle"
                   for="radio_{$payment.payment_id}"
                   data-ca-toggling="payments_form_wrapper_{$payment.payment_id}"
                   data-ca-hide-all-in=".litecheckout__payment-methods"
            >
                {if $payment.image}
                    <div class="litecheckout__shipping-method__logo">
                        {include file="common/image.tpl" obj_id=$payment.payment_id images=$payment.image class="litecheckout__shipping-method__logo-image"}
                    </div>
                {/if}
                <p class="litecheckout__shipping-method__title">{$payment.payment}</p>
                <p class="litecheckout__shipping-method__delivery-time">{$payment.description}</p>
            </label>

        </div>
    {/foreach}
{else}
    <div class="litecheckout__item">
        <p class="ty-error-text">
            {__("pd.no_payments_available")}
        </p>
    </div>
{/if}
{$cardlink_payment_id = ""|fn_cardlink_get_payment_id}

{if $cart.payment_id==$cardlink_payment_id}
    {assign var="payment_info" value=$cart.payment_id|fn_get_payment_method_data}
    {assign var="installments" value=$payment_info.processor_params.installments}
    {assign var="cards" value=""|fn_cardlink_get_cards}



    {if $payment_info.processor_params.tokenization=='yes' && $payment_info.processor_params.iframe_mode!='Y' && $smarty.session.auth.user_id}
        <div class="litecheckout__group cardlink-tokenization">
            {if $cards|count}
                <div class="litecheckout__field cardlink-cards">
                    <b>{__("cardlink.select_your_card")}</b>
                    <table class="table">

                        {foreach from=$cards key="k" item="set"}
                            <div class="payment-cards__field"><label for="card-{$set.card_id}" class="checkbox">
                                    <input onclick="jQuery('#cardlink-store-card').hide()" type="radio"
                                           id="card-{$set.card_id}" name="cardlink_payment_gateway-card"
                                           class="checkbox" value="{$set.token}">
                                    <span>
                                {if $set.card_type=='mastercard'}
                                    <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAZCAYAAABQDyyRAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyVpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTQ4IDc5LjE2NDAzNiwgMjAxOS8wOC8xMy0wMTowNjo1NyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIDIxLjAgKE1hY2ludG9zaCkiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NDc5NjM5Rjk4NzRBMTFFQ0JGRUNDQUYyMTFBNUMyOTUiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NDc5NjM5RkE4NzRBMTFFQ0JGRUNDQUYyMTFBNUMyOTUiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo0Nzk2MzlGNzg3NEExMUVDQkZFQ0NBRjIxMUE1QzI5NSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo0Nzk2MzlGODg3NEExMUVDQkZFQ0NBRjIxMUE1QzI5NSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/Ppsp+uMAAAS1SURBVHjaxJZZbFRVGMf/58yduTMjnS6Uhq0KWLCVTUKFwgs8iLiEgAWRGtFEo3FPjAkhIfqAMS5RRJ+MCvhgWGTXgAYMqIkmCkqRLSgGBRUwLe0snemdO3OO/zNzWcR25qoxfskvM3Pn3POt5zufQB8yEHLgQkTvnoNIaxOs8ZWQNRpQnVC/H0B232Zk1m9DZnMWOmvWhwIi2Dol0npXS6SteWRoyqAKWScFZHdadx351T20dX9my9ov02s7kqrjSl3iygeLcdX9S1CxbDSsUYq/XWgo778Alwf5SWOwj4YsQ2KJHA/10oLYyy0NoalmN50D8koX1ktJAnzIzx/P5E6+8EHy+VWf9qzq0wBubK1A9ZuPYMAD9AwONEpJmOboMXHYi7WWjVUin85Dl3jFClIVeWdXavWja7ofdvPaLTrlyUpUv/UElSfpbw6lRdOlYEMKkendEOccoRlvVRNmkvq3QCkTHY3m6+1JQwbI4R8e6N1+0YA2RO99EVXLE1SuyypnGqpcRGd2FkJr8iM6HaDWho4wjrr0DtqlEY32pJ/P5k61n3LbA1WQ1atRs5GFVlnO86JIVLSch6g1yfaSyK/CyUMNjfrcAbihPtj83hfpd+UCRNrGwKp3yvrueV+ZhRzWa6rzkpg4dvRCxnkopCi7T45RGDHEGtY2LXqPnIvoQgW/QgOGUnmoj7PEKMgOx5cBnjeYf2PkTjkW1kQX/sUa6KLPYBm9ySx8BLKonwaPGx6cIFkDVcrvW6b+7Xx/wQGyqliEPoKQ57rKqIhJjf9XZBfUeQnhuwZUb6DfnCLE+hbCVxoCXNfVo7rld3C/Df0Ni3Odob5DbJTGQvDri7CAg+wDcisy6/yfAY3sbzbgiL8qZw9StaW74ZU1s+nrzHoakN54GO6JsC/TNXKJENTpcEHhpYoigyJQJgI+DLBCAt//kvtpw1c0IAWdfA6Jpeamkz6j0HOwEsiIi63YXDJqdKwYijL6zQ3JAsCzm+JL42kVL1TUceSO2RCxWQhPMzdhOR+UwwSmAghekynGZVwNVF2EBaLLKpdRiZXbE2+8+lHqlT/dhnvg7LIgBsxAeLpVbGwlermG3R1GMiEcNcvOha6LWdrpv5+ag2HCLiyJFdsSK59eF3/q8i5+UfbC2X0I7uFGBMfWw6ozdREoKBSFYSREol6i9qB37+LunkWrT7tvN1QHRjcMtkbJsCx6acYhg0V4NM1QcvSUe+yxNV2Pv/ZxakXJiag4bIjwbIRvuwXh25sQnFAcyTTnKXWuHe7+Hchs/wzO3svfmdlkz5g7OTJv8ohgc11MDpZCyK60Os+R7PDO9t4dhnRWZ/5Rt6JBNmvEd7uwLREMB4X9X3XPq8nUf7nHGDK/cCS9BzeTOaTHq4ujpJ0s8tK0k9xBPicjyWyyhEwjdWQLua9QRsC1pJrs9vY0kTPj11zyCRnrcfzCcGKkkewqRLu4WQt5iLzuHQjjNc8ZhpEDZCPhYIBWUkFuInFyltSS5WQeeZ+cJJPJOe+d4gjqHbQLBqS8Bae9Tcz8/g1Z6lnb6TGRnCG3knryg9eKzNokOWF6InmGHCEPmvr0jOvynjWRJ73+iT8EGAAsmaEpgnJMXgAAAABJRU5ErkJggg=="
                                         alt="mastercard">

{elseif $set.card_type=='visa'}

                                    <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACUAAAAZCAIAAAA5RHCCAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyVpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTQ4IDc5LjE2NDAzNiwgMjAxOS8wOC8xMy0wMTowNjo1NyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIDIxLjAgKE1hY2ludG9zaCkiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NDc5NjM5RkQ4NzRBMTFFQ0JGRUNDQUYyMTFBNUMyOTUiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NDc5NjM5RkU4NzRBMTFFQ0JGRUNDQUYyMTFBNUMyOTUiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo0Nzk2MzlGQjg3NEExMUVDQkZFQ0NBRjIxMUE1QzI5NSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo0Nzk2MzlGQzg3NEExMUVDQkZFQ0NBRjIxMUE1QzI5NSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/Pm+q6NsAAARwSURBVHjavFZtbFNVGD7n3tP2tuvnuo/ue2xubJFuAzeUaRigEcQJiZMw8YcxEk3QRQ3GEJmKLmjYnEFNwAR+oBEkMHAxA2Nc9sOMjYyMDYe4SsYYzea2dm3Xr3t7v46nLZN9tAgJ9aQ5bd+c9zznec7zvvfCvd/3jE0HVCoaQogxJrOMMTU3RyOxZwAxWDwDgDEAC4JzKaIgAopCE25212ZrhkkTWZmoQbZWKugPf7iEdIyCF6UtLb+wIYIPE4UXEg+9vpawQhQFWUG6NumVOOEB4MXTiBM8QZ58o8hNABVNBRH94PlFLjM8RJm4AUKAogYJsjzgEqgnYAVBkgg8EkTJrGUan18ZEqTwARJ0f6K0Iid58MYMEiScZlA3ba8CiR9B/hoi2PLCSnB4WLvTzyhpRoUK0vUkIojy9XEPUbsk23j9by+RpDTbRFPw6i3XyKQ3Vc88UpiqUtDR9ElP0DXLkcWFFr0SUfM1g5BCSzUkgZM9I5+39ZcWp//eXEf23f/jwMdHuxu2V+1+zlr2zqlci/6P1hcajl08fP4qMR5JKX4o9dfGZ3NTtMGQuO6Tc7axGahE3ftqaYzLC1IZJZqTFVN4SZmnGNRv1Vphksod4CkAxhy+lvZBbbr+o22rfh60867A1sq8gVHn4TOXS/LNF1q3Nb1SvXllrsWoJrnfdP5pG3UW5JhwpACsy+7wjlAh/gQxPGJQK80Gxh8SeFFuPN0fdPg+e2OdWce09d0ESprgufwhIGOfPyTKcmPdqmgWCX56dsBalEbgD9imnV5Oo0KLyoPCsUo0iVGk6Rjy41TvyPEuW0FJxtvPWB2zbI9typRprMg3r1+RtfXJknG7q2Zv+3sn+qIOOHh+aMbu3rOlvDTTACRpcpZdvC8EFIjFj1xhhlHDcuK+tsukFe2vryT26RwaZx2+9aUWnVrJKOj2d5/++s0NRr265XhfR/8tTyB08NyQLtNQlmOacgdJa55aikfqL553c8xakeVv2PknKvPqqwtJpGPATi68bvWyCXfg2y7bi2uLXt2w/ETvSO/ErJ8TWjqGfASGUZTtORtmq1FMe7ml/OLipRsY4OOAXt1cHy5Nl4870zcKdMym8uwDP11pPtr9/slLSIlED7vm0fwsk+alLzo1Rs13u2qSVIjI3nSke3Taex/8dlQXZmsZS6p2zfJ08lfCuHXHamLdZB2zu7asPDe5568pPycS9XY+VXrT4fvq5ceK88wbK3LI4qrCtAwdY0nRLuUHXjvURWoW/y+j4chvCEAKyCHgHMakn8L77Pr3PmQJGosArUCQUmDODS7ulLhZAOlEtU7Bhx7/EtIPo9v2p5XhT8LwoKyKbI7v+IWYGMaX6h7fbeLITB4JIEoFgTAMBrIAMem8MgS3Uf9Njp4AzkOF8+DjxReeAF+YyqoIkXdAjCSJpxkzqDmGZPGuNsB3I/BfBqviFZrkXLFnGBHHfHD6ik5viLxvRlSNPcPY8Thnm7+APBIoKMrCsN3h+UeAAQCCHXNCX/YJfwAAAABJRU5ErkJggg=="
                                         alt="visa">
                                {/if}
                                ************{$set.last_four} {$set.expiry_month}/{$set.expiry_year}
                            </span>
                                    <a href="#" title="{__("cardlink.remove_card")}" class="cardlink-remove-card"
                                       data-id="{$set.card_id}" aria-label="{__("cardlink.remove_card")}">&times;</a>
                                </label>
                            </div>
                        {/foreach}
                        <div class="payment-cards__field"><label for="card-new" class="checkbox">
                                <input type="radio" id="card-new" onclick="jQuery('#cardlink-store-card').show()"
                                       name="cardlink_payment_gateway-card" class="checkbox" value="0" checked>
                                <span>{__("cardlink.add_credit_card")}</span>
                            </label>
                        </div>
                    </table>
                </div>
            {/if}
            <div class="litecheckout__field" id="cardlink-store-card">
                <label><input type="checkbox" name="store_card" value="1"> {__("cardlink.store_my_card")}</label>
            </div>
        </div>
    {/if}
    {if $installments|count}
        {foreach from=$installments key="k" item="set"}

            {if $set.amount && $cart.subtotal > $set.amount}
                {assign var="has_installments" value=true}
            {/if}
        {/foreach}



        {if $has_installments}
            <div class="litecheckout__group cardlink-installments">
                <div class="litecheckout__field">
                    <b>{__("cardlink.select_installments")}</b>
                    <select name="installments" id="cardlink-installments-select">
                        <option value="0">{__("cardlink.no_installments")}</option>
                        {foreach from=$installments key="k" item="set"}
                            {if $cart.subtotal > $set.amount}
                                <option value="{$set.doseis}"
                                        {if $cart.installments==$set.doseis}selected{/if}>{$set.doseis} {__("cardlink.installments")}</option>
                            {/if}
                        {/foreach}
                    </select>
                </div>
            </div>
        {/if}
    {/if}
    <style>
        .cardlink-tokenization {
            padding: 10px;
            background: #fafafa;
            border: 1px solid #eee;
        }

        .cardlink-cards {
            padding: 5px;
        }

        .cardlink-cards b {
            display: block;
            margin: 0 0 15px;
        }

        .payment-cards__field span img {
            display: inline-block;
            margin-right: 10px;
        }

        .payment-cards__field {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            margin: 10px 0;
        }

        .cardlink-installments {
            padding: 10px;
            background: #fafafa;
            border: 1px solid #eee;
            margin-top: 10px;
        }

        .cardlink-installments b {
            display: block;
            margin-bottom: 15px;
        }

        /*.ty-payment-method-iframe__box
        {*/
            /*    position: fixed;*/
            /*    left: 0;*/
            /*    top: 0;*/
            /*    background: rgba(0,0,0,.5);*/
            /*    width: 100%;*/
            /*    height: 100%;*/
            /*} */
    </style>
{/if}

{/if}