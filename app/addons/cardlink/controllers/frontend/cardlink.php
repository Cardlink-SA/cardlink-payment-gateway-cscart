<?php
if (!defined('BOOTSTRAP')) {
    die('Access denied');
}
use Tygh\Registry;

if ($mode == 'gpay_error_finalize') {

    $mpi = $_REQUEST;


    $xid = (string)($mpi['xid'] ?? '');
    if ($xid === '') {
        fn_set_notification("E",__("cardlink.transaction_failed"),'UNKNOWN ERROR');
        return array(CONTROLLER_STATUS_REDIRECT, fn_url('checkout.checkout'));
    }


    $stored = db_get_row("SELECT * FROM ?:cardlink_wallet_data WHERE xid=?s",$xid);
    // 2. Ανάκτηση της προσωρινής παραγγελίας που δημιουργήσαμε στο checkout mode
    $order_id = $_SESSION['current_order_id'];
    if (empty($order_id)) {
        fn_set_notification("E",__("cardlink.transaction_failed"),'UNKNOWN ERROR');
        return array(CONTROLLER_STATUS_REDIRECT, fn_url('checkout.checkout'));
    }

    $pp_response['reason_text'] = $_REQUEST['mdErrorMsg'];
    $pp_response['order_status'] = 'F';
    fn_finish_payment($order_id, $pp_response);
    fn_set_notification("E",__("cardlink.transaction_failed"),$_REQUEST['mdErrorMsg']);


    return array(CONTROLLER_STATUS_REDIRECT, fn_url('checkout.checkout'));
}



