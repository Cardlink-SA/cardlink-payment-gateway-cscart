{* $Id$ *}
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


    {* Προσθήκη μετά τις δόσεις ή την tokenization επιλογή *}

{*    {$payment_info.processor_params|fn_print_die}*}

    {if $payment_info.processor_params.wallets_enabled == "yes"}
        <div class="cardlink-wallets-container" style="margin-top: 20px; text-align: center;">
            <p><b>{__("cardlink.pay_with_wallet")}</b></p>
            <div id="gpcontainer"></div>
            <div id="applePayDiv"></div>

        </div>
        <script>
            (function () {
                var applePayUrl = "{$payment_info.processor_params|fn_cardlink_get_apple_pay_js_url nofilter}"; // Από τη συνάρτηση PHP
                console.log("Apple script URL:",applePayUrl);
                var script = document.createElement('script');
                script.src = applePayUrl;
                script.onload = function () {
                    if (typeof initApplePay === "function") {
                        // Step 5: Initialization
                        initApplePay(
                                "{$order_id}",
                                "{$cart.total}",
                                "EUR",
                                "Order #{$order_id}",
                                "Total Amount",
                                "{$payment_info.processor_params.merchant_id}",
                                "{"cardlink.wallet_sale"|fn_url}" // Το endpoint του CS-Cart
                        );
                    }
                };
                document.head.appendChild(script);
            })();
        </script>

        <script>
            (function() {
                var script = document.createElement('script');
                script.src = "{$payment_info.processor_params|fn_cardlink_get_google_pay_url nofilter}";

                script.onload = async () => {
                    if (typeof GooglePay !== "undefined") {
                        console.log("Initializing Google Pay...");

                        try {
                            googlePay = new GooglePay();

                            let xmlVersion = "2.1"; // Αν χρησιμοποιείς version 2

                            googlePay.initGooglePay(
                                    xmlVersion,
                                    "{"cardlink.wallet_sale"|fn_url}#",
                                    "{$order_id}",
                                    "{$cart.total}",
                                    "EUR",
                                    "{$payment_info.processor_params.merchant_id}",
                                    threeDSHandler
                            );

                            await googlePay.initGooglePayClient();
                            console.log("Google Pay initialized successfully.");

                        } catch (error) {
                            console.error("Error initializing Google Pay:", error);
                        }
                    }
                };
                document.head.appendChild(script);
            })();

            /**
             * Διαχειρίζεται το 3D Secure στάδιο (PROCESSING) για το Google Pay
             */
            async function threeDSHandler(resp) {



                let orderAmount = resp.orderAmount;
                let orderId = resp.orderId;
                let txId = resp.txId;
                let cardEncData = resp.cardEncData;
                let trExtId = resp.trExtId;
                let trMpiCounts = resp.trMpiCounts;

                const xid = await getXid(txId, trExtId, trMpiCounts);

                if (!xid) {
                    console.error("XID generation failed, cannot proceed.");
                    return;
                }

                console.log("MidInput", "{$payment_info.processor_params.merchant_id}");
                console.log("MpiUrlInput", "{$payment_info.processor_params|fn_cardlink_get_mpi_url}");
                const form = await createMPIRequestForm({
                    cardEncData: cardEncData,
                    cardType: '1',
                    deviceCategory: '0', //WWW Browser
                    purchAmount: orderAmount,
                    actionUrl: "{$payment_info.processor_params|fn_cardlink_get_mpi_url}",
                    exponent: '2',
                    orderId: orderId,
                    currency: '978',
                    merchantId: "{$payment_info.processor_params.merchant_id}", //documentation sys1331720664552& from vpos 00035210772
                    okUrl: "{"cardlink.finalize_gpay"|fn_url}",
                    failUrl: "{"cardlink.gpay_error_finalize"|fn_url}",
                    xid: xid,
                });
                form.submit();
            }



            async function createMPIRequestForm(params) {
                console.log("test createMPIRequestForm");

                const form = document.createElement('form');
                form.action = params.actionUrl;
                form.method = 'POST';
                form.name = 'VEReqForm';
                form.id = 'VEReqForm1';

                function addField(name, value) {
                    if (value == null || value === '' ||
                            (Array.isArray(value) && value.length === 0)) return;
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = value;
                    form.appendChild(input);
                }

                const description = 'Order ' + params.orderId;

                const signPayload = {
                    mpiVersion: '2.0',
                    pan: '',                         // Optional (probably handled via cardEncData)
                    expiry: '',                      // Optional (probably handled via cardEncData)
                    cardEncData: params.cardEncData, // Encrypted card info
                    devCat: params.deviceCategory,
                    purchAmount: params.purchAmount,
                    exponent: params.exponent,
                    description: description,
                    currMpi: params.currency,
                    merchantID: params.merchantId,
                    xidb64: params.xid,
                    okUrl: params.okUrl,
                    failUrl: params.failUrl,
                    recurFreq: null,
                    recurEnd: null,
                    installments: null
                };

                let signature;
                let formattedAmount;

                try {
                    const res = await fetch('{"cardlink.sign_data"|fn_url}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(signPayload)
                    });

                    if (res.status === 400) {
                        console.error("Missing MPI Private key", res);
                        alert("Missing MPI Private key");
                        return;
                    } else if (!res.ok) {
                        console.error("Failed to sign payload:", res);
                        alert("Unexpected error signing payload");
                        return;
                    } else {
                        console.log("Signed successfully");
                    }

                    const json = await res.json();
                    signature = json.signature;
                    formattedAmount = json.purchaseAmountFormatted;// get formatted amount string

                    if (!signature || signature.startsWith("Error")) {
                        console.error("Invalid signature:", signature);
                        return;
                    }

                    console.log("Signature received:", signature);
                    console.log("Formatted Purchase Amount:", formattedAmount);
                } catch (err) {
                    console.error("Failed to sign MPI form:", err);
                    return;
                }

                addField('version', signPayload.mpiVersion);
                addField('cardEncData', signPayload.cardEncData);
                addField('deviceCategory', signPayload.devCat);
                addField('purchAmount', formattedAmount);
                addField('exponent', signPayload.exponent);
                addField('description', signPayload.description);
                addField('currency', signPayload.currMpi);
                addField('merchantID', signPayload.merchantID);
                addField('xid', signPayload.xidb64);
                addField('okUrl', signPayload.okUrl);
                addField('failUrl', signPayload.failUrl);
                addField('digest', signature);

                [...form.elements].forEach(input => {
                    console.log(`${ input.name}: ${ input.value}`);
                });

                document.body.appendChild(form);
                return form;
            }


            function finalizePayment(mpiData) {
                // Εδώ καλείς το τελικό step του CS-Cart controller σου
                // στέλνοντας το mpiData (που περιέχει το CAVV, ECI, xid κλπ)
                console.log("Finalizing with data:", mpiData);
                // window.location.href = "..."
            }

            /**
             * Υπολογίζει το XID: VPOS + TxId(7) + "-" + trExtId(6) + mpiCounts(2)
             */
            async function getXid(txId, trExtId, mpiCounts) {
                const padLeft = (str, char, len) => (char.repeat(len) + str).slice(-len);

                const part1 = "VPOS";
                const part2 = padLeft(txId.toString(), '0', 7);
                const part3 = "-";
                const part4 = padLeft(trExtId.toString(), '0', 6);
                const part5 = padLeft(mpiCounts.toString(), '0', 2);

                const rawXid = part1 + part2 + part3 + part4 + part5;

                return btoa(rawXid); // Μετατροπή σε Base64
            }


        </script>
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

        .cardlink-wallets-container {
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .apple-pay-button-container {
            display: inline-block;
            width: 100%;
            max-width: 300px;
            height: 45px;
        }

        .google-pay-button-container {
            display: inline-block;
            width: 100%;
            max-width: 300px;
        }

        /*.ty-payment-method-iframe__box

        {*/
                    /*    position: fixed;*/
                    /*    left: 0;*/
                    /*    top: 0;*/
                    /*    background: rgba(0,0,0,.5);*/
                    /*    width: 100%;*/
                    /*    height: 100%;*/
                    /*}  */
    </style>
{/if}