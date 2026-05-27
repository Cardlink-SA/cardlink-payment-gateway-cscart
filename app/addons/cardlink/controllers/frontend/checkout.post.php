<?php

use Tygh\Tygh;

if($_SERVER['REQUEST_METHOD']=='POST'){
	if($mode=='remove_cardlink_card'){
		db_query("DELETE FROM ?:cardlink_cards WHERE card_id=?i",$_REQUEST['card_id']);
		echo json_encode(['status'=>'ok']);
		exit;
	}
}

if($mode=='update_installments'){
	$_SESSION['cart']['installments'] = $_REQUEST['installments'];
	echo json_encode(['status'=>'ok']);
	exit;
}

if($mode=='checkout'){


    if($_SESSION['current_order_id']){
        $order = fn_get_order_info($_SESSION['current_order_id']);
        if(!$order){
            unset($_SESSION['current_order_id']);
        }
    }

	if(!$_SESSION['current_order_id']){
		list($order_id) = fn_place_order($_SESSION['cart'], $_SESSION['auth'], 'N');
		$_SESSION['current_order_id'] = $order_id;
	}



	Tygh::$app['view']->assign('order_id',$_SESSION['current_order_id']);
}