if ($mode == 'finalize_gpay') {

    $mpi = $_POST;




    $xid = (string)($mpi['xid'] ?? '');
    if ($xid === '') {
        fn_print_die('Missing XID', $mpi);
    }


    $stored = db_get_row("SELECT * FROM ?:cardlink_wallet_data WHERE xid=?s",$xid);


    $genericError = false;

    if (empty($stored)) {
        fn_set_notification("E",__("cardlink.transaction_failed"),'No stored 3DS session for XID (cache miss/expired)');
        return array(CONTROLLER_STATUS_REDIRECT, fn_url('checkout.checkout'));
    }

    $order_id     = (int)($stored['order_id'] ?? 0);

    $order_info = fn_get_order_info($order_id);

    $preparedTxId = (string)($stored['preparedTxId'] ?? '');
    $amount       = (string) $order_info['total'];
    $currency     = 'EUR';
    $orderDesc    = 'Order ' . $order_id;
    $payMethod    = (string)($stored['payMethod'] ?? '');

    if ($order_id <= 0 || $preparedTxId === '' || $amount === '') {
//        fn_print_die('Stored cache data incomplete', ['stored' => $stored, 'mpi' => $mpi]);
        db_query("DELETE FROM ?:cardlink_wallet_data WHERE order_id=?i",$order_id);
        $genericError = true;
    }

    // MPI status fields
    $mdStatus    = (string)($mpi['mdStatus'] ?? '');
    $mdErrorMsg  = (string)($mpi['mdErrorMsg'] ?? '');
    $eci         = (string)($mpi['eci'] ?? '');
    $cavv        = (string)($mpi['cavv'] ?? '');
    $enrolled    = (string)($mpi['veresEnrolledStatus'] ?? '');
    $paresStatus = (string)($mpi['paresTxStatus'] ?? '');
    $protocol    = (string)($mpi['protocol'] ?? '');

    // Αν ο MPI γύρισε error, fail payment
    if ($mdStatus === '7') {
        $pp_response = [
            'order_status' => 'F',
            'reason_text'  => $mdErrorMsg !== '' ? $mdErrorMsg : ('3DS error (mdStatus=' . $mdStatus . ')')
        ];
        fn_finish_payment($order_id, $pp_response);
        db_query("DELETE FROM ?:cardlink_wallet_data WHERE order_id=?i",$order_id);
        fn_set_notification("E",__("cardlink.transaction_failed"),$mdErrorMsg !== '' ? $mdErrorMsg : ('3DS error (mdStatus=' . $mdStatus . ')'));
        return array(CONTROLLER_STATUS_REDIRECT, fn_url('checkout.checkout'));
    }




    $order_info = fn_get_order_info($order_id);
    $processor_data = fn_get_processor_data($order_info['payment_id']);

    $merchant_id   = (string)($processor_data['processor_params']['merchant_id'] ?? '');
    $shared_secret = (string)($processor_data['processor_params']['shared_secret'] ?? '');


    $payMethod = $stored['payMethod'];


    // ---- build SECOND SaleRequest XML (preparedTxId + ThreeDSecure) ----
    $xmlNamespace    = 'http://www.modirum.com/schemas/vposxmlapi41';
    $xmlNamespaceNs2 = 'http://www.w3.org/2000/09/xmldsig#';
    $apiVersion      = '2.1';

    $messageId = 'M' . (int)(microtime(true) * 1000);
    $dt = new DateTime('now', new DateTimeZone('Europe/Athens'));
    $timestamp = $dt->format('Y-m-d\TH:i:s.vP');

    // Escape for XML nodes
    $orderIdXml   = htmlspecialchars((string)$order_id, ENT_XML1, 'UTF-8');
    $orderDescXml = htmlspecialchars($orderDesc, ENT_XML1, 'UTF-8');
    $amountXml    = htmlspecialchars($amount, ENT_XML1, 'UTF-8');
    $currencyXml  = htmlspecialchars($currency, ENT_XML1, 'UTF-8');

    $preparedTxIdAttr = htmlspecialchars($preparedTxId, ENT_QUOTES, 'UTF-8');
    $payMethodXml     = htmlspecialchars($payMethod, ENT_XML1, 'UTF-8');

    $enrolledXml    = htmlspecialchars($enrolled, ENT_XML1, 'UTF-8');
    $paresStatusXml = htmlspecialchars($paresStatus, ENT_XML1, 'UTF-8');
    $cavvXml        = htmlspecialchars($cavv, ENT_XML1, 'UTF-8');

    // XID: βάλε όπως έρχεται (b64). Αν χρειαστεί raw -> base64_decode.
    $xidXml = htmlspecialchars($xid, ENT_XML1, 'UTF-8');

    $eciXml      = htmlspecialchars($eci, ENT_XML1, 'UTF-8');
    $protocolXml = htmlspecialchars($protocol, ENT_XML1, 'UTF-8');

    $protocolElement = ($protocolXml !== '')
        ? "                <Protocol>{$protocolXml}</Protocol>\n"
        : '';

    $requestContent =
        "    <SaleRequest>\n" .
        "        <Authentication>\n" .
        "            <Mid>{$merchant_id}</Mid>\n" .
        "        </Authentication>\n" .
        "        <OrderInfo>\n" .
        "            <OrderId>{$orderIdXml}</OrderId>\n" .
        "            <OrderDesc>{$orderDescXml}</OrderDesc>\n" .
        "            <OrderAmount>{$amountXml}</OrderAmount>\n" .
        "            <Currency>{$currencyXml}</Currency>\n" .
        "        </OrderInfo>\n" .
        "        <PaymentInfo preparedTxId=\"{$preparedTxIdAttr}\">\n" .
        "            <PayMethod>{$payMethodXml}</PayMethod>\n" .
        "            <ThreeDSecure>\n" .
        "                <EnrollmentStatus>{$enrolledXml}</EnrollmentStatus>\n" .
        "                <AuthenticationStatus>{$paresStatusXml}</AuthenticationStatus>\n" .
        "                <CAVV>{$cavvXml}</CAVV>\n" .
        "                <XID>{$xidXml}</XID>\n" .
        "                <ECI>{$eciXml}</ECI>\n" .
        $protocolElement .
        "            </ThreeDSecure>\n" .
        "        </PaymentInfo>\n" .
        "        <WalletInfo>\n" .
        "            <Attribute></Attribute>\n" .
        "        </WalletInfo>\n" .
        "    </SaleRequest>";

    // Digest calc on <Message xmlns..>
    $messageXml =
        '<Message xmlns="' . $xmlNamespace . '" xmlns:ns2="' . $xmlNamespaceNs2 . '" messageId="' . $messageId . '" timeStamp="' . $timestamp . '" version="' . $apiVersion . '">' .
        "\n" . $requestContent . "\n" .
        '</Message>';

    $canonicalized = preg_replace('/<\?xml[^?]*\?>\s*/i', '', $messageXml);
    $canonicalized = trim($canonicalized);

    $digest = base64_encode(hash('sha256', $canonicalized . $shared_secret, true));

    $xml_payload  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
    $xml_payload .= '<VPOS xmlns="' . $xmlNamespace . '" xmlns:ns2="' . $xmlNamespaceNs2 . '">' . "\n";
    $xml_payload .= '<Message messageId="' . $messageId . '" timeStamp="' . $timestamp . '" version="' . $apiVersion . '">' . "\n";
    $xml_payload .= $requestContent . "\n";
    $xml_payload .= '</Message>' . "\n";
    $xml_payload .= '<Digest>' . $digest . '</Digest>' . "\n";
    $xml_payload .= '</VPOS>';



    // Send to VPOS
    $vpos_url = fn_cardlink_get_direct_api_endpoint_url($processor_data);
    $response_xml = fn_cardlink_post_to_vpos($vpos_url, $xml_payload);



    $status = fn_cardlink_extract_xml_value($response_xml, 'Status');
    $tx_id_final = fn_cardlink_extract_xml_value($response_xml, 'TxId');

    if ($status === 'CAPTURED') {
        fn_change_order_status($order_id, 'P');
        fn_update_order_payment_info($order_id, ['transaction_id' => $tx_id_final]);

        if($order_info['user_id']!=0){
            fn_login_user($order_info['user_id']);
        }

        Tygh::$app['session']['auth']['order_ids'] = [$order_id];


        db_query("DELETE FROM ?:cardlink_wallet_data WHERE order_id=?i",$order_id);
        return array(CONTROLLER_STATUS_REDIRECT, fn_url('checkout.complete?order_id=' . $order_id));
    }


    db_query("DELETE FROM ?:cardlink_wallet_data WHERE order_id=?i",$order_id);
    // Fail
    $pp_response = [
        'order_status' => 'F',
        'reason_text'  => 'VPOS finalize failed. Status=' . ($status ?: 'UNKNOWN')
    ];
    fn_finish_payment($order_id, $pp_response);


    fn_set_notification("E",__("cardlink.transaction_failed"),'VPOS finalize failed. Status=' . ($status ?: 'UNKNOWN'));


    return array(CONTROLLER_STATUS_REDIRECT, fn_url('checkout.checkout'));
}



