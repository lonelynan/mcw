<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
if(isset($_GET["UsersID"])){
	$UsersID=$_GET["UsersID"];
}else{
	echo '缺少必要的参数';
	exit;
}
$base_url = base_url();
$pifa_url = $base_url.'api/'.$UsersID.'/pifa/';
if(isset($_GET["ProductsID"])){
	$ProductsID=$_GET["ProductsID"];
}else{
	echo '缺少必要的参数';
	exit;
}

//商城配置信息
$rsConfig = shop_config($UsersID);
//分销相关设置
$dis_config = dis_config($UsersID);
//合并参数
$rsConfig = array_merge($rsConfig,$dis_config);

//商城配置一股脑转换批发配置
$Config = $DB->GetRs("pifa_config","*","where Users_ID='".$UsersID."'");
$rsConfig['ShopName'] = $Config['PifaName'];
$rsConfig['NeedShipping'] = $Config['p_NeedShipping'];
$rsConfig['SendSms'] = $Config['p_SendSms'];
$rsConfig['MobilePhone'] = $Config['p_MobilePhone'];
$rsConfig['Commit_Check'] = $Config['p_Commit_Check'];

$owner = get_owner($rsConfig,$UsersID);
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/wechatuser.php');
$owner = get_owner($rsConfig,$UsersID);
if($owner['id'] != '0'){
	$rsConfig["ShopName"] = $owner['shop_name'];
	$rsConfig["ShopLogo"] = $owner['shop_logo'];
	$pifa_url = $pifa_url.$owner['id'].'/';
};
//获取此产品
$rsProducts=$DB->GetRs("pifa_products","*","where Users_ID='".$UsersID."' and Products_SoldOut=0 and Products_ID=".$ProductsID);
if(!$rsProducts){
	echo "暂无此信息！";
	exit;
}

//加入访问记录
$Data=array(
	"Users_ID"=>$UsersID,
	"S_Module"=>"pifa",
	"S_CreateTime"=>time()
);
$DB->Add("statistics",$Data);
//调用模版
$header_title = '全部评论 - '.$rsProducts["Products_Name"].' - '.$rsConfig["ShopName"];
$share_link = $pifa_url.'commit/'.$ProductsID.'/';
require_once('../share.php');
$share_title = $share_desc = '全部评论 - '.$rsProducts["Products_Name"].' - '.$rsConfig["ShopName"];
$share_img = 'http://'.$_SERVER["HTTP_HOST"].'/static/api/images/cover_img/shop.jpg';
if(isset($JSON["ImgPath"])){
	$share_img = 'http://'.$_SERVER["HTTP_HOST"].$JSON["ImgPath"][0];
}

$C = $DB->GetRS("users","Users_Logo","where Users_ID='".$UsersID."'");
include("skin/commitlist.php");
?>