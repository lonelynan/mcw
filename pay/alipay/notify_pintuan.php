﻿<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/distribute.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Ext/virtual.func.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/order.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Ext/sms.func.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/pay_order.class.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/api/pintuan/comm/function.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/lib_pintuan.php');

if(empty($_POST)) {//判断POST来的数组是否为空
	echo "fail";
	exit;
}
$xml = $_POST['notify_data'];

$doc1 = new DOMDocument();
$doc1->loadXML($xml);
$out_trade_no = $doc1->getElementsByTagName( "out_trade_no" )->item(0)->nodeValue;
$OrderID = 0;
if(strpos($out_trade_no,'PRE')>-1){
	$OrderID = $out_trade_no;
	$rsOrder = $DB->GetRs("user_pre_order","*","where pre_sn='".$OrderID."'");
	if(!$rsOrder){
		echo "faile";
		exit;
	}
	$UsersID = $rsOrder["usersid"];
	$UserID = $rsOrder["userid"];
	$Status = $rsOrder["status"];
}else{
	$rsOrder=$DB->GetRs("user_order","Order_ID,Users_ID,User_ID,Order_Status","where Order_Code='".$out_trade_no."'");
	if(!$rsOrder){
		echo "faile";
		exit;
	}
	$OrderID = $rsOrder['Order_ID'];
	$UsersID = $rsOrder["Users_ID"];
	$UserID = $rsOrder["User_ID"];
	$Status = $rsOrder["Order_Status"];
}
$pay_order = new pay_order($DB,$OrderID);
$rsPay=$DB->GetRs("users_payconfig","*","where Users_ID='".$UsersID."'");
$rsUsers=$DB->GetRs("users","*","where Users_ID='".$UsersID."'");
$rsUser=$DB->GetRs("user","*","where Users_ID='".$UsersID."' and User_ID=".$UserID);

require_once("alipay.config.php");
require_once("lib/alipay_notify.class.php");

$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyNotify();

if($verify_result) {
	$doc = new DOMDocument();	
	if ($alipay_config['sign_type'] == 'MD5') {
		$doc->loadXML($xml);
	}
	if( ! empty($doc->getElementsByTagName( "notify" )->item(0)->nodeValue) ) {
		$trade_no = $doc->getElementsByTagName( "trade_no" )->item(0)->nodeValue;		
		$trade_status = $doc->getElementsByTagName( "trade_status" )->item(0)->nodeValue;
		if($trade_status == 'TRADE_FINISHED') {
			if($Status==1){
			    $data = payDone($UsersID, $OrderID, "支付宝", $trade_no);
				if($data["status"]==1){
					echo "success";
					exit;		
				}else{
					echo "fail";
					exit;
				}
			}else{
				echo "success";
				exit;
			}
		}
		else if ($trade_status == 'TRADE_SUCCESS') {
			if($Status==1){
				$data = payDone($UsersID, $OrderID, "支付宝", $trade_no);
				if($data["status"]==1){
					echo "success";
					exit;		
				}else{
					echo "fail";
					exit;
				}
			}else{
				echo "success";
				exit;
			}
		}
	}
}
else {
    echo "fail";
}
?>