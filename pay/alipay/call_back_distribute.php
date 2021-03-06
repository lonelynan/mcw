<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/distribute.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Ext/virtual.func.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/order.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Ext/sms.func.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/pay_order.class.php');

$out_trade_no = $_GET['out_trade_no'];
$trade_no = $_GET['trade_no'];

$OrderID = substr($out_trade_no,10);
$rsOrder=$DB->GetRs("distribute_order","Users_ID,User_ID,Order_Status","where Order_ID='".$OrderID."'");
if(!$rsOrder){
	echo "订单不存在";
	exit;
}
$UsersID = $rsOrder["Users_ID"];
$UserID = $rsOrder["User_ID"];
$Status = $rsOrder["Order_Status"];

$pay_order = new pay_order($DB,$OrderID);
$rsPay=$DB->GetRs("users_payconfig","*","where Users_ID='".$UsersID."'");
$rsUsers=$DB->GetRs("users","*","where Users_ID='".$UsersID."'");
$rsUser=$DB->GetRs("user","*","where Users_ID='".$UsersID."' and User_ID=".$UserID);
require_once("alipay.config.php");
require_once("lib/alipay_notify.class.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php

$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyReturn();
if($verify_result) {//验证成功
	if($Status==1){
		$data = $pay_order->deal_distribute_order(2,$trade_no);
		if($data["status"]==1){
			$url = '/api/'.$UsersID.'/distribute/';
			echo "<script type='text/javascript'>window.location.href='".$url."';</script>";	
			exit;		
		}else{
			echo $data["msg"];
			exit;
		}
	}else{
		$url = '/api/'.$UsersID.'/distribute/';
		echo "<script type='text/javascript'>window.location.href='".$url."';</script>";	
		exit;
	}
}else {
    echo "验证失败";
}
?>
        <title>支付宝即时到账交易接口</title>
	</head>
    <body>
    </body>
</html>