if ($mode == 'sign_data') {
    $request = json_decode(file_get_contents('php://input'), true);

    $payment_id = isset($request['payment_id']) ? $request['payment_id'] : fn_cardlink_get_payment_id();
    $processor_data = fn_get_processor_data($payment_id);


    $purchAmount = (float)$request['purchAmount'];
    $purchaseAmountFormatted = ($purchAmount == 0) ? "" : round($purchAmount * 100);



    $data = $request;

    $shared_secret = $processor_data['processor_params']['shared_secret'];


    $mpiVersion = '2.0';


    $fields = [
        $mpiVersion ?? $data['mpiVersion'] ?? '',
        $data['pan'] ?? '',
        $data['expiry'] ?? '',
        $data['cardEncData'] ?? '',
        $data['devCat'] ?? '0',
        $purchaseAmountFormatted,
        $data['exponent'] ?? '2',
        $data['description'] ?? '',
        $data['currMpi'] ?? '978',
        $data['merchantID'] ?? '',
        $data['xidb64'] ?? '',
        $data['okUrl'] ?? '',
        $data['failUrl'] ?? '',
    ];

    // Optionally add recurring/installment fields if present
    if (!empty($data['recurFreq'])) {
        $fields[] = $data['recurFreq'];
    }
    if (!empty($data['recurEnd'])) {
        $fields[] = $data['recurEnd'];
    }
    if (!empty($data['installments'])) {
        $fields[] = $data['installments'];
    }

    $payload = implode('', $fields);


    $signature = base64_encode(hash('sha1', $payload . $shared_secret, true));

    $response = [
        'signature' => $signature,
        'purchaseAmountFormatted' => (string)$purchaseAmountFormatted
    ];



    //save xid for future ref
    $xid_b64 = $data['xidb64'];
    $order_id = (int) str_replace("Order ","",$data['description']);

    $e = [
        'xid'          =>   $xid_b64,
    ];
    db_query("UPDATE ?:cardlink_wallet_data SET ?u WHERE order_id=?i",$e,$order_id);



    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

if ($mode == 'wallet_sale') {


    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);


    $wallet_type = '';
    $payment_token = "";

    if (!empty($data['googlePayResponse'])) {
        $payment_token = $data['googlePayResponse'];
        $wallet_type = 'google';
    } elseif (!empty($data['applePayResponse'])) {
        $payment_token = $data['applePayResponse'];
        $wallet_type = 'apple';
    }

    if (empty($payment_token)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'No wallet token received']);
        exit;
    }

    $order_id = $_SESSION['current_order_id'];
    if (empty($order_id)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'No active order session found']);
        exit;
    }


    $order_info = fn_get_order_info($order_id);
    $processor_data = fn_get_processor_data($order_info['payment_id']);

    $xml_payload = fn_cardlink_build_wallet_xml($order_info, $payment_token, $processor_data, $wallet_type);

    $vpos_url = fn_cardlink_get_direct_api_endpoint_url($processor_data);


    $response_xml = fn_cardlink_post_to_vpos($vpos_url, $xml_payload);


    $status = fn_cardlink_extract_xml_value($response_xml, 'Status');
    $tx_id = fn_cardlink_extract_xml_value($response_xml, 'TxId');



    //save for future ref
    if($tx_id){
        $response = json_decode($payment_token,1);
        $e = [
            'order_id'  =>  $order_id,
            'preparedTxId'  =>  $tx_id,
            'payMethod'  =>  mb_convert_case($response['info']['cardNetwork'] ?? 'visa',MB_CASE_LOWER,"UTF-8"),
            'created_at'    =>  time()
        ];
        db_query("INSERT INTO ?:cardlink_wallet_data ?e",$e);
    }

    header('Content-Type: application/json');


    if ($status == 'CAPTURED') {
        fn_change_order_status($order_id, 'P'); // Processed
        fn_update_order_payment_info($order_id, ['transaction_id' => $tx_id]);

        unset($_SESSION['current_order_id']);

        echo json_encode(['status' => 'success', 'order_id' => $order_id]);
        exit;

    } elseif ($status == 'PROCESSING') {
        $res = [
            'status' => 'processing',
            'orderId' => $order_id,
            'orderAmount' => $order_info['total'],
            'txId' => $tx_id,
            'cardEncData' => fn_cardlink_extract_xml_attribute($response_xml, 'cardEncData'),
            'trExtId' => fn_cardlink_extract_xml_attribute($response_xml, 'trExtId'),
            'trMpiCounts' => fn_cardlink_extract_xml_attribute($response_xml, 'trMpiCounts'),
        ];
        echo json_encode($res);
        exit;

    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Payment Failed',
            'vpos_status' => $status
        ]);
        exit;
    }
} elseif ($mode == 'confirmation') {
    $payment_data = $_REQUEST;

    error_log("--- Cardlink Background Confirmation Start ---", 3, __DIR__ . '/cardlink.log');
    error_log("IP: " . $_SERVER['REMOTE_ADDR'], 3, __DIR__ . '/cardlink.log');
    error_log("POST Data: " . print_r($payment_data, true), 3, __DIR__ . '/cardlink.log');
    error_log("--- Cardlink Background Confirmation End ---", 3, __DIR__ . '/cardlink.log');


    //find order id. We usually get something like [ORDER_ID]at[DATESTRING]
    $order_id = explode("at", $_REQUEST['orderid']);
    $order_id = (int)$order_id[0];


    if (!empty($order_id)) {
        $order_info = fn_get_order_info($order_id);
        $processor_data = fn_get_processor_data($order_info['payment_id']);

        $xlsbonusdigest = '';
        $post_data = $post_data_bonus = array();
        $post_data_values = array(
            'version',
            'mid',
            'orderid',
            'status',
            'orderAmount',
            'currency',
            'paymentTotal',
            'message',
            'riskScore',
            'payMethod',
            'txId',
            'paymentRef',
            'extToken',
            'extTokenPanEnd',
            'extTokenExp',
        );


        //EG: ALPHA Bonus
        if ($processor_data['processor_params']['acquirer'] == 1) { //EG: Only if Nexi
            $post_data_values[] = 'xlsbonusadjamt';
            $post_data_values[] = 'xlsbonustxid';
            $post_data_values[] = 'xlsbonusstatus';
            $post_data_values[] = 'xlsbonusdetails';
            $post_data_values[] = 'xlsbonusawards';
            $post_data_values[] = 'xlsbonusdigest';


            if (array_key_exists('xlsbonusdigest', $payment_data)) {
                isset($payment_data['xlsbonusdigest']) ? $xlsbonusdigest = $payment_data['xlsbonusdigest'] : $xlsbonusdigest = '';
            }
        }


        foreach ($post_data_values as $post_data_value) {
            if (isset($payment_data[$post_data_value])) {

                if (!in_array($post_data_value, array('_charset_', 'digest', 'submitButton', 'xlsbonusadjamt', 'xlsbonustxid', 'xlsbonusstatus', 'xlsbonusdetails', 'xlsbonusdigest'))) {
                    $post_data[] = $payment_data[$post_data_value];
                }
                if (in_array($post_data_value, array('xlsbonusadjamt', 'xlsbonustxid', 'xlsbonusstatus', 'xlsbonusdetails'))) {
                    $post_data_bonus[] = $payment_data[$post_data_value];
                }
            }
        }


        $form_secret = $processor_data['processor_params']['shared_secret'];
        $form_data = iconv('utf-8', 'utf-8//IGNORE', implode("", $post_data)) . $form_secret;
        $digest = base64_encode(hash('sha256', $form_data, true));

        $failed = false;

        if ($processor_data['processor_params']['acquirer'] == 1 && isset($payment_data['xlsbonusdigest'])) { //EG: Only if Nexi and only if the field was sent

            $failed = true;
            $form_data_bonus = iconv('utf-8', 'utf-8//IGNORE', implode("", $post_data_bonus)) . $form_secret;
            $digest_bonus = base64_encode(hash('sha256', $form_data_bonus, true));

            if ($xlsbonusdigest != '') {
                if ($xlsbonusdigest == $digest_bonus) {
                    $failed = false;
                }
            }
        }


        if (!$failed && $payment_data['digest'] === $digest) {
            if ($payment_data['status'] == 'CAPTURED' || $payment_data['status'] == 'AUTHORIZED') {


                $pp_response['order_status'] = $payment_data['status'] == 'CAPTURED' ? 'P' : 'O';
                $pp_response['reason_text'] = __('transaction_approved');
                $pp_response['transaction_id'] = $payment_data['paymentRef'];
                $pp_response['orderid'] = $payment_data['orderid'];

                $payMethod = $payment_data['payMethod'];

                $extToken = isset($payment_data['extToken']) ? filter_var($payment_data['extToken'], FILTER_SANITIZE_STRING) : '';
                $extTokenPanEnd = isset($payment_data['extTokenPanEnd']) ? filter_var($payment_data['extTokenPanEnd'], FILTER_SANITIZE_STRING) : '';
                $extTokenExp = isset($payment_data['extTokenExp']) ? $payment_data['extTokenExp'] : '';
                $extTokenExpYear = substr($extTokenExp, 0, 4);
                $extTokenExpMonth = substr($extTokenExp, 4, 2);


                $card_exist = db_get_field("SELECT card_id FROM ?:cardlink_cards WHERE card_type=?s AND last_four=?s AND expiry_year=?s AND expiry_month", $payMethod, $extTokenPanEnd, $extTokenExpYear, $extTokenExpMonth);

                if ($extToken && !$card_exist) {
                    // Build the token
                    $token = [
                        'token' => $extToken,
                        'last_four' => $extTokenPanEnd,
                        'expiry_year' => $extTokenExpYear,
                        'expiry_month' => $extTokenExpMonth,
                        'card_type' => $payMethod,
                        'user_id' => $order_info['user_id']
                    ];
                    db_query("INSERT INTO ?:cardlink_cards ?e", $token);
                }
                //				fn_print_die($card_exist,$token,$_REQUEST);
            } elseif ($_REQUEST['status'] == 'CANCELED') {

                $pp_response['order_status'] = 'I';
                $pp_response['reason_text'] = __("text_transaction_cancelled");

            } elseif ($_REQUEST['status'] == 'REFUSED') {

                $pp_response['order_status'] = 'D';
                $pp_response['reason_text'] = __("text_transaction_declined");

            } elseif ($_REQUEST['status'] == 'ERROR') {

                $pp_response['order_status'] = 'F';
                $pp_response['reason_text'] = __("cardlink.unexpected_error");

            } elseif ($_REQUEST['status'] == 'AUTHORISED-EXPIRED') {

                $pp_response['order_status'] = 'F';
                $pp_response['reason_text'] = __("cardlink.preauth_expired");

            } elseif ($_REQUEST['status'] == 'REVERSED') {

                $pp_response['order_status'] = 'F';
                $pp_response['reason_text'] = __("cardlink.preauth_canceled");

            } else {
                $pp_response['order_status'] = 'F';
                $pp_response['reason_text'] = __("cardlink.unknown_error");
            }

            fn_finish_payment($order_id, $pp_response);

            echo "OK"; // Η Cardlink θέλει αυτό για να σταματήσει να στέλνει


        } else {

            echo "DIGEST_INVALID";
        }
    }
    exit;
}