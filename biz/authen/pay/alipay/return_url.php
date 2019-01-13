<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/biz/global.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/FF/vendor/alipay/pc/Submit.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/FF/vendor/alipay/pc/Corefunction.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/FF/vendor/alipay/pc/Md5function.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/FF/vendor/alipay/pc/Notify.php');

    $out_trade_no = $_GET['out_trade_no'];
    $OrderID = substr($out_trade_no, 10);
    $UsersID = $rsBiz['Users_ID'];
    //$rsOrder = $DB->GetRs("biz_pay","*","where Users_ID='".$UsersID."' and Biz_ID='".$_SESSION["BIZ_ID"]."' and id='".$OrderID."'");
    $rsOrder = $DB->GetRs("biz_pay","*","where id='".$OrderID."'");
	if(!$rsOrder){
        echo '订单不存在';
        exit;
    }
    $UsersID = $rsOrder['Users_ID'];
    $biz_id = $rsOrder['biz_id'];
    $Status = $rsOrder['status'];
    $years = !empty($rsOrder['years'])?$rsOrder['years']:1;//时间
    $expiredate = time()+$years*365*24*3600;
    $bond_free = $rsOrder['bond_free']; //保证金

    $users_payconfig = $DB->GetRs("users_payconfig","*","where Users_ID='".$UsersID."'");
    $alipay_config = array(
        'sign_type'=>'MD5',
        'key'=>$users_payconfig['Payment_AlipayKey'],
        'transport'=>'http',
        'partner'=>$users_payconfig['Payment_AlipayPartner'],
        'input_charset'=> strtolower('utf-8'),
    );
    
         
    $alipayNotify = new \vendor\alipay\pc\Notify($alipay_config);//计算得出通知验证结果
    $verify_result = $alipayNotify->verifyReturn();
    if($verify_result) {//验证成功
        if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
            if($Status == 0) {
                $Data = array(
                    "status" => 1,
                    "paytime" => time()
                );
                $url = "/biz/index.php";
                mysql_query("BEGIN");
                //$flag_a = $DB->set('biz_pay',$Data,"where Users_ID='".$UsersID."' and Biz_ID='".$_SESSION["BIZ_ID"]."' and id='".$OrderID."'");
                $flag_a = $DB->set('biz_pay',$Data,"where Users_ID='".$UsersID."' and Biz_ID='".$Biz_ID."' and id='".$OrderID."'");
               
				$Data1 = array(
                        "bond_free" => $bond_free,
                        "expiredate" => $expiredate,
                        'is_pay' => 1,
                        'is_biz' => 1
                    );
                //$flag_b = $DB->set('biz',$Data1,"where Users_ID='".$UsersID."' and Biz_ID=".$_SESSION["BIZ_ID"]);
                $flag_b = $DB->set('biz',$Data1,"where Users_ID='".$UsersID."' and Biz_ID=".$Biz_ID);
				
				if ($flag_a && $flag_b){
							mysql_query('commit');
					echo '<script type=\'text/javascript\'>window.location.href=\''.$url.'\';</script>';	
					exit;		
				}else {
							mysql_query("ROLLBACK");
					echo '支付失败!';
					exit;
				}
                
            }else{
				$url = "/biz/index.php";
                echo '<script type=\'text/javascript\'>window.location.href=\''.$url.'\';</script>';	
				exit;
            }
           // $url = "biz/index.php";
          //  echo '<script type=\'text/javascript\'>window.location.href=\''.$url.'\';</script>';	
           // exit;
        } else {
             echo 'trade_status=' . $_GET['trade_status'];
             exit;
        }
    }else {
        //验证失败
        echo "支付失败！";
    }
    
?>