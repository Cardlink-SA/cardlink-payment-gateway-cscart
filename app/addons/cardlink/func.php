<?php


function fn_cardlink_get_cards() {
    $cards = db_get_array("SELECT * FROM ?:cardlink_cards WHERE user_id=?i", $_SESSION['auth']['user_id']);

    return $cards;
}

function fn_cardlink_install() {

    db_query("CREATE TABLE `?:cardlink_cards` (
  `card_id` int(11) NOT NULL,
  `token` varchar(120) NOT NULL,
  `last_four` varchar(4) NOT NULL,
  `expiry_year` varchar(4) NOT NULL,
  `expiry_month` varchar(2) NOT NULL,
  `card_type` varchar(120) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

    db_query("ALTER TABLE `?:cardlink_cards`
  ADD PRIMARY KEY (`card_id`),
  ADD KEY `user_id` (`user_id`);");

    db_query("ALTER TABLE `?:cardlink_cards`
  MODIFY `card_id` int(11) NOT NULL AUTO_INCREMENT;");


    db_query("CREATE TABLE `?:cardlink_wallet_data` (
  `order_id` int(11) NOT NULL,
  `xid` varchar(128) NOT NULL,
  `preparedTxId` varchar(128) NULL,
  `payMethod` varchar(64) NOT NULL,
  `created_at` int(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;");

    db_query("ALTER TABLE `?:cardlink_wallet_data`
     ADD PRIMARY KEY (`xid`),
    ADD INDEX(`order_id`); ");


    $processor = [
        "processor" => 'Cardlink',
        "processor_script" => 'cardlink.php',
        "processor_template" => 'views/orders/components/payments/cc_outside.tpl',
        "admin_template" => 'cardlink.tpl',
        "callback" => 'N',
        "type" => 'P',
        "addon" => 'cardlink'
    ];

    $pid = db_query("INSERT INTO `?:payment_processors` ?e", $processor);

    //also add the payment methods if not exist
    if (!fn_cardlink_get_payment_id()) {
        $payment_data = array(
            'storefront_ids' => '',
            'payment_id' => '',
            'processor_id' => $pid,
            'payment' => 'Cardlink',
            'company_id' => '1',
            'description' => '',
            'instructions' => '',
            'usergroup_ids' => '0',
            'p_surcharge' => '0.000',
            'a_surcharge' => '0.000',
            'surcharge_title' => '',
            'processor_params' => array(
                'acquirer' => '0',
                'iris_org_id' => '',
                'iris_api_user' => '',
                'iris_api_pass' => '',
                'merchant_id' => '',
                'shared_secret' => '',
                'css_url' => '',
                'currency' => 'EUR',
                'transaction_type' => 'yes',
                'tokenization' => 'yes',
                'iframe_mode' => 'N',
                'mode' => 'live',
            ),
        );

        //hack to upload an image for the payment method
        $_REQUEST['payment_image_image_data'] = array(
            '0' => array(
                'pair_id' => '',
                'type' => 'M',
                'object_id' => 0,
                'image_alt' => ''
            )
        );
        $_REQUEST['file_payment_image_image_icon'] = array(
            '0' => 'https://www.e-growth.gr/media/cardlink.png'
        );
        $_REQUEST['type_payment_image_image_icon'] = array(
            '0' => 'url'
        );
        $_REQUEST['is_high_res_payment_image_image_icon'] = array(
            '0' => 'N'
        );


        fn_update_payment($payment_data, 0);
    }

    $processor = [
        "processor" => 'IRIS by Cardlink',
        "processor_script" => 'cl_iris.php',
        "processor_template" => 'views/orders/components/payments/cc_outside.tpl',
        "admin_template" => '',
        "callback" => 'N',
        "type" => 'P',
        "addon" => 'cardlink'
    ];

    $pid = db_query("INSERT INTO `?:payment_processors` ?e", $processor);

    //also add the payment methods if not exist
    if (!fn_cardlink_get_iris_payment_id()) {
        $payment_data = array(
            'storefront_ids' => '',
            'payment_id' => '',
            'processor_id' => $pid,
            'payment' => 'IRIS Payments by Cardlink',
            'company_id' => '1',
            'description' => '',
            'instructions' => '',
            'usergroup_ids' => '0',
            'p_surcharge' => '0.000',
            'a_surcharge' => '0.000',
            'surcharge_title' => '',
            'processor_params' => array(
                'iris_customer_code' => '',
            ),
        );

        //hack to upload an image for the payment method


        $_REQUEST['payment_image_image_data'] = array(
            '0' => array(
                'pair_id' => '',
                'type' => 'M',
                'object_id' => 0,
                'image_alt' => ''
            )
        );
        $_REQUEST['file_payment_image_image_icon'] = array(
            '0' => 'https://www.e-growth.gr/media/iris.png'
        );
        $_REQUEST['type_payment_image_image_icon'] = array(
            '0' => 'url'
        );
        $_REQUEST['is_high_res_payment_image_image_icon'] = array(
            '0' => 'N'
        );

        fn_update_payment($payment_data, 0);
    }

}

function fn_cardlink_uninstall() {
    db_query("DROP TABLE IF EXISTS ?:cardlink_cards");
    db_query("DROP TABLE IF EXISTS ?:cardlink_wallet_data");

    db_query("DELETE FROM ?:payment_descriptions WHERE payment_id IN (SELECT payment_id FROM ?:payments WHERE processor_id IN (SELECT processor_id FROM ?:payment_processors WHERE processor_script IN ('cardlink.php')))");
    db_query("DELETE FROM ?:payments WHERE processor_id IN (SELECT processor_id FROM ?:payment_processors WHERE processor_script IN ('cardlink.php'))");
    db_query("DELETE FROM ?:payment_processors WHERE processor_script IN ('cardlink.php')");


    db_query("DELETE FROM ?:payment_descriptions WHERE payment_id IN (SELECT payment_id FROM ?:payments WHERE processor_id IN (SELECT processor_id FROM ?:payment_processors WHERE processor_script IN ('cl_iris.php')))");
    db_query("DELETE FROM ?:payments WHERE processor_id IN (SELECT processor_id FROM ?:payment_processors WHERE processor_script IN ('cl_iris.php'))");
    db_query("DELETE FROM ?:payment_processors WHERE processor_script IN ('cl_iris.php')");
}

function fn_cardlink_get_payment_id() {
    return db_get_field("SELECT payment_id FROM ?:payments WHERE processor_id IN (SELECT processor_id FROM ?:payment_processors WHERE processor_script IN ('cardlink.php'))");
}

function fn_cardlink_get_iris_payment_id() {
    return db_get_field("SELECT payment_id FROM ?:payments WHERE processor_id IN (SELECT processor_id FROM ?:payment_processors WHERE processor_script IN ('cl_iris.php'))");
}


function fn_cardlink_iris_rf_code($order_id, $cust_code) {
    $rf_payment_code = '';
    $payment_info = db_get_field("SELECT data FROM ?:order_data WHERE order_id = ?i AND type = 'A'", $order_id);

    if (!empty($payment_info)) {
        $payment_info = unserialize($payment_info);
        $rf_payment_code = $payment_info['rf_payment_code'];
    }
    if ($rf_payment_code !== '') {
        return $rf_payment_code;
    }

    $order_info = fn_get_order_info($order_id);
    $order_total = $order_info['total'];
    //	/ calculate payment check code /
    $paymentSum = 0;
    if ($order_total > 0) {
        $ordertotal = str_replace([','], '.', (string)$order_total);
        $ordertotal = number_format($ordertotal, 2, '', '');
        $ordertotal = strrev($ordertotal);
        $factor = [
            1,
            7,
            3
        ];
        $idx = 0;
        for ($i = 0; $i < strlen($ordertotal); $i++) {
            $idx = $idx <= 2 ? $idx : 0;
            $paymentSum += $ordertotal[$i] * $factor[$idx];
            $idx++;
        }
    }
    $randomNumber = str_pad($order_id, 13, '0', STR_PAD_LEFT);;
    $paymentCode = $paymentSum ? ($paymentSum % 8) : '8';
    $systemCode = '12';
    $tempCode = $cust_code . $paymentCode . $systemCode . $randomNumber . '271500';
    $mod97 = bcmod($tempCode, '97');
    $cd = 98 - (int)$mod97;
    $cd = str_pad((string)$cd, 2, '0', STR_PAD_LEFT);
    $rf_payment_code = 'RF' . $cd . $cust_code . $paymentCode . $systemCode . $randomNumber;


    $payment_data = array(
        'order_id' => $order_id,
        'type' => 'A',
        'data' => serialize(['rf_payment_code' => $rf_payment_code]),
    );
    db_query("INSERT INTO ?:order_data ?e", $payment_data);
    return $rf_payment_code;
}


function fn_cardlink_return_error($error = 'A') {
    switch ($error) {
        case "A":
            fn_set_notification("E", __("cardlink.iris_misconfigured_acquirer"), __("cardlink.iris_misconfigured_acquirer"));
            fn_redirect("checkout.checkout");
            break;
    }


    exit;
}

function fn_cardlink_get_payments_post($params, &$payments) {
    //	$iris_payment_id = fn_cardlink_get_iris_payment_id();
    //	$cardlink_payment_id = fn_cardlink_get_payment_id();

    //	foreach ($payments as $k => $payment) {
    //check if payment is IRIS
    //		if (is_array($payment) && $payment['payment_id'] == $iris_payment_id) {
    //check if cardlink payment exists and if Nexi is selected
    //$data = fn_get_processor_data($cardlink_payment_id);
    //			if (!isset($data['processor_params']) || $data['processor_params']['acquirer'] != '1') {
    //				$payments[$k]['status'] = 'D';
    //				$payments[$k]['payment'] .= "\n(".__("cardlink.iris_misconfigured_acquirer").")";
    //				unset($payments[$k]);
    //				fn_set_notification("E", __("cardlink.iris_misconfigured_acquirer"), __("cardlink.iris_misconfigured_acquirer"));
    //			}
    //		}
    //	}
}


function fn_cardlink_override_exists() {
    $addons = ['payment_dependencies'];
    $exists = false;
    foreach ($addons as $addon) {
        $has = db_get_field("SELECT status FROM ?:addons WHERE addon=?s AND status='A'", $addon);

        if ($has == 'A') {
            $exists = true;
            break;
        }
    }

    return $exists;
}


/*2026*/


function fn_cardlink_get_direct_api_endpoint_url($processor_data): string {

    $endpoint_url = '';


    if ($processor_data['processor_params']['mode'] == 'test') {

        switch ($processor_data['processor_params']['acquirer']) {
            case 0 :
                $endpoint_url = "https://ecommerce-test.cardlink.gr/vpos/xmlpayvpos";
                break;
            case 1 :
                $endpoint_url = "https://alphaecommerce-test.cardlink.gr/vpos/xmlpayvpos";
                break;
            case 2 :
                $endpoint_url = "https://eurocommerce-test.cardlink.gr/vpos/xmlpayvpos";
                break;
        }
    } else {
        switch ($processor_data['processor_params']['acquirer']) {
            case 0 :
                $endpoint_url = "https://ecommerce.cardlink.gr/vpos/xmlpayvpos";
                break;
            case 1 :
                $endpoint_url = "https://www.alphaecommerce.gr/vpos/xmlpayvpos";
                break;
            case 2 :
                $endpoint_url = "https://vpos.eurocommerce.gr/vpos/xmlpayvpos";
                break;
        }
    }

    return $endpoint_url;
}


/**
 * αυτόματη εκτέλεση Capture ή Void κατά την αλλαγή status
 */
function fn_cardlink_change_order_status($status_to, $status_from, $order_info, $force_notification, $order_statuses, $place_order) {

    $payment_id = $order_info['payment_id'];
    $processor_data = fn_get_processor_data($payment_id);

    // Έλεγχος αν ο επεξεργαστής είναι ο Cardlink
    if (empty($processor_data['processor_script']) || $processor_data['processor_script'] != 'cardlink.php') {
        return;
    }

    $txId = !empty($order_info['payment_info']['transaction_id']) ? $order_info['payment_info']['transaction_id'] : '';

    if (empty($txId)) {
        return;
    }

    // Παράδειγμα: Capture όταν η παραγγελία γίνεται 'Completed' (C)
    if ($status_to == 'C' && $status_from == 'O') { // O = Open/Authorized
        fn_cardlink_execute_direct_action_v2($order_info, $processor_data, 'capture');
    }

    // Παράδειγμα: Void όταν η παραγγελία ακυρώνεται (I ή F)
    if (in_array($status_to, [
            'I',
            'F'
        ]) && $status_from == 'O') {
        fn_cardlink_execute_direct_action_v2($order_info, $processor_data, 'void');
    }
}


function fn_cardlink_get_transaction_status($order_info, $processor_data) {
    $params = $processor_data['processor_params'];
    $shared_secret = $params['shared_secret'];
    $mid = $params['merchant_id'];
    $txId = !empty($order_info['payment_info']['transaction_id']) ? $order_info['payment_info']['transaction_id'] : '';


    if (empty($txId)) {
        return false;
    }

    $dt = new \DateTime('now', new \DateTimeZone('Europe/Athens'));
    $timestamp = $dt->format('Y-m-d\TH:i:s.vP');
    $message_id = 'M' . (int)(microtime(true) * 1000);

    // 1. Κατασκευή του StatusRequest Content
    // Μπορούμε να ψάξουμε είτε με txId είτε με OrderId. Εδώ χρησιμοποιούμε το TxId για ακρίβεια.
    $request_content = "<StatusRequest>
        <Authentication>
            <Mid>{$mid}</Mid>
        </Authentication>
        <TransactionInfo>
        	<OrderId>{$order_info['payment_info']['orderid']}</OrderId>
        </TransactionInfo>
    </StatusRequest>";

    // 2. Υπολογισμός Digest (v2.1)
    $xml_ns = 'http://www.modirum.com/schemas/vposxmlapi41';
    $xml_ns_ns2 = 'http://www.w3.org/2000/09/xmldsig#';

    $message_xml = '<Message xmlns="' . $xml_ns . '" xmlns:ns2="' . $xml_ns_ns2 . '" messageId="' . $message_id . '" timeStamp="' . $timestamp . '" version="2.1">';
    $message_xml .= "\n" . $request_content . "\n";
    $message_xml .= '</Message>';

    $canonicalized = trim(preg_replace('/<\?xml[^?]*\?>\s*/i', '', $message_xml));
    $data_to_hash = $canonicalized . $shared_secret;
    $digest = base64_encode(hash('sha256', $data_to_hash, true));

    // 3. Τελικό XML Payload
    $final_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
    $final_xml .= '<VPOS xmlns="' . $xml_ns . '" xmlns:ns2="' . $xml_ns_ns2 . '">' . "\n";
    $final_xml .= '<Message messageId="' . $message_id . '" timeStamp="' . $timestamp . '" version="2.1">' . "\n";
    $final_xml .= $request_content . "\n";
    $final_xml .= '</Message>' . "\n";
    $final_xml .= '<Digest>' . $digest . '</Digest>' . "\n";
    $final_xml .= '</VPOS>';

    // 4. Αποστολή
    $url = fn_cardlink_get_direct_api_endpoint_url($processor_data);
    $response = fn_cardlink_post_to_vpos($url, $final_xml);

    //	fn_print_die($url,$final_xml,$response);

    // 5. Επεξεργασία Αποτελέσματος
    if (empty($response)) {
        return false;
    }

    $full_history = fn_cardlink_parse_full_history($response);


    $effective_status = 'UNKNOWN';
    $effective_settl = '';
    $total_captured = 0.0;
    $total_refunded = 0.0;
    $original_amount = (float) $order_info['total'];

    foreach ($full_history as $tx) {

        $tx_type = strtoupper(trim($tx['tx_type']));
        $tx_status = strtoupper(trim($tx['status']));
        $tx_amount = (float) $tx['amount'];

        if ($tx_status === 'ERROR' || $tx_status === 'DENIED') {
            continue;
        }

        // Original authorization amount
        if ($tx_type === 'AUTHORIZATION') {
            $original_amount = $tx_amount;

            if ($effective_status === 'UNKNOWN') {
                $effective_status = 'AUTHORIZED';
            }

            continue;
        }

        // Count ONLY real active captured transactions
        if ($tx_type === 'CAPTURE' && $tx_status === 'CAPTURED') {
            $total_captured += $tx_amount;
            $effective_status = 'CAPTURED';

            if (!empty($tx['attributes']['SETTLEMENT STATUS'])) {
                $effective_settl = $tx['attributes']['SETTLEMENT STATUS'];
            }

            continue;
        }

        // Count refunds only
        if ($tx_type === 'REFUND' && $tx_status === 'REFUNDED') {
            $total_refunded += $tx_amount;
            continue;
        }

        // Full reversal only if nothing active captured exists
        if (($tx_type === 'VOID' || $tx_type === 'REVERSAL') && $total_captured <= 0) {
            $effective_status = 'REVERSED';
        }
    }

    $refundable_amount = max(0, $total_captured - $total_refunded);
    $remaining_to_capture = max(0, $original_amount - $total_captured);

    if ($total_captured > 0) {
        $effective_status = 'CAPTURED';
    }

    if ($total_captured > 0 && $total_refunded >= $total_captured) {
        $effective_status = 'REFUNDED';
    }

    return [
        'status' => $effective_status,
        'settlstatus' => $effective_settl,
        'total_captured' => $total_captured,
        'captured_amount' => $total_captured,
        'total_refunded' => $total_refunded,
        'refundable_amount' => $refundable_amount,
        'remaining_to_capture' => $remaining_to_capture,
        'raw' => $full_history
    ];
}


function fn_cardlink_parse_full_history($xml_response) {
    if (empty($xml_response)) {
        return [];
    }

    $dom = new DOMDocument();

    @$dom->loadXML($xml_response);
    $details = $dom->getElementsByTagName('TransactionDetails');

    $history = [];

    foreach ($details as $tx) {
        $data = [
            'order_id' => $tx->getElementsByTagName('OrderId')->item(0)->nodeValue,
            'amount' => $tx->getElementsByTagName('OrderAmount')->item(0)->nodeValue,
            'status' => $tx->getElementsByTagName('Status')->item(0)->nodeValue,
            'tx_id' => $tx->getElementsByTagName('TxId')->item(0)->nodeValue,
            'tx_type' => $tx->getElementsByTagName('TxType')->item(0)->nodeValue,
            'tx_date' => $tx->getElementsByTagName('TxDate')->item(0)->nodeValue,
            'description' => $tx->getElementsByTagName('Description')->item(0)->nodeValue,
            'attributes' => []
        ];

        // Εξαγωγή όλων των Attributes (όπως το SETTLEMENT STATUS)
        $attributes = $tx->getElementsByTagName('Attribute');
        foreach ($attributes as $attr) {
            $name = $attr->getAttribute('name');
            $data['attributes'][$name] = $attr->nodeValue;
        }

        $history[] = $data;
    }

    return $history;
}

function fn_cardlink_execute_direct_action($order_info, $processor_data, $action, $extra_params = []) {
    $params = $processor_data['processor_params'];
    $shared_secret = $params['shared_secret'];
    $mid = $params['merchant_id'];

    $txId = !empty($order_info['payment_info']['transaction_id'])
        ? $order_info['payment_info']['transaction_id']
        : '';

    if (empty($txId)) {
        return false;
    }

    $action = strtolower($action);
    $currency = !empty($params['currency']) ? $params['currency'] : 'EUR';

    $history = fn_cardlink_get_transaction_status($order_info, $processor_data);

    $captured_amount = !empty($history['captured_amount'])
        ? (float) $history['captured_amount']
        : (float) $order_info['total'];

    $remaining_to_capture = !empty($history['remaining_to_capture'])
        ? (float) $history['remaining_to_capture']
        : (float) $order_info['total'];

    $settlement_status = null;

    if (isset($history['settlement_status'])) {
        $settlement_status = (int) $history['settlement_status'];
    } elseif (isset($history['settlstatus'])) {
        $settlement_status = (int) $history['settlstatus'];
    }

    if ($action === 'capture') {
        $the_amount = $remaining_to_capture;
    } else {
        $the_amount = $captured_amount;
    }

    $amount = isset($extra_params['amount'])
        ? (float) $extra_params['amount']
        : (float) $the_amount;

    /*
     * Refund routing logic:
     * settlstatus 0 / 10 = same-day or settlement transit => CancelRequest
     * settlstatus >= 20 = settled => RefundRequest
     */
    if ($action === 'refund') {
        /*
         * Same day / unsettled:
         * RefundRequest δεν γίνεται. Πρέπει να πάει ως CancelRequest.
         * Επιτρέπουμε partial amount, αρκεί να μην ξεπερνάει το refundable/captured amount.
         */
        if ($settlement_status === 0 || $settlement_status === 10) {
            if ($amount <= 0 || $amount > $captured_amount) {
                fn_set_notification(
                    'E',
                    __('error'),
                    'Invalid reversal amount.'
                );

                return false;
            }

            $action = 'cancel';
        }
    }

    $reqName = ($action === 'void' || $action === 'cancel')
        ? 'CancelRequest'
        : ucfirst($action) . 'Request';

    $expectedResponse = ($action === 'void' || $action === 'cancel')
        ? 'CancelResponse'
        : ucfirst($action) . 'Response';

    $formatted_amount = number_format($amount, 2, '.', '');

    $dt = new \DateTime('now', new \DateTimeZone('Europe/Athens'));
    $timestamp = $dt->format('Y-m-d\TH:i:s.vP');
    $message_id = 'M' . (int)(microtime(true) * 1000);

    $request_content = "    <{$reqName}>
        <Authentication>
            <Mid>{$mid}</Mid>
        </Authentication>
        <OrderInfo>
            <OrderId>{$order_info['payment_info']['orderid']}</OrderId>
            <OrderAmount>{$formatted_amount}</OrderAmount>
            <Currency>{$currency}</Currency>
        </OrderInfo>
    </{$reqName}>";

    $xml_ns = 'http://www.modirum.com/schemas/vposxmlapi41';
    $xml_ns_ns2 = 'http://www.w3.org/2000/09/xmldsig#';

    $message_xml = '<Message xmlns="' . $xml_ns . '" xmlns:ns2="' . $xml_ns_ns2 . '" messageId="' . $message_id . '" timeStamp="' . $timestamp . '" version="2.1">';
    $message_xml .= "\n" . $request_content . "\n";
    $message_xml .= '</Message>';

    $canonicalized = trim(preg_replace('/<\?xml[^?]*\?>\s*/i', '', $message_xml));
    $data_to_hash = $canonicalized . $shared_secret;
    $digest = base64_encode(hash('sha256', $data_to_hash, true));

    $final_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
    $final_xml .= '<VPOS xmlns="' . $xml_ns . '" xmlns:ns2="' . $xml_ns_ns2 . '">' . "\n";
    $final_xml .= '<Message messageId="' . $message_id . '" timeStamp="' . $timestamp . '" version="2.1">' . "\n";
    $final_xml .= $request_content . "\n";
    $final_xml .= '</Message>' . "\n";
    $final_xml .= '<Digest>' . $digest . '</Digest>' . "\n";
    $final_xml .= '</VPOS>';

    $url = fn_cardlink_get_direct_api_endpoint_url($processor_data);
    $response = fn_cardlink_post_to_vpos($url, $final_xml);

    $status = strtoupper(fn_cardlink_extract_xml_value($response, 'Status'));

    $success_statuses = [
        'CAPTURED',
        'AUTHORIZED',
        'APPROVED',
        'SUCCESS',
        'REFUNDED',
        'CANCELED'
    ];

    if (in_array($status, $success_statuses)) {
        fn_set_notification('N', __('notice'), __("cardlink.action_{$action}_success"));
        return true;
    }

        $error_code = fn_cardlink_extract_xml_value($response, 'ErrorCode');
        $error_desc = fn_cardlink_extract_xml_value($response, 'Description');

    /*
     * Fallback:
     * If RefundRequest fails with O1 and this was full amount,
     * retry as CancelRequest.
     */
    if ($action === 'refund' && $error_code === 'O1') {
        $is_full_captured_amount = abs($amount - $captured_amount) < 0.01;

        if ($is_full_captured_amount) {
            return fn_cardlink_execute_direct_action($order_info, $processor_data, 'cancel', [
                'amount' => $captured_amount
            ]);
        }
    }

    fn_set_notification(
        'E',
        __('error'),
        __("cardlink.action_{$action}_failed") . ': ' . ($error_desc ?: "Error $error_code")
    );

        return false;
    }

function fn_cardlink_build_background_confirmation_url($unused = null) {
    return fn_url('cardlink.confirmation', 'C', 'https');
}

function fn_cardlink_get_xml_node_value_v2(DOMElement $element, $tag_name) {
    $node = $element->getElementsByTagName($tag_name)->item(0);

    return $node ? trim($node->nodeValue) : '';
}

function fn_cardlink_normalize_amount_v2($amount) {
    if ($amount === null || $amount === '') {
        return 0.0;
    }

    return (float) str_replace(',', '.', (string) $amount);
}

function fn_cardlink_is_same_business_day($tx_date, $timezone = 'Europe/Athens') {
    if (empty($tx_date)) {
        return false;
    }

    try {
        $tz = new \DateTimeZone($timezone);
        $transaction_date = new \DateTime($tx_date, $tz);
        $now = new \DateTime('now', $tz);

        return $transaction_date->format('Y-m-d') === $now->format('Y-m-d');
    } catch (\Exception $e) {
        return false;
    }
}

function fn_cardlink_get_settlement_status_value($transaction_details) {
    if (!empty($transaction_details['attributes']['SETTLEMENT STATUS'])) {
        return trim((string) $transaction_details['attributes']['SETTLEMENT STATUS']);
    }

    if (isset($transaction_details['attributes']['SettlStatus'])) {
        return trim((string) $transaction_details['attributes']['SettlStatus']);
    }

    if (isset($transaction_details['settlstatus'])) {
        return trim((string) $transaction_details['settlstatus']);
    }

    return '';
}

function fn_cardlink_get_settlement_status_code($transaction_details) {
    $settlement_status = strtoupper(fn_cardlink_get_settlement_status_value($transaction_details));

    if ($settlement_status === '' || $settlement_status === 'NA') {
        return null;
    }

    if (is_numeric($settlement_status)) {
        return (int) $settlement_status;
    }

    if ($settlement_status === 'READY') {
        return 0;
    }

    if (in_array($settlement_status, ['SETTLED', 'SETTLEMENT COMPLETED'], true)) {
        return 20;
    }

    if (in_array($settlement_status, ['TRANSIT', 'IN TRANSIT', 'SETTLEMENT TRANSIT', 'PENDING SETTLEMENT'], true)) {
        return 10;
    }

    return null;
}

function fn_cardlink_is_in_settlement_transit($transaction_details) {
    return fn_cardlink_get_settlement_status_code($transaction_details) === 10;
}

function fn_cardlink_is_settled($transaction_details) {
    $settlement_code = fn_cardlink_get_settlement_status_code($transaction_details);

    if ($settlement_code === null) {
        return false;
    }

    return $settlement_code === 1 || $settlement_code >= 20;
}

function fn_cardlink_parse_full_history_v2($xml_response) {
    if (empty($xml_response)) {
        return [];
    }

    $dom = new DOMDocument();
    @$dom->loadXML($xml_response);

    $details = $dom->getElementsByTagName('TransactionDetails');
    $history = [];

    foreach ($details as $tx) {
        if (!$tx instanceof DOMElement) {
            continue;
        }

        $data = [
            'order_id' => fn_cardlink_get_xml_node_value_v2($tx, 'OrderId'),
            'amount' => fn_cardlink_normalize_amount_v2(fn_cardlink_get_xml_node_value_v2($tx, 'OrderAmount')),
            'payment_total' => fn_cardlink_normalize_amount_v2(fn_cardlink_get_xml_node_value_v2($tx, 'PaymentTotal')),
            'status' => strtoupper(fn_cardlink_get_xml_node_value_v2($tx, 'Status')),
            'tx_id' => fn_cardlink_get_xml_node_value_v2($tx, 'TxId'),
            'tx_type' => strtoupper(fn_cardlink_get_xml_node_value_v2($tx, 'TxType')),
            'tx_date' => fn_cardlink_get_xml_node_value_v2($tx, 'TxDate'),
            'tx_completed' => fn_cardlink_get_xml_node_value_v2($tx, 'TxCompleted'),
            'description' => fn_cardlink_get_xml_node_value_v2($tx, 'Description'),
            'payment_ref' => fn_cardlink_get_xml_node_value_v2($tx, 'PaymentRef'),
            'attributes' => []
        ];

        foreach ($tx->getElementsByTagName('Attribute') as $attr) {
            if (!$attr instanceof DOMElement) {
                continue;
            }

            $name = trim($attr->getAttribute('name'));
            $data['attributes'][$name] = trim($attr->nodeValue);
        }

        $data['is_same_day'] = fn_cardlink_is_same_business_day($data['tx_completed'] ?: $data['tx_date']);
        $data['settlement_code'] = fn_cardlink_get_settlement_status_code($data);
        $data['is_in_settlement_transit'] = fn_cardlink_is_in_settlement_transit($data);
        $data['is_settled'] = fn_cardlink_is_settled($data);
        $history[] = $data;
    }

    return $history;
}

function fn_cardlink_is_capture_like_transaction_v2(array $transaction) {
    if (strtoupper((string) $transaction['status']) !== 'CAPTURED') {
        return false;
    }

    if (strtoupper((string) $transaction['tx_type']) === 'CAPTURE') {
        return true;
    }

    return !in_array(strtoupper((string) $transaction['tx_type']), ['REFUND', 'VOID', 'REVERSAL'], true);
}

function fn_cardlink_get_remaining_amount_for_capture($capture_tx, $history) {
    $captured_amount = !empty($capture_tx['attributes']['CAPTURED AMOUNT'])
        ? fn_cardlink_normalize_amount_v2($capture_tx['attributes']['CAPTURED AMOUNT'])
        : fn_cardlink_normalize_amount_v2($capture_tx['amount']);

    if ($captured_amount <= 0) {
        $captured_amount = fn_cardlink_normalize_amount_v2($capture_tx['payment_total']);
    }

    $refunded_amount = !empty($capture_tx['attributes']['REFUNDED AMOUNT'])
        ? fn_cardlink_normalize_amount_v2($capture_tx['attributes']['REFUNDED AMOUNT'])
        : 0.0;

    return max(0.0, round($captured_amount - $refunded_amount, 2));
}

function fn_cardlink_get_capture_transactions($order_info, $processor_data) {
    $history = fn_cardlink_get_transaction_status_v2($order_info, $processor_data);

    return !empty($history['capture_transactions']) ? $history['capture_transactions'] : [];
}

function fn_cardlink_get_transaction_status_v2($order_info, $processor_data) {
    $params = $processor_data['processor_params'];
    $shared_secret = $params['shared_secret'];
    $mid = $params['merchant_id'];
    $tx_id = !empty($order_info['payment_info']['transaction_id']) ? $order_info['payment_info']['transaction_id'] : '';
    $order_payment_id = !empty($order_info['payment_info']['orderid']) ? $order_info['payment_info']['orderid'] : '';

    if (empty($tx_id) || empty($order_payment_id)) {
        return false;
    }

    $dt = new \DateTime('now', new \DateTimeZone('Europe/Athens'));
    $timestamp = $dt->format('Y-m-d\TH:i:s.vP');
    $message_id = 'M' . (int) (microtime(true) * 1000);

    $request_content = "<StatusRequest>
        <Authentication>
            <Mid>{$mid}</Mid>
        </Authentication>
        <TransactionInfo>
            <OrderId>{$order_payment_id}</OrderId>
        </TransactionInfo>
    </StatusRequest>";

    $xml_ns = 'http://www.modirum.com/schemas/vposxmlapi41';
    $xml_ns_ns2 = 'http://www.w3.org/2000/09/xmldsig#';
    $message_xml = '<Message xmlns="' . $xml_ns . '" xmlns:ns2="' . $xml_ns_ns2 . '" messageId="' . $message_id . '" timeStamp="' . $timestamp . '" version="2.1">';
    $message_xml .= "\n" . $request_content . "\n";
    $message_xml .= '</Message>';

    $canonicalized = trim(preg_replace('/<\?xml[^?]*\?>\s*/i', '', $message_xml));
    $digest = base64_encode(hash('sha256', $canonicalized . $shared_secret, true));

    $final_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
    $final_xml .= '<VPOS xmlns="' . $xml_ns . '" xmlns:ns2="' . $xml_ns_ns2 . '">' . "\n";
    $final_xml .= '<Message messageId="' . $message_id . '" timeStamp="' . $timestamp . '" version="2.1">' . "\n";
    $final_xml .= $request_content . "\n";
    $final_xml .= '</Message>' . "\n";
    $final_xml .= '<Digest>' . $digest . '</Digest>' . "\n";
    $final_xml .= '</VPOS>';

    $response = fn_cardlink_post_to_vpos(fn_cardlink_get_direct_api_endpoint_url($processor_data), $final_xml);

    if (empty($response)) {
        return false;
    }

    $raw_history = fn_cardlink_parse_full_history_v2($response);
    $history_context = [
        'payment_info' => !empty($order_info['payment_info']) && is_array($order_info['payment_info']) ? $order_info['payment_info'] : []
    ];

    $capture_rows = [];
    $fallback_capture_rows = [];
    foreach ($raw_history as $transaction) {
        if (!fn_cardlink_is_capture_like_transaction_v2($transaction)) {
            continue;
        }

        $transaction['remaining_amount'] = fn_cardlink_get_remaining_amount_for_capture($transaction, $history_context);
        $transaction['can_void'] = $transaction['remaining_amount'] > 0 && $transaction['is_same_day'] && !$transaction['is_settled'];
        $transaction['can_refund'] = $transaction['remaining_amount'] > 0 && !$transaction['can_void'];
        $transaction['action'] = $transaction['can_void'] ? 'void' : ($transaction['can_refund'] ? 'refund' : '');

        if (strtoupper((string) $transaction['tx_type']) === 'CAPTURE') {
            $capture_rows[] = $transaction;
        } else {
            $fallback_capture_rows[] = $transaction;
        }
    }
    if (empty($capture_rows)) {
        $capture_rows = $fallback_capture_rows;
    }

    if (count($capture_rows) > 1) {
        foreach ($capture_rows as &$capture_row) {
            if (!empty($capture_row['can_void'])) {
                $capture_row['can_void'] = false;
                $capture_row['action'] = $capture_row['can_refund'] ? 'refund' : '';
            }
        }
        unset($capture_row);
    }

    $last_voidable_key = null;
    foreach ($capture_rows as $row_key => $capture_row) {
        if (!empty($capture_row['can_void'])) {
            $last_voidable_key = $row_key;
        }
    }

    if ($last_voidable_key !== null) {
        foreach ($capture_rows as $row_key => &$capture_row) {
            if (empty($capture_row['can_void'])) {
                continue;
            }

            if ($row_key !== $last_voidable_key) {
                $capture_row['can_void'] = false;
                $capture_row['action'] = '';
            }
        }
        unset($capture_row);
    }

    $authorization_tx = null;
    $status = 'UNKNOWN';
    $settlement = '';
    $original_amount = (float) $order_info['total'];
    $has_reversed_authorization = false;

    foreach ($raw_history as $transaction) {
        $tx_type = strtoupper((string) $transaction['tx_type']);
        $tx_status = strtoupper((string) $transaction['status']);
        $tx_amount = fn_cardlink_normalize_amount_v2($transaction['amount']);

        if ($tx_status === 'ERROR' || $tx_status === 'DENIED') {
            continue;
        }

        if ($tx_type === 'AUTHORIZATION') {
            $authorization_tx = $transaction;
            $original_amount = $tx_amount > 0 ? $tx_amount : $original_amount;
            if ($status === 'UNKNOWN') {
                $status = 'AUTHORIZED';
            }
            continue;
        }

        if (fn_cardlink_is_capture_like_transaction_v2($transaction)) {
            $status = 'CAPTURED';

            if ($settlement === '' && !empty($transaction['attributes']['SETTLEMENT STATUS'])) {
                $settlement = $transaction['attributes']['SETTLEMENT STATUS'];
            }
            continue;
        }

        if (in_array($tx_type, ['VOID', 'REVERSAL'], true) && in_array($tx_status, ['CANCELED', 'REVERSED'], true)) {
            $has_reversed_authorization = true;
        }
    }

    $total_captured = 0.0;
    $refundable_amount = 0.0;
    foreach ($capture_rows as $capture_row) {
        $capture_amount = !empty($capture_row['attributes']['CAPTURED AMOUNT'])
            ? fn_cardlink_normalize_amount_v2($capture_row['attributes']['CAPTURED AMOUNT'])
            : fn_cardlink_normalize_amount_v2($capture_row['amount']);

        if ($capture_amount <= 0) {
            $capture_amount = fn_cardlink_normalize_amount_v2($capture_row['payment_total']);
        }

        $total_captured += $capture_amount;
        $refundable_amount += $capture_row['remaining_amount'];
    }

    $total_captured = round($total_captured, 2);
    $refundable_amount = round($refundable_amount, 2);
    $total_refunded = max(0.0, round($total_captured - $refundable_amount, 2));
    $remaining_to_capture = max(0.0, round($original_amount - $total_captured, 2));

    if ($total_captured > 0) {
        $status = 'CAPTURED';
    } elseif ($has_reversed_authorization) {
        $status = 'REVERSED';
    } elseif (!empty($authorization_tx)) {
        $status = 'AUTHORIZED';
    }

    if ($total_captured > 0 && $refundable_amount <= 0) {
        $status = 'REFUNDED';
    }

    $single_capture = count($capture_rows) === 1 ? reset($capture_rows) : null;

    return [
        'status' => $status,
        'settlstatus' => $settlement,
        'total_captured' => $total_captured,
        'captured_amount' => $total_captured,
        'total_refunded' => $total_refunded,
        'refundable_amount' => $refundable_amount,
        'remaining_to_capture' => $remaining_to_capture,
        'is_same_day' => !empty($single_capture['is_same_day']),
        'has_multiple_captures' => count($capture_rows) > 1,
        'capture_transactions' => $capture_rows,
        'authorization_tx' => $authorization_tx,
        'order_level_action' => $single_capture ? $single_capture['action'] : '',
        'order_level_tx_id' => $single_capture ? $single_capture['tx_id'] : '',
        'order_level_amount' => $single_capture ? $single_capture['remaining_amount'] : 0.0,
        'raw' => $raw_history
    ];
}

function fn_cardlink_log_direct_action_v2($type, array $context) {
    error_log(
        '[' . date('c') . '] ' . $type . ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL,
        3,
        __DIR__ . '/cardlink_direct_actions.log'
    );
}

function fn_cardlink_get_direct_history_entries_v2(array $payment_info) {
    if (empty($payment_info['cardlink_direct_history'])) {
        return [];
    }

    if (is_array($payment_info['cardlink_direct_history'])) {
        return $payment_info['cardlink_direct_history'];
    }

    if (is_string($payment_info['cardlink_direct_history'])) {
        $decoded = json_decode($payment_info['cardlink_direct_history'], true);

        return is_array($decoded) ? $decoded : [];
    }

    return [];
}

function fn_cardlink_sanitize_payment_info_v2(array $payment_info) {
    $history_entries = fn_cardlink_get_direct_history_entries_v2($payment_info);

    if (isset($payment_info['cardlink_direct_history'])) {
        $payment_info['cardlink_direct_history'] = !empty($history_entries)
            ? json_encode($history_entries, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            : '';
    }

    return $payment_info;
}

function fn_cardlink_store_direct_action_result_v2($order_info, $action, array $operation, array $result) {
    $refreshed_order_info = fn_get_order_info((int) $order_info['order_id']);
    $payment_info = !empty($refreshed_order_info['payment_info']) && is_array($refreshed_order_info['payment_info'])
        ? $refreshed_order_info['payment_info']
        : [];

    $history_entries = fn_cardlink_get_direct_history_entries_v2($payment_info);

    $history_entries[] = [
        'created_at' => date('c'),
        'action' => $action,
        'target_tx_id' => !empty($operation['target_tx_id']) ? $operation['target_tx_id'] : '',
        'amount' => fn_cardlink_normalize_amount_v2($operation['amount']),
        'success' => !empty($result['success']),
        'status' => !empty($result['status']) ? $result['status'] : '',
        'tx_id' => !empty($result['tx_id']) ? $result['tx_id'] : '',
        'payment_ref' => !empty($result['payment_ref']) ? $result['payment_ref'] : '',
        'description' => !empty($result['description']) ? $result['description'] : '',
        'settlement_status' => !empty($result['settlement_status']) ? $result['settlement_status'] : ''
    ];

    $payment_info['cardlink_direct_history'] = json_encode($history_entries, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    fn_update_order_payment_info((int) $order_info['order_id'], $payment_info);
}

function fn_cardlink_build_direct_action_operation_v2($action, $amount, $target_tx_id = '', array $source_transaction = []) {
    return [
        'action' => $action,
        'amount' => round(fn_cardlink_normalize_amount_v2($amount), 2),
        'target_tx_id' => $target_tx_id,
        'source_transaction' => $source_transaction
    ];
}

function fn_cardlink_prepare_direct_action_operations_v2($order_info, $action, array $extra_params, array $history) {
    $requested_amount = array_key_exists('amount', $extra_params) && $extra_params['amount'] !== null && $extra_params['amount'] !== ''
        ? round(fn_cardlink_normalize_amount_v2($extra_params['amount']), 2)
        : null;
    $requested_tx_id = !empty($extra_params['tx_id']) ? trim((string) $extra_params['tx_id']) : '';
    $capture_transactions = !empty($history['capture_transactions']) ? $history['capture_transactions'] : [];

    if ($action === 'capture') {
        $remaining_to_capture = !empty($history['remaining_to_capture']) ? round((float) $history['remaining_to_capture'], 2) : 0.0;
        $capture_amount = $requested_amount !== null ? $requested_amount : $remaining_to_capture;

        if ($capture_amount <= 0 || $capture_amount > $remaining_to_capture + 0.0001) {
            return ['error' => __('cardlink.invalid_action_amount')];
        }

        return [fn_cardlink_build_direct_action_operation_v2('capture', $capture_amount)];
    }

    if (in_array($action, ['void', 'cancel'], true) && $history['status'] === 'AUTHORIZED' && empty($capture_transactions)) {
        $authorization_amount = $requested_amount !== null ? $requested_amount : (float) $order_info['total'];
        $authorization_tx_id = !empty($requested_tx_id)
            ? $requested_tx_id
            : (!empty($history['authorization_tx']['tx_id']) ? $history['authorization_tx']['tx_id'] : $order_info['payment_info']['transaction_id']);

        if ($authorization_amount <= 0 || $authorization_amount > (float) $order_info['total'] + 0.0001) {
            return ['error' => __('cardlink.invalid_action_amount')];
        }

        return [fn_cardlink_build_direct_action_operation_v2($action, $authorization_amount, $authorization_tx_id, !empty($history['authorization_tx']) ? $history['authorization_tx'] : [])];
    }

    $eligible_transactions = [];
    foreach ($capture_transactions as $capture_tx) {
        if ($requested_tx_id && $capture_tx['tx_id'] !== $requested_tx_id) {
            continue;
        }

        if ($action === 'refund' && !empty($capture_tx['can_refund'])) {
            $eligible_transactions[] = $capture_tx;
        }

        if (in_array($action, ['void', 'cancel'], true) && !empty($capture_tx['can_void'])) {
            $eligible_transactions[] = $capture_tx;
        }
    }

    if (empty($eligible_transactions)) {
        return ['error' => __('cardlink.no_available_transaction_action')];
    }

    usort($eligible_transactions, static function ($left, $right) {
        $left_sort = !empty($left['tx_completed']) ? $left['tx_completed'] : (!empty($left['tx_date']) ? $left['tx_date'] : '');
        $right_sort = !empty($right['tx_completed']) ? $right['tx_completed'] : (!empty($right['tx_date']) ? $right['tx_date'] : '');

        if ($left_sort === $right_sort) {
            return strcmp((string) $right['tx_id'], (string) $left['tx_id']);
        }

        return strcmp((string) $right_sort, (string) $left_sort);
    });

    $total_available = 0.0;
    foreach ($eligible_transactions as $capture_tx) {
        $total_available += (float) $capture_tx['remaining_amount'];
    }
    $total_available = round($total_available, 2);

    $remaining_amount = $requested_amount !== null ? $requested_amount : $total_available;
    if ($remaining_amount <= 0 || $remaining_amount > $total_available + 0.0001) {
        return ['error' => __('cardlink.invalid_action_amount')];
    }

    $operations = [];
    foreach ($eligible_transactions as $capture_tx) {
        if ($remaining_amount <= 0) {
            break;
        }

        $operation_amount = round(min((float) $capture_tx['remaining_amount'], $remaining_amount), 2);

        if ($operation_amount <= 0) {
            continue;
        }

        $operations[] = fn_cardlink_build_direct_action_operation_v2($action, $operation_amount, $capture_tx['tx_id'], $capture_tx);
        $remaining_amount = round($remaining_amount - $operation_amount, 2);
    }

    if ($remaining_amount > 0.0001) {
        return ['error' => __('cardlink.invalid_action_amount')];
    }

    return $operations;
}

function fn_cardlink_is_full_direct_action_amount_v2(array $operation) {
    $requested_amount = round(fn_cardlink_normalize_amount_v2($operation['amount']), 2);
    $available_amount = !empty($operation['source_transaction']['remaining_amount'])
        ? round(fn_cardlink_normalize_amount_v2($operation['source_transaction']['remaining_amount']), 2)
        : $requested_amount;

    return abs($requested_amount - $available_amount) < 0.01;
}

function fn_cardlink_is_partial_void_amount_v2($action, array $operation) {
    if (!in_array($action, ['void', 'cancel'], true)) {
        return false;
    }

    return !fn_cardlink_is_full_direct_action_amount_v2($operation);
}

function fn_cardlink_resolve_direct_action_request_v2($action, array $operation) {
    if (fn_cardlink_is_partial_void_amount_v2($action, $operation)) {
        return [
            'error' => __('cardlink.partial_void_not_supported'),
            'error_code' => 'PARTIAL_REVERSAL_NOT_SUPPORTED'
        ];
    }

    if ($action !== 'refund') {
        return ['request_action' => $action];
    }

    $source_transaction = !empty($operation['source_transaction']) ? $operation['source_transaction'] : [];
    $is_full_amount = fn_cardlink_is_full_direct_action_amount_v2($operation);
    $settlement_code = fn_cardlink_get_settlement_status_code($source_transaction);

    if ($settlement_code === 10) {
        if ($is_full_amount) {
            return [
                'request_action' => 'cancel',
                'converted_from_refund' => true
            ];
        }

        return [
            'error' => 'Transaction is in settlement transit. Please wait until settlement is complete and try again for partial refunds.',
            'error_code' => 'SETTLEMENT_TRANSIT'
        ];
    }

    if ($settlement_code === 0) {
        if ($is_full_amount) {
            return [
                'request_action' => 'cancel',
                'converted_from_refund' => true
            ];
        }

        return [
            'error' => 'Partial refund is not possible on unsettled transactions. Please wait for settlement to complete.',
            'error_code' => 'UNSETTLED_PARTIAL'
        ];
    }

    return ['request_action' => 'refund'];
}

function fn_cardlink_perform_direct_request_v2($order_info, $processor_data, $request_action, array $operation) {
    $params = $processor_data['processor_params'];
    $shared_secret = $params['shared_secret'];
    $mid = $params['merchant_id'];
    $currency = !empty($params['currency']) ? $params['currency'] : 'EUR';
    $action = $request_action;
    $target_tx_id = !empty($operation['target_tx_id']) ? $operation['target_tx_id'] : '';
    $amount = round(fn_cardlink_normalize_amount_v2($operation['amount']), 2);
    $formatted_amount = number_format($amount, 2, '.', '');

    $request_name = in_array($action, ['void', 'cancel'], true)
        ? 'CancelRequest'
        : ucfirst($action) . 'Request';

    $dt = new \DateTime('now', new \DateTimeZone('Europe/Athens'));
    $timestamp = $dt->format('Y-m-d\TH:i:s.vP');
    $message_id = 'M' . (int) (microtime(true) * 1000);

    $request_content = "    <{$request_name}>
        <Authentication>
            <Mid>{$mid}</Mid>
        </Authentication>
        <OrderInfo>
            <OrderId>{$order_info['payment_info']['orderid']}</OrderId>
            <OrderAmount>{$formatted_amount}</OrderAmount>
            <Currency>{$currency}</Currency>
        </OrderInfo>
    </{$request_name}>";

    $xml_ns = 'http://www.modirum.com/schemas/vposxmlapi41';
    $xml_ns_ns2 = 'http://www.w3.org/2000/09/xmldsig#';
    $message_xml = '<Message xmlns="' . $xml_ns . '" xmlns:ns2="' . $xml_ns_ns2 . '" messageId="' . $message_id . '" timeStamp="' . $timestamp . '" version="2.1">';
    $message_xml .= "\n" . $request_content . "\n";
    $message_xml .= '</Message>';

    $canonicalized = trim(preg_replace('/<\?xml[^?]*\?>\s*/i', '', $message_xml));
    $digest = base64_encode(hash('sha256', $canonicalized . $shared_secret, true));

    $final_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
    $final_xml .= '<VPOS xmlns="' . $xml_ns . '" xmlns:ns2="' . $xml_ns_ns2 . '">' . "\n";
    $final_xml .= '<Message messageId="' . $message_id . '" timeStamp="' . $timestamp . '" version="2.1">' . "\n";
    $final_xml .= $request_content . "\n";
    $final_xml .= '</Message>' . "\n";
    $final_xml .= '<Digest>' . $digest . '</Digest>' . "\n";
    $final_xml .= '</VPOS>';

    fn_cardlink_log_direct_action_v2('request', [
        'order_id' => $order_info['order_id'],
        'action' => $action,
        'requested_action' => !empty($operation['action']) ? $operation['action'] : $action,
        'target_tx_id' => $target_tx_id,
        'amount' => $formatted_amount
    ]);

    $response = fn_cardlink_post_to_vpos(fn_cardlink_get_direct_api_endpoint_url($processor_data), $final_xml);
    $status = strtoupper(fn_cardlink_extract_xml_value($response, 'Status'));
    $description = fn_cardlink_extract_xml_value($response, 'Description');
    $error_code = fn_cardlink_extract_xml_value($response, 'ErrorCode');
    $response_tx_id = fn_cardlink_extract_xml_value($response, 'TxId');
    $payment_ref = fn_cardlink_extract_xml_value($response, 'PaymentRef');

    fn_cardlink_log_direct_action_v2('response', [
        'order_id' => $order_info['order_id'],
        'action' => $action,
        'target_tx_id' => $target_tx_id,
        'amount' => $formatted_amount,
        'status' => $status,
        'error_code' => $error_code,
        'description' => $description
    ]);

    $success_statuses = ['CAPTURED', 'AUTHORIZED', 'APPROVED', 'SUCCESS', 'REFUNDED', 'CANCELED', 'REVERSED'];
    $result = [
        'success' => in_array($status, $success_statuses, true),
        'status' => $status,
        'tx_id' => $response_tx_id,
        'payment_ref' => $payment_ref,
        'description' => $description,
        'error_code' => $error_code,
        'settlement_status' => fn_cardlink_extract_xml_attribute($response, 'SETTLEMENT STATUS'),
        'executed_action' => $action
    ];

    return $result;
}

function fn_cardlink_execute_single_direct_action_v2($order_info, $processor_data, array $operation) {
    $action = strtolower((string) $operation['action']);
    $routing = fn_cardlink_resolve_direct_action_request_v2($action, $operation);

    if (!empty($routing['error'])) {
        $result = [
            'success' => false,
            'status' => 'ERROR',
            'tx_id' => '',
            'payment_ref' => '',
            'description' => $routing['error'],
            'error_code' => !empty($routing['error_code']) ? $routing['error_code'] : '',
            'settlement_status' => !empty($operation['source_transaction']['attributes']['SETTLEMENT STATUS'])
                ? $operation['source_transaction']['attributes']['SETTLEMENT STATUS']
                : '',
            'executed_action' => $action
        ];

        fn_cardlink_store_direct_action_result_v2($order_info, $action, $operation, $result);

        return $result;
    }

    $request_action = !empty($routing['request_action']) ? $routing['request_action'] : $action;
    $result = fn_cardlink_perform_direct_request_v2($order_info, $processor_data, $request_action, $operation);

    if (
        $action === 'refund'
        && $request_action === 'refund'
        && empty($result['success'])
        && !empty($result['error_code'])
        && strtoupper((string) $result['error_code']) === 'O1'
        && fn_cardlink_is_full_direct_action_amount_v2($operation)
    ) {
        $fallback_result = fn_cardlink_perform_direct_request_v2($order_info, $processor_data, 'cancel', $operation);
        if (!empty($fallback_result['success'])) {
            $fallback_result['converted_from_refund'] = true;
            $result = $fallback_result;
        }
    }

    fn_cardlink_store_direct_action_result_v2($order_info, $action, $operation, $result);

    return $result;
}

function fn_cardlink_execute_direct_action_v2($order_info, $processor_data, $action, $extra_params = []) {
    $tx_id = !empty($order_info['payment_info']['transaction_id']) ? $order_info['payment_info']['transaction_id'] : '';

    if (empty($tx_id)) {
        return false;
    }

    $action = strtolower($action);
    $history = fn_cardlink_get_transaction_status_v2($order_info, $processor_data);

    if (empty($history)) {
        fn_set_notification('E', __('error'), __('cardlink.status_refresh_failed'));

        return false;
    }

    $operations = fn_cardlink_prepare_direct_action_operations_v2($order_info, $action, $extra_params, $history);
    if (isset($operations['error'])) {
        fn_set_notification('E', __('error'), $operations['error']);

        return false;
    }

    foreach ($operations as $operation) {
        $result = fn_cardlink_execute_single_direct_action_v2($order_info, $processor_data, $operation);

        if (empty($result['success'])) {
            $message = __("cardlink.action_{$action}_failed");
            $message .= ': ' . ($result['description'] ?: ($result['error_code'] ? 'Error ' . $result['error_code'] : __('cardlink.unknown_error')));

            if (!empty($operation['target_tx_id'])) {
                $message .= ' [TxId: ' . $operation['target_tx_id'] . ', Amount: ' . number_format($operation['amount'], 2, '.', '') . ']';
            }

            fn_set_notification('E', __('error'), $message);

            return false;
        }
    }

    fn_set_notification('N', __('notice'), __("cardlink.action_{$action}_success"));

    return true;
}

/**
 * refund
 */
function fn_cardlink_fn_payment_issuing_refund(&$status, $order_info, $amount, $reason, $processor_data) {
    if ($processor_data['processor_script'] == 'cardlink.php') {
        fn_cardlink_execute_direct_action_v2($order_info, $processor_data, 'refund', ['amount' => $amount]);

        return true;
    }
}

/**
 * @param $order_id
 * @param $processor_data
 * @param $token
 * @return mixed
 *
 * @description Check this out. Needs more info
 */
function fn_cardlink_execute_direct_charge($order_id, $processor_data, $token) {
    $params = $processor_data['processor_params'];
    $order_info = fn_get_order_info($order_id);

    $url = ($params['mode'] == 'test') ? "https://ecommerce-test.cardlink.gr/vpos/api/v1/transactions/charge" : "https://ecommerce.cardlink.gr/vpos/api/v1/transactions/charge";

    $data = [
        'amount' => $order_info['total'],
        'currency' => $params['currency'],
        'paymentToken' => $token,
        'orderId' => $order_id,
        'description' => "Order #$order_id via Wallet"
    ];

    $auth = base64_encode($params['merchant_id'] . ':' . $params['api_password']);

    $response = Tygh\Http::post($url, json_encode($data), [
        'headers' => [
            'Content-Type: application/json',
            'Authorization: Basic ' . $auth
        ]
    ]);

    return json_decode($response, true);
}


/*WALLETS*/


function fn_cardlink_get_direct_api_js_url($processor_data): string {

    $endpoint_url = '';


    if ($processor_data['mode'] == 'test') {

        switch ($processor_data['acquirer']) {
            case 0 :
                $endpoint_url = "https://ecommerce-test.cardlink.gr/vpos/js";
                break;
            case 1 :
                $endpoint_url = "https://alphaecommerce-test.cardlink.gr/vpos/js";
                break;
            case 2 :
                $endpoint_url = "https://eurocommerce-test.cardlink.gr/vpos/js";
                break;
        }
    } else {
        switch ($processor_data['acquirer']) {
            case 0 :
                $endpoint_url = "https://ecommerce.cardlink.gr/vpos/js";
                break;
            case 1 :
                $endpoint_url = "https://www.alphaecommerce.gr/vpos/js";
                break;
            case 2 :
                $endpoint_url = "https://vpos.eurocommerce.gr/vpos/js";
                break;
        }
    }

    return $endpoint_url;
}



function fn_cardlink_get_mpi_url($processor_data) {
    $endpoint_url = '';


    if ($processor_data['mode'] == 'test') {

        switch ($processor_data['acquirer']) {
            case 0 :
                $endpoint_url = "https://ecommerce-test.cardlink.gr/vpos/js";
                break;
            case 1 :
                $endpoint_url = "https://alphaecommerce-test.cardlink.gr/vpos/js";
                break;
            case 2 :
                $endpoint_url = "https://eurocommerce-test.cardlink.gr/mdpaympi/MerchantServer";
                break;
        }
    } else {
        switch ($processor_data['acquirer']) {
            case 0 :
                $endpoint_url = "https://ecommerce.cardlink.gr/vpos/js";
                break;
            case 1 :
                $endpoint_url = "https://www.alphaecommerce.gr/vpos/js";
                break;
            case 2 :
                $endpoint_url = "https://eurocommerce.cardlink.gr/mdpaympi/MerchantServer";
                break;
        }
    }

    return $endpoint_url;
}



function fn_cardlink_get_apple_pay_js_url($processor_data) {
    $mid = $processor_data['merchant_id']; //'0101119349';//
    $secret =trim($processor_data['shared_secret']); // 'Cardlink123456789';//
    $version = 2;


    $date = (new DateTime('now', new DateTimeZone('Europe/Athens')))->format('YmdHi');


    $data_to_digest = $version . $mid . $date . $secret ;

    $digest = base64_encode(hash('sha256', $data_to_digest, true));
//    $digest = base64_encode(hash_hmac('sha256', $data_to_digest, $secret, true));

    $query = http_build_query([
        'version' => $version,
        'mid' => $mid,
        'date' => $date,
        'digest' => $digest,
    ], '', '&');

    return fn_cardlink_get_direct_api_js_url($processor_data) . "/applepaydirect.js?" . $query;



}


//BILL: EG: EDW
function fn_cardlink_get_google_pay_url($processor_data) {
    $mid = $processor_data['merchant_id']; //'0101119349';//
    $secret = trim($processor_data['shared_secret']); //'Cardlink123456789';//
    $version = 2;


    $date = (new DateTime('now', new DateTimeZone('Europe/Athens')))->format('YmdHi');


    $data_to_digest = $version . $mid . $date . $secret ;

    $digest = base64_encode(hash('sha256', $data_to_digest, true));
//    $digest = base64_encode(hash_hmac('sha256', $data_to_digest, $secret, true));

    $query = http_build_query([
        'version' => $version,
        'mid' => $mid,
        'date' => $date,
        'digest' => $digest,
    ], '', '&');

    return fn_cardlink_get_direct_api_js_url($processor_data) . "/googlepaydirect.js?" . $query;
}


function fn_cardlink_build_wallet_xml($order_info, $wallet_token, $processor_data, $wallet_type = 'google') {
    $mid = $processor_data['processor_params']['merchant_id'];
    $shared_secret = $processor_data['processor_params']['shared_secret'];
    $order_id = $order_info['order_id'];
    $amount = number_format($order_info['total'], 2, '.', '');
    $currency = "EUR";

    $dt = new \DateTime('now', new \DateTimeZone('Europe/Athens'));
    $timestamp = $dt->format('Y-m-d\TH:i:s.vP');
    $message_id = 'M' . (int)(microtime(true) * 1000);

    $attr_name = ($wallet_type == 'apple') ? 'applePaymentData' : 'googlePaymentData';
    $xml_ns = 'http://www.modirum.com/schemas/vposxmlapi41';
    $xml_ns_ns2 = 'http://www.w3.org/2000/09/xmldsig#';

    // 1. Δημιουργία του Message DOM για Canonicalization
    $dom = new \DOMDocument('1.0', 'UTF-8');
    $message = $dom->createElementNS($xml_ns, 'Message');
    $message->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ns2', $xml_ns_ns2);

    // Alphabetically
    $message->setAttribute('messageId', $message_id);
    $message->setAttribute('timeStamp', $timestamp);
    $message->setAttribute('version', '2.1');

    $saleRequest = $dom->createElement('SaleRequest');

    $auth = $dom->createElement('Authentication');
    $auth->appendChild($dom->createElement('Mid', $mid));
    $saleRequest->appendChild($auth);

    $orderInfo = $dom->createElement('OrderInfo');
    $orderInfo->appendChild($dom->createElement('OrderId', $order_id));
    $orderInfo->appendChild($dom->createElement('OrderDesc', "Order #{$order_id}"));
    $orderInfo->appendChild($dom->createElement('OrderAmount', $amount));
    $orderInfo->appendChild($dom->createElement('Currency', $currency));
    $saleRequest->appendChild($orderInfo);

    $saleRequest->appendChild($dom->createElement('PaymentInfo'));

    $walletInfo = $dom->createElement('WalletInfo');
    $attr = $dom->createElement('Attribute');
    $attr->setAttribute('name', $attr_name);
    $attr->appendChild($dom->createTextNode($wallet_token));
    $walletInfo->appendChild($attr);
    $saleRequest->appendChild($walletInfo);

    $message->appendChild($saleRequest);
    $dom->appendChild($message);

    // get C14N
    $canonicalized_message = $dom->firstChild->C14N();

    //Digest
    $data_to_hash = $canonicalized_message . $shared_secret;
    $digest = base64_encode(hash('sha256', $data_to_hash, true));

    //VPOS XML
    $vpos_dom = new \DOMDocument('1.0', 'UTF-8');
    $vpos_dom->standalone = true; // standalone="yes"

    $vpos = $vpos_dom->createElementNS($xml_ns, 'VPOS');
    $vpos->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ns2', $xml_ns_ns2);


    $node = $vpos_dom->importNode($message, true);
    $vpos->appendChild($node);

    $vpos->appendChild($vpos_dom->createElement('Digest', $digest));
    $vpos_dom->appendChild($vpos);

    return $vpos_dom->saveXML();
}
function fn_cardlink_post_to_vpos($url, $xml_payload) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/xml; charset=UTF-8',
        'Accept: application/xml',
        'Content-Length: ' . strlen($xml_payload)
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}


/**
 * Extract from tag eg: <Status>CAPTURED</Status>
 */
function fn_cardlink_extract_xml_value($xml, $tag_name) {
    if (empty($xml)) {
        return '';
    }

    $dom = new DOMDocument();
    @$dom->loadXML($xml);
    $elements = $dom->getElementsByTagName($tag_name);

    if ($elements->length > 0) {
        return $elements->item(0)->nodeValue;
    }

    return '';
}

/**
 * Extract from Attribute eg: <Attribute name="cardEncData">...</Attribute>
 */
function fn_cardlink_extract_xml_attribute($xml, $attr_value) {
    if (empty($xml)) {
        return '';
    }

    $dom = new DOMDocument();
    @$dom->loadXML($xml);
    $attributes = $dom->getElementsByTagName('Attribute');

    foreach ($attributes as $attribute) {
        if ($attribute->getAttribute('name') == $attr_value) {
            return $attribute->nodeValue;
        }
    }

    return '';
}


//TODO: NO USAGE
function fn_cardlink_build_wallet_3ds_sale_xml($merchant_id, $shared_secret, $order_id, $amount, $currency, $preparedTxId, $payMethod, array $threeDSData, $orderDesc = '')
{
    $xmlNamespace = 'http://www.modirum.com/schemas/vposxmlapi41';
    $xmlNamespaceNs2 = 'http://www.w3.org/2000/09/xmldsig#';
    $version = '2.1';

    // escape xml text nodes
    $enrollmentStatus = htmlspecialchars($threeDSData['enrollmentStatus'] ?? '', ENT_XML1, 'UTF-8');
    $authenticationStatus = htmlspecialchars($threeDSData['authenticationStatus'] ?? '', ENT_XML1, 'UTF-8');
    $cavv = htmlspecialchars($threeDSData['cavv'] ?? '', ENT_XML1, 'UTF-8');
    $xid = htmlspecialchars($threeDSData['xid'] ?? '', ENT_XML1, 'UTF-8');
    $eci = htmlspecialchars($threeDSData['eci'] ?? '', ENT_XML1, 'UTF-8');
    $protocol = htmlspecialchars($threeDSData['protocol'] ?? '', ENT_XML1, 'UTF-8');

    $protocolElement = $protocol !== '' ? "                <Protocol>{$protocol}</Protocol>\n" : '';

    // messageId/timestamp (ίδιο pattern)
    $messageId = 'M' . (int)(microtime(true) * 1000);
    $dt = new DateTime('now', new DateTimeZone('Europe/Athens'));
    $timestamp = $dt->format('Y-m-d\TH:i:s.vP');

    // sale request content (2ο request)
    $requestContent =
        "    <SaleRequest>\n" .
        "        <Authentication>\n" .
        "            <Mid>{$merchant_id}</Mid>\n" .
        "        </Authentication>\n" .
        "        <OrderInfo>\n" .
        "            <OrderId>{$order_id}</OrderId>\n" .
        "            <OrderDesc>" . htmlspecialchars($orderDesc, ENT_XML1, 'UTF-8') . "</OrderDesc>\n" .
        "            <OrderAmount>{$amount}</OrderAmount>\n" .
        "            <Currency>{$currency}</Currency>\n" .
        "        </OrderInfo>\n" .
        "        <PaymentInfo preparedTxId=\"{$preparedTxId}\">\n" .
        "            <PayMethod>{$payMethod}</PayMethod>\n" .
        "            <ThreeDSecure>\n" .
        "                <EnrollmentStatus>{$enrollmentStatus}</EnrollmentStatus>\n" .
        "                <AuthenticationStatus>{$authenticationStatus}</AuthenticationStatus>\n" .
        "                <CAVV>{$cavv}</CAVV>\n" .
        "                <XID>{$xid}</XID>\n" .
        "                <ECI>{$eci}</ECI>\n" .
        $protocolElement .
        "            </ThreeDSecure>\n" .
        "        </PaymentInfo>\n" .
        "        <WalletInfo>\n" .
        "            <Attribute></Attribute>\n" .
        "        </WalletInfo>\n" .
        "    </SaleRequest>";

    // build <Message> for digest (explicit xmlns attrs όπως magento)
    $messageXml = '<Message xmlns="' . $xmlNamespace . '" xmlns:ns2="' . $xmlNamespaceNs2 . '" messageId="' . $messageId . '" timeStamp="' . $timestamp . '" version="' . $version . '">';
    $messageXml .= "\n" . $requestContent . "\n";
    $messageXml .= '</Message>';

    // canonicalize = remove xml decl + trim (όπως στο magento code που έδειξες)
    $canonicalized = preg_replace('/<\?xml[^?]*\?>\s*/i', '', $messageXml);
    $canonicalized = trim($canonicalized);

    $digest = base64_encode(hash('sha256', $canonicalized . $shared_secret, true));

    // final VPOS xml (ίδιο structure με το magento class)
    $xml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
    $xml .= '<VPOS xmlns="' . $xmlNamespace . '" xmlns:ns2="' . $xmlNamespaceNs2 . '">' . "\n";
    $xml .= '<Message messageId="' . $messageId . '" timeStamp="' . $timestamp . '" version="' . $version . '">' . "\n";
    $xml .= $requestContent . "\n";
    $xml .= '</Message>' . "\n";
    $xml .= '<Digest>' . $digest . '</Digest>' . "\n";
    $xml .= '</VPOS>';

    return $xml;
}
