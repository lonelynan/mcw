<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
//require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/distribute.php');

if(isset($_GET["UsersID"])){
	$UsersID=$_GET["UsersID"];
}else{
	echo '缺少必要的参数';
	exit;
}

$base_url = base_url();
$pifa_url = $base_url.'api/'.$UsersID.'/pifa/';

$TypeID=empty($_GET["TypeID"])?0:$_GET["TypeID"];

if(!empty($_SESSION[$UsersID."User_ID"])){
	$userexit = $DB->GetRs("user","*","where User_ID=".$_SESSION[$UsersID."User_ID"]." and Users_ID='".$UsersID."'");
	if(!$userexit){
		$_SESSION[$UsersID."User_ID"] = "";
	}
}

if(!strpos($_SERVER['REQUEST_URI'],"mp.weixin.qq.com")){
	header("location:?wxref=mp.weixin.qq.com");
}

/*分享页面初始化配置*/
$share_flag = 1;
$signature = '';

/*若用户没有登陆，跳转到登陆页面*/
if(empty($_SESSION[$UsersID."User_ID"]) || !isset($_SESSION[$UsersID."User_ID"])){
	$_SESSION[$UsersID."HTTP_REFERER"]="/api/".$UsersID."/user/coupon/";
	header("location:/api/".$UsersID."/user/login/?wxref=mp.weixin.qq.com");
}

$rsConfig=$DB->GetRs("shop_config","*","where Users_ID='".$UsersID."'");
//商城配置一股脑转换批发配置
$Config = $DB->GetRs("pifa_config","*","where Users_ID='".$UsersID."'");
$rsConfig['ShopName'] = $Config['PifaName'];
$rsConfig['NeedShipping'] = $Config['p_NeedShipping'];
$rsConfig['SendSms'] = $Config['p_SendSms'];
$rsConfig['MobilePhone'] = $Config['p_MobilePhone'];
$rsConfig['Commit_Check'] = $Config['p_Commit_Check'];

$Status  = !empty($_GET['status'])?$_GET['status']:0;

//获取此次request的action,若无action,用默认值list
$action = isset($_GET['action'])?$_GET['action']:'list';

//显示收藏夹内商品
if($action == 'list'){
	//获取此用户所收藏的商品
	$sql = "select f.FAVOURITE_ID,p.Products_ID,p.Products_Name,p.Products_JSON
from pifa_Products as p
join user_favourite_products as f
on p.Products_id = f.Products_ID and f.User_ID =".$_SESSION[$UsersID.'User_ID'];
	$resource = $DB->query($sql);
	$result = $DB->toArray($resource);
	foreach($result as $key=>$item){
		$JSON = json_decode($item['Products_JSON'],TRUE);
		$product = $item;
		$product['ImgPath'] = $JSON["ImgPath"][0];
		$favourList[$product['Products_ID']] = $product;
	}
}elseif($action == 'del'){
	
	//删除收藏夹内指定商品
	$condition = 'User_ID='.$_SESSION[$UsersID.'User_ID'].' and FAVOURITE_ID='.$_GET['favour_id'];
	echo $condition;
	
	$Flag=$DB->Del("user_favourite_products",$condition);

	header("location:".$_SERVER['HTTP_REFERER']);
	exit;
}



?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta content="telephone=no" name="format-detection" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>个人中心</title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/api/shop/skin/default/css/style.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/api/js/global.js'></script>
<script type='text/javascript' src='/static/api/pifa/js/js.js'></script>
</head>

<body>
<div id="shop_page_contents">
	<div id="cover_layer"></div>
	<link href='/static/api/shop/skin/default/css/member.css' rel='stylesheet' type='text/css' />
	<div id="favourite_list">
		<?php if(isset($favourList)):?>
		<?php foreach($favourList as $key=>$item):?>
		<div class="item">
			<div class="del">
				<div cartid="5_0"><a href="/api/<?=$UsersID?>/pifa/member/favourite/del/<?=$item['FAVOURITE_ID']?>/"><img src="/static/api/shop/skin/default/images/del.gif"></a></div>
			</div>
			<div class="img"><a href="/api/<?=$UsersID?>/pifa/product/<?=$key?>/"><img src="<?=$item['ImgPath']?>" height="100" width="100"></a></div>
			<dl class="info">
				<dd class="name"><a href="/api/<?=$UsersID?>/pifa/product/<?=$key?>/">
					<?=$item['Products_Name']?>
					</a> </dd>
			</dl>
			<div class="clear"></div>
		</div>
		<?php endforeach;?>
		<?php else:?>
		<p style="margin-left:20px;">&nbsp;&nbsp;&nbsp;搜藏夹中暂无产品!</p>
		<?php endif;?>
	</div>
</div>
<?php require_once('../footer.php');?>