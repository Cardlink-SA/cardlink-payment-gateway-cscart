<?php


parse_str("version=2&mid=3692581&orderid=O240528172241&status=CAPTURED&orderAmount=10.0&currency=EUR&paymentTotal=10.0&message=OK, IRIS tx id: IRISEMU1716906168129 StsId:762d8d74-cffc-4caa-a2f2-de903870acb9 status: 'AUTHORISED'&riskScore=&payMethod=IRIS&txId=92639546561243&paymentRef=IRISEMU1716906168129&digest=ofZ09XtmWBd+bY7/UGCcO1Soy2CD6FwXg0y4nlTJzQk=&submitButton=Click here to continue",$parsed);

$original = $parsed;

$parsedDigest = 'ofZ09XtmWBd+bY7/UGCcO1Soy2CD6FwXg0y4nlTJzQk=';
unset($parsed['submitButton']);
unset($parsed['digest']);
//unset($parsed['message']);



$form_secret = 'alpha_test_1';
$form_data = iconv('utf-8', 'utf-8//IGNORE', implode("", $parsed)) . $form_secret;
$digest = base64_encode(hash('sha256', $form_data, true));

fn_print_die([
				 'Digest in Payload: '=>$parsedDigest,
				 'Digest calculated: '=>$digest,
				 'Digest is same: '=> $parsedDigest==$digest,
				 'payload'=>$original
			 ]);