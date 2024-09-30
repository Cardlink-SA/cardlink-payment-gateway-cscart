<?php

/**
 * @var array $processor_data
 * @var array $order_info
 * @var string $mode
 */
defined('BOOTSTRAP') or die('Access denied');

use Tygh\Registry;
use Tygh\Tygh;


if (defined('PAYMENT_NOTIFICATION')) {
	$pp_response = array();
	$pp_response['order_status'] = 'F';
	$pp_response['reason_text'] = __('text_transaction_declined');
	$order_id = !empty($_REQUEST['order_id']) ? (int)$_REQUEST['order_id'] : 0;


	if ($mode == 'success' && !empty($_REQUEST['order_id'])) {
		$order_info = fn_get_order_info($order_id);

		if (empty($processor_data)) {
			$processor_data = fn_get_processor_data($order_info['payment_id']);
		}
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


			if (array_key_exists('xlsbonusdigest', $_REQUEST)) {
				isset($_REQUEST['xlsbonusdigest']) ? $xlsbonusdigest = $_REQUEST['xlsbonusdigest'] : $xlsbonusdigest = '';
			}
		}


		foreach ($post_data_values as $post_data_value) {
			if (isset($_REQUEST[$post_data_value])) {

				if (!in_array($post_data_value, array(
					'_charset_',
					'digest',
					'submitButton',
					'xlsbonusadjamt',
					'xlsbonustxid',
					'xlsbonusstatus',
					'xlsbonusdetails',
					'xlsbonusdigest'
				))) {
					$post_data[] = $_REQUEST[$post_data_value];
				}
				if (in_array($post_data_value, array(
					'xlsbonusadjamt',
					'xlsbonustxid',
					'xlsbonusstatus',
					'xlsbonusdetails'
				))) {
					$post_data_bonus[] = $_REQUEST[$post_data_value];
				}


			}
		}


		$form_secret = $processor_data['processor_params']['shared_secret'];
		$form_data = iconv('utf-8', 'utf-8//IGNORE', implode("", $post_data)) . $form_secret;
		$digest = base64_encode(hash('sha256', $form_data, true));

		$failed = false;

		if ($processor_data['processor_params']['acquirer'] == 1 && isset($_REQUEST['xlsbonusdigest'])) { //EG: Only if Nexi and only if the field was sent

			$failed = true;
			$form_data_bonus = iconv('utf-8', 'utf-8//IGNORE', implode("", $post_data_bonus)) . $form_secret;
			$digest_bonus = base64_encode(hash('sha256', $form_data_bonus, true));

			if ($xlsbonusdigest != '') {
				if ($xlsbonusdigest == $digest_bonus) {
					$failed = false;
				}
			}
		}


		if (!$failed && $_REQUEST['digest'] === $digest) {


			if ($_REQUEST['status'] == 'CAPTURED' || $_REQUEST['status'] == 'AUTHORIZED') {


				if ($order_info['user_id'] != 0) {
					fn_login_user($order_info['user_id']);
				}


				Tygh::$app['session']['auth']['order_ids'] = [$order_id];


				$auth['order_ids'] = [$order_id];

				$pp_response['order_status'] = 'P';
				$pp_response['reason_text'] = __('transaction_approved');
				$pp_response['transaction_id'] = $_REQUEST['paymentRef'];

				$payMethod = $_REQUEST['payMethod'];

				$extToken = isset($_REQUEST['extToken']) ? filter_var($_REQUEST['extToken'], FILTER_SANITIZE_STRING) : '';
				$extTokenPanEnd = isset($_REQUEST['extTokenPanEnd']) ? filter_var($_REQUEST['extTokenPanEnd'], FILTER_SANITIZE_STRING) : '';
				$extTokenExp = isset($_REQUEST['extTokenExp']) ? $_REQUEST['extTokenExp'] : '';
				$extTokenExpYear = substr($extTokenExp, 0, 4);
				$extTokenExpMonth = substr($extTokenExp, 4, 2);


				$card_exist = db_get_field("SELECT card_id FROM ?:cardlink_cards WHERE card_type=?s AND last_four=?s AND expiry_year=?s AND expiry_month", $payMethod, $extTokenPanEnd, $extTokenExpYear, $extTokenExpMonth);

				if ($extToken && !$card_exist) {
					// Build the token
					$token = [
						'token'        => $extToken,
						'last_four'    => $extTokenPanEnd,
						'expiry_year'  => $extTokenExpYear,
						'expiry_month' => $extTokenExpMonth,
						'card_type'    => $payMethod,
						'user_id'      => $order_info['user_id']
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
		} else {
			$pp_response['order_status'] = 'F';
			$pp_response['reason_text'] = __("cardlink.digest_wrong");

		}

		if ($pp_response['order_status'] != 'P') {
			//EG: do not logout the user
			$order_info = fn_get_order_info($order_id);

			if ($order_info['user_id'] != 0) {
				fn_login_user($order_info['user_id']);
			} else {
				fn_form_cart($order_id, $_SESSION['cart'], $_SESSION['auth']);
			}
		}


	} else {
		$order_info = fn_get_order_info($order_id);


		if ($order_info['user_id'] != 0) {
			fn_login_user($order_info['user_id']);
		} else {
			fn_form_cart($order_id, $_SESSION['cart'], $_SESSION['auth']);
		}
	}

	if (fn_check_payment_script('cl_iris.php', $order_id)) {
		fn_finish_payment($order_id, $pp_response);
		fn_order_placement_routines('route', $order_id);
	}

} else {


	$processor_data_iris = $processor_data; //Store data of the IRIS payment method
	$processor_data = fn_get_processor_data(fn_cardlink_get_payment_id()); //get the data from the Cardlink payment method


	$payment_url = '';
	if ($processor_data['processor_params']['mode'] == 'test') {
		switch ($processor_data['processor_params']['acquirer']) {
			case 0 :
				//This is wrong. Only nexi is allowed
				fn_cardlink_return_error("A");
				//$payment_url = "https://ecommerce-test.cardlink.gr/vpos/shophandlermpi";
				break;
			case 1 :
				$payment_url = "https://alphaecommerce-test.cardlink.gr/vpos/shophandlermpi";
				break;
			case 2 :
				//This is wrong. Only nexi is allowed
				fn_cardlink_return_error("A");
				//$payment_url = "https://eurocommerce-test.cardlink.gr/vpos/shophandlermpi";
				break;
		}
	} else {
		switch ($processor_data['processor_params']['acquirer']) {
			case 0 :
				//This is wrong. Only nexi is allowed
				fn_cardlink_return_error("A");
				//$payment_url = "https://ecommerce.cardlink.gr/vpos/shophandlermpi";
				break;
			case 1 :
				$payment_url = "https://www.alphaecommerce.gr/vpos/shophandlermpi";
				break;
			case 2 :
				//This is wrong. Only nexi is allowed
				fn_cardlink_return_error("A");
				//$payment_url = "https://vpos.eurocommerce.gr/vpos/shophandlermpi";
				break;
		}
	}


	if (preg_match("#\_#", $order_id)) {
		list($order_id) = fn_update_order($_SESSION['cart'], 0);
	}

	$version = 2;
//	$trType = $processor_data['processor_params']['transaction_type'] == 'yes' ? 2 : 1;
	$country = $order_info['b_country'];
	$ship_country = $order_info['s_country'];
	$state_code = $order_info['b_state'];

	$amount = fn_format_price($order_info['total'], $processor_data['processor_params']['currency']);
	$confirm_url = fn_url("payment_notification.success?payment=cl_iris&order_id=$order_id", AREA, 'current');
	$cancel_url = fn_url("payment_notification.fail?payment=cl_iris&order_id=$order_id", AREA, 'current');

	$installments = '';
	$offset = '';


	$phone = $order_info['phone'];
	if (!trim($phone)) {
		$phone = $order_info['b_phone'];
		if (!trim($phone)) {
			$phone = $order_info['s_phone'];
		}
	}


	$post_data = array(
		'version'              => $version,
		'mid'                  => $processor_data['processor_params']['merchant_id'],
		'lang'                 => CART_LANGUAGE,
		'orderid'              => $order_id . 'at' . date('Ymdhisu'),
		'orderDesc'            => fn_cardlink_iris_rf_code($order_id, $processor_data_iris['processor_params']['iris_customer_code']),
		//EG: Added to get RF
		'orderAmount'          => $amount,
		'currency'             => $processor_data['processor_params']['currency'],
		'payerEmail'           => $order_info['email'],
		'payerPhone'           => $phone,
		//EG: Added for IRIS
		'billCountry'          => $country,
		//'billState'            => $state_code,
		'billZip'              => $order_info['b_zipcode'],
		'billCity'             => $order_info['b_city'],
		'billAddress'          => $order_info['b_address'],
		'shipCountry'          => $ship_country,
		'shipZip'              => $order_info['s_zipcode'],
		'shipCity'             => $order_info['s_city'],
		'shipAddress'          => $order_info['s_address'],
		'payMethod'            => 'IRIS',
//		'trType'               => $trType,
		'cssUrl'               => $processor_data['processor_params']['css_url'],
		'extInstallmentoffset' => $offset,
		'extInstallmentperiod' => $installments,
		'confirmUrl'           => $confirm_url,
		'cancelUrl'            => $cancel_url,


	);




	$form_secret = $processor_data['processor_params']['shared_secret'];
	$form_data = iconv('utf-8', 'utf-8//IGNORE', implode("", $post_data)) . $form_secret;
	$post_data['digest'] = base64_encode(hash('sha256', $form_data, true));

	fn_create_payment_form($payment_url, $post_data, 'IRIS', false);

}
exit;
