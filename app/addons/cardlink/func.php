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


	$processor = [
		"processor"          => 'Cardlink',
		"processor_script"   => 'cardlink.php',
		"processor_template" => 'views/orders/components/payments/cc_outside.tpl',
		"admin_template"     => 'cardlink.tpl',
		"callback"           => 'N',
		"type"               => 'P',
		"addon"              => 'cardlink'
	];

	$pid = db_query("INSERT INTO `?:payment_processors` ?e", $processor);

	//also add the payment methods if not exist
	if (!fn_cardlink_get_payment_id()) {
		$payment_data = array(
			'storefront_ids'   => '',
			'payment_id'       => '',
			'processor_id'     => $pid,
			'payment'          => 'Cardlink',
			'company_id'       => '1',
			'description'      => '',
			'instructions'     => '',
			'usergroup_ids'    => '0',
			'p_surcharge'      => '0.000',
			'a_surcharge'      => '0.000',
			'surcharge_title'  => '',
			'processor_params' => array(
				'acquirer'         => '0',
				'iris_org_id'      => '',
				'iris_api_user'    => '',
				'iris_api_pass'    => '',
				'merchant_id'      => '',
				'shared_secret'    => '',
				'css_url'          => '',
				'currency'         => 'EUR',
				'transaction_type' => 'yes',
				'tokenization'     => 'yes',
				'iframe_mode'      => 'N',
				'mode'             => 'live',
			),
		);

		//hack to upload an image for the payment method
		$_REQUEST['payment_image_image_data'] = array(
			'0' => array(
				'pair_id'      => '',
				'type'         => 'M',
				'object_id'    => 0,
				'image_alt'    => ''
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
		"processor"          => 'IRIS by Cardlink',
		"processor_script"   => 'cl_iris.php',
		"processor_template" => 'views/orders/components/payments/cc_outside.tpl',
		"admin_template"     => 'cl_iris.tpl',
		"callback"           => 'N',
		"type"               => 'P',
		"addon"              => 'cardlink'
	];

	$pid = db_query("INSERT INTO `?:payment_processors` ?e", $processor);

	//also add the payment methods if not exist
	if (!fn_cardlink_get_iris_payment_id()) {
		$payment_data = array(
			'storefront_ids'   => '',
			'payment_id'       => '',
			'processor_id'     => $pid,
			'payment'          => 'IRIS Payments by Cardlink',
			'company_id'       => '1',
			'description'      => '',
			'instructions'     => '',
			'usergroup_ids'    => '0',
			'p_surcharge'      => '0.000',
			'a_surcharge'      => '0.000',
			'surcharge_title'  => '',
			'processor_params' => array(
				'iris_customer_code'             => '',
			),
		);

		//hack to upload an image for the payment method


		$_REQUEST['payment_image_image_data'] = array(
			'0' => array(
				'pair_id'      => '',
				'type'         => 'M',
				'object_id'    => 0,
				'image_alt'    => ''
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
		'type'     => 'A',
		'data'     => serialize(['rf_payment_code' => $rf_payment_code]),
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
	foreach ($addons as $addon){
		$has = db_get_field("SELECT status FROM ?:addons WHERE addon=?s AND status='A'",$addon);

		if($has=='A'){
			$exists = true;
			break;
		}
	}

	return $exists;
}