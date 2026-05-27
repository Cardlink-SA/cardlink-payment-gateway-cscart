<?php

use Tygh\Tygh;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'cardlink_direct') {
	$order_id = $_REQUEST['order_id'];
	$action = $_REQUEST['action'];
    $amount = !empty($_REQUEST['amount']) ? $_REQUEST['amount'] : null;
    $tx_id = !empty($_REQUEST['tx_id']) ? $_REQUEST['tx_id'] : null;

	$order_info = fn_get_order_info($order_id);
	$processor_data = fn_get_processor_data($order_info['payment_id']);

	if (!empty($order_info) && $action) {
        $extra_params = [];
        if ($amount !== null) {
            $extra_params['amount'] = $amount;
        }
        if ($tx_id !== null) {
            $extra_params['tx_id'] = $tx_id;
        }

        $done = fn_cardlink_execute_direct_action_v2($order_info, $processor_data, $action, $extra_params);

		if($done){
			switch ($action){
				case "capture":
					if($amount==$order_info['total']){
						fn_change_order_status($order_id,"P");

					}
					break;
			}
		}
	}

	return array(CONTROLLER_STATUS_REDIRECT, "orders.details?order_id=$order_id");
}elseif($mode=='details'){
	$order_id = $_REQUEST['order_id'];
	$order_info = fn_get_order_info($order_id);
    if (!empty($order_info['payment_info']) && is_array($order_info['payment_info'])) {
        $sanitized_payment_info = fn_cardlink_sanitize_payment_info_v2($order_info['payment_info']);

        if ($sanitized_payment_info !== $order_info['payment_info']) {
            $order_info['payment_info'] = $sanitized_payment_info;
            fn_update_order_payment_info($order_id, $sanitized_payment_info);
        }

        unset($order_info['payment_info']['cardlink_direct_history']);
        Tygh::$app['view']->assign('order_info', $order_info);
    }
	$processor_data = fn_get_processor_data($order_info['payment_id']);
	$cardlink_status = fn_cardlink_get_transaction_status_v2($order_info,$processor_data);
	if (!empty($cardlink_status['raw'])) {
		$cardlink_status['raw'] = array_reverse($cardlink_status['raw']);
	}
	$cardlink_status = !empty($cardlink_status) ? $cardlink_status : [];
	$cardlink_status += ['status' => '', 'settlstatus' => '', 'raw' => []];

	Tygh::$app['view']->assign('cardlink_status', $cardlink_status['status']); // π.χ. CAPTURED ή REVERSED
	Tygh::$app['view']->assign('cardlink_settlement', $cardlink_status['settlstatus']); // π.χ. READY ή 1
	Tygh::$app['view']->assign('cardlink_status_full', !empty($cardlink_status) ? $cardlink_status : []);
	Tygh::$app['view']->assign('cardlink_status', !empty($cardlink_status['status']) ? $cardlink_status['status'] : '');
	Tygh::$app['view']->assign('cardlink_settlement', !empty($cardlink_status['settlstatus']) ? $cardlink_status['settlstatus'] : '');


//	fn_print_die($cardlink_status);


}
