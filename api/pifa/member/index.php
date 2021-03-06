<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
//require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/distribute.php');

/*分享页面初始化配置*/
$share_flag = 1;
$signature = '';

if(isset($_GET["UsersID"])){
	$UsersID=$_GET["UsersID"];
}else{
	echo '缺少必要的参数';
	exit;
}

if(!empty($_SESSION[$UsersID."User_ID"])){
	$userexit = $DB->GetRs("user","*","where User_ID=".$_SESSION[$UsersID."User_ID"]." and Users_ID='".$UsersID."'");
	if(!$userexit){
		$_SESSION[$UsersID."User_ID"] = "";
	}	
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
$is_login=1;
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/wechatuser.php');

$Status=empty($_GET["Status"])?0:$_GET["Status"];

$rsUser=$DB->GetRs("user","*","where User_ID=".$_SESSION[$UsersID."User_ID"]);

$num0 = $num1 = $num2 = $num3 = 0;
$r = $DB->GetRs("user_order","count(*) as num","where Users_ID='".$UsersID."' and User_ID=".$_SESSION[$UsersID."User_ID"]." and Order_Type='pifa' and Order_Status=0");
$num0 = $r["num"];
$r = $DB->GetRs("user_order","count(*) as num","where Users_ID='".$UsersID."' and User_ID=".$_SESSION[$UsersID."User_ID"]." and Order_Type='pifa' and Order_Status=1");
$num1 = $r["num"];
$r = $DB->GetRs("user_order","count(*) as num","where Users_ID='".$UsersID."' and User_ID=".$_SESSION[$UsersID."User_ID"]." and Order_Type='pifa' and Order_Status=2");
$num2 = $r["num"];
$r = $DB->GetRs("user_order","count(*) as num","where Users_ID='".$UsersID."' and User_ID=".$_SESSION[$UsersID."User_ID"]." and Order_Type='pifa' and Order_Status=3");
$num3 = $r["num"];
$r = $DB->GetRs("user_order","count(*) as num","where Users_ID='".$UsersID."' and User_ID=".$_SESSION[$UsersID."User_ID"]." and Order_Type='pifa' and Order_Status=4");
$num4 = $r["num"];
$rsConfig1=$DB->GetRs("user_config","*","where Users_ID='".$UsersID."'");
$LevelName = '普通会员';
if(!empty($rsConfig1["UserLevel"])){
	$level_arr = json_decode($rsConfig1["UserLevel"],true);
	if(!empty($level_arr[$rsUser["User_Level"]])){
		$LevelName = $level_arr[$rsUser["User_Level"]]["Name"];
	}
}

$base_url = base_url();
$pifa_url = $base_url.'api/'.$UsersID.'/pifa/';

$show_support = true;
?>
<!DOCTYPE html>
<html lang="zh-cn">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>个人中心</title>
	<link href="/static/css/bootstrap.css" rel="stylesheet">
	<link rel="stylesheet" href="./static/css/font-awesome.css">
	<link href="/static/api/distribute/css/style.css" rel="stylesheet">
	<link href="/static/api/shop/skin/default/css/member.css?t=<?php echo time();?>" rel="stylesheet">
	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
	<script src="/static/js/jquery-1.11.1.min.js"></script>
	<script type='text/javascript' src='/static/api/js/global.js'></script>
	<script type='text/javascript' src='/static/api/pifa/js/js.js'></script>
	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
      <script src="http://cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="http://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
	</head>
	<body>
<div class="wrap">
		<div class="container">
		<h4 class="row page-title">个人中心</h4>
	</div>
		<div class="contaienr">
		<div id="member_header">
				<div class="header_r">会员级别: <font style="font-weight:bold">
					<?=$LevelName?>
					</font> </div>
				<div class="header_l"> <span class="img"><img src="<?=$rsUser['User_HeadImg']?>"></span> <span class="nickname">
					<?php 
		if(strlen($rsUser['User_NickName']) >0){
			echo $rsUser['User_NickName'];	
		}else{
			echo '暂无';
		}
	?>
					</span>
				<div class="clearfix"></div>
			</div>
				<div class="clearfix"></div>
			</div>
	</div>
		<div id="member_orders"> <a href="/api/<?=$UsersID?>/pifa/member/status/0/"><font style="font-size:16px; font-weight:bold">
		<?=$num0?>
		</font><br>
			待确认</a> <a href="/api/<?=$UsersID?>/pifa/member/status/1/"><font style="font-size:16px; font-weight:bold">
			<?=$num1?>
			</font><br>
			待付款</a> <a href="/api/<?=$UsersID?>/pifa/member/status/2/"><font style="font-size:16px; font-weight:bold">
			<?=$num2?>
			</font><br>
			已付款</a> <a href="/api/<?=$UsersID?>/pifa/member/status/3/"><font style="font-size:16px; font-weight:bold">
			<?=$num3?>
			</font><br>
			已发货</a> <a href="/api/<?=$UsersID?>/pifa/member/status/4/"><font style="font-size:16px; font-weight:bold">
			<?=$num4?>
			</font><br>
			已完成</a>
		<div class="clearfix"></div>
	</div>
		<div class="list_item">
		<div class="dline"></div>
		<a style="display:none;" href="/api/<?=$UsersID?>/user/integral/" class="item item_0"><span class="ico"></span>我的积分：
			<?=$rsUser['User_Integral']?>
			分
			<?php if($rsUser['User_UseLessIntegral']>0): ?>
			&nbsp;&nbsp;不可用(
			<?=$rsUser['User_UseLessIntegral']?>
			分)
			<?php endif; ?>
			<span class="jt"></span></a> <a href="/api/<?=$UsersID?>/user/money/" class="item item_1"><span class="ico"></span> 我的余额：
			<?=$rsUser['User_Money']?>
			元<span class="jt"></span></a> <a style="display:none;" href="/api/<?=$UsersID?>/user/coupon/1/" class="item item_2"><span class="ico"></span>我的优惠券<span class="jt"></span></a> <a style="display:none;" href="/api/<?=$UsersID?>/user/gift/1/" class="item item_5"><span class="ico"></span>兑换礼品<span class="jt"></span></a> <a href="/api/<?=$UsersID?>/user/my/address/" class="item item_3"><span class="ico"></span>收货地址管理<span class="jt"></span></a> <a href="/api/<?=$UsersID?>/pifa/member/favourite/" class="item item_6"><span class="ico"></span>我的收藏夹<span class="jt"></span></a> 
			<a href="/api/<?=$UsersID?>/pifa/member/backup/status/0/" class="item item_7"><span class="ico"></span>我的退货单<span class="jt"></span></a>
		<?php if($rsUser['Is_Distribute'] == 1):?>
		<?php endif;?>
	</div>
	</div>
<?php require_once('../footer.php');?>