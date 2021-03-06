<?php
require_once('../global.php');
$Biz_ID=$_SESSION["BIZ_ID"];
$BizInfo = $DB->GetRS('biz_apply','*','WHERE Biz_ID = '.$Biz_ID); 

$baseinfo = json_decode($BizInfo['baseinfo'],true);
$authinfo = json_decode($BizInfo['authinfo'],true);
$accountinfo = json_decode($BizInfo['accountinfo'],true);
   
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title></title>


<link rel="stylesheet" href="/static/biz/css/bootstrap.min.css" type="text/css">
<link rel="stylesheet" href="/static/biz/css/style.min.css" type="text/css">
<link rel="stylesheet" href="/static/biz/css/agreement.min.css" type="text/css">
<style type="text/css">
#bizs .search{padding:10px; background:#f7f7f7; border:1px solid #ddd; margin-bottom:8px; font-size:12px;}
#bizs .search *{font-size:12px;}
#bizs .search .search_btn{background:#1584D5; color:white; border:none; height:22px; line-height:22px; width:50px;}
</style>
 <link href='/static/member/css/audit.css' rel='stylesheet' type='text/css' />
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<section class="vbox">
    <header class="header bg-white b-b b-light">
        <p>查看审核资料</p>
    </header>
    <div class="panel panel-default">
   
            <div class="panel-body">    
        
    
<div id="iframe_page">
  <div class="iframe_content"> 
     <div class="w">
	<div class="main_xx">
     
        
        <div class="group ">
                <span>认证类型：</span><span><?php if(!empty($BizInfo['authtype'])){
                    if($BizInfo['authtype']==1){echo '企业认证';}elseif($BizInfo['authtype']==2){echo'个人认证';}
                    }?></span>
        </div>
<?php if($BizInfo['authtype']==1){?>
        <div class="group ">
        	<span>公司名称：</span><span><?php echo !empty($baseinfo['company_name'])?$baseinfo['company_name']:''?></span>
        </div>
        <div class="group ">
        	<span>公司主体：</span><span>
                    <?php if(!empty($baseinfo['main_type'])){
                    if($baseinfo['main_type']==1){echo '大陆企业';}elseif($baseinfo['main_type']==2){echo'境外企业';}elseif($baseinfo['main_type']==3){echo'保税区';}
                    }?></span>
                   
        </div>
        <div class="group ">
        	<span>公司固话：</span><span><?php echo !empty($baseinfo['tel'][0])?$baseinfo['tel'][0]:'';echo !empty($baseinfo['tel'][1])?$baseinfo['tel'][1]:'';?></span>
        </div>
        <div class="group ">
        	<span>企业所在地：</span><span><?php echo !empty($baseinfo['city'][0])?$baseinfo['city'][0]:'';echo !empty($baseinfo['city'][1])?$baseinfo['city'][1]:'';echo !empty($baseinfo['city'][2])?$baseinfo['city'][2]:'';echo !empty($baseinfo['address'])?$baseinfo['address']:'';?></span>
        </div>
<?php }?>
        <div class="group ">
        	<span>主营商品：</span><span><?php echo !empty($baseinfo['goods'])?$baseinfo['goods']:''?></span>
        </div>
        <div class="group ">
        	<span>联系人：</span><span><?php echo !empty($baseinfo['contacts'])?$baseinfo['contacts']:''?></span>
        </div>
        <div class="group ">
        	<span>手机：</span><span><?php echo !empty($baseinfo['mobile'])?$baseinfo['mobile']:''?></span>
        </div>
        <div class="group ">
        	<span>邮箱地址：</span><span><?php echo !empty($baseinfo['email'])?$baseinfo['email']:''?></span>
        </div>
         
    </div>
     
    <div class="main_xx">
    	<p>资质信息</p>
<?php if($BizInfo['authtype']==1){?>        
        <div class="group ">
        	<span>企业类型：</span><span>
                <?php if(!empty($authinfo['company_type'])){
                    if($authinfo['company_type']==1){echo '有限责任公司';}elseif($authinfo['company_type']==2){echo'农民专业合作社';}elseif($authinfo['company_type']==3){echo'中外合资企业';}elseif($authinfo['company_type']==4){echo'外国或港澳台地区独资企业';}
                    }?></span>
        </div>
        <div class="group ">
        	<span>企业住所：</span><span><?php echo !empty($authinfo['compay_add'])?$authinfo['compay_add']:''?></span>
        </div>
        <div class="group ">
        	<span>注册资金：</span><span><?php echo !empty($authinfo['compay_reg_money'])?$authinfo['compay_reg_money']:''?></span>
        </div>
        <div class="group ">
        	<span>营业执照注册号或统一社会信用代码：</span><span><?php echo !empty($authinfo['compay_license'])?$authinfo['compay_license']:''?></span>
        </div>
        <div class="group ">
        	<span>法人：</span><span><?php echo !empty($authinfo['compay_user'])?$authinfo['compay_user']:''?></span>
        </div>
         
        <div class="group ">
            <span class="l">法人身份证扫描件：</span><span><?php if(!empty($authinfo['compay_shenfenimg'])){?><a href='<?=$authinfo['compay_shenfenimg']?>' target="_blank"><img src="<?php echo $authinfo['compay_shenfenimg']?>"></a><?php } ?></span>
        </div>
        <div class="group ">
        	<span class="l">营业执照影印件：</span><span><?php if(!empty($authinfo['compay_licenseimg'])){?><a href='<?=$authinfo['compay_licenseimg']?>' target="_blank"><img src="<?php echo $authinfo['compay_licenseimg']?>"></a><?php } ?></span>
        </div>
        <div class="group ">
        	<span class="l">税务登记证扫描件：</span><span><?php if(!empty($authinfo['compay_shuiwuimg'])){?><a href='<?=$authinfo['compay_shuiwuimg']?>' target="_blank"><img src="<?php echo $authinfo['compay_shuiwuimg']?>"></a><?php } ?></span>
        </div>
<?php }else{?>        
        <div class="group ">
        	<span>真实姓名：</span><span><?php echo !empty($authinfo['per_realname'])?$authinfo['per_realname']:''?></span>
        </div>
        <div class="group ">
        	<span>身份证号码：</span><span><?php echo !empty($authinfo['per_shenfenid'])?$authinfo['per_shenfenid']:''?></span>
        </div>
         
        <div class="group ">
            <span class="l">身份证扫描件：</span><span>
                <?php if(!empty($authinfo['per_shenfenimg'])){?><a href='<?=$authinfo['per_shenfenimg']?>' target="_blank"><img src="<?php echo !empty($authinfo['per_shenfenimg'])?$authinfo['per_shenfenimg']:''?>"></a><?php } ?></span>
        </div>
<?php } ?>        
    </div>
    <div class="main_xx">
    	<p>账户信息</p>
        <div class="group ">
        	<span>提现方式：</span><span>
                <?php if(!empty($accountinfo['withdraw_type'])){
                    if($accountinfo['withdraw_type']==1){echo '银行卡';}elseif($accountinfo['withdraw_type']==2){echo'支付宝';}
                    }?></span>
        </div>
<?php if($accountinfo['withdraw_type']==1){?>         
        <div class="group ">
        	<span>开户城市：</span><span><?php echo !empty($accountinfo['blan_city'])?$accountinfo['blan_city']:''?></span>
        </div>
        <div class="group ">
        	<span>开户银行：</span><span><?php echo !empty($accountinfo['blan_name'])?$accountinfo['blan_name']:''?></span>
        </div>
        <div class="group ">
        	<span>开户姓名：</span><span><?php echo !empty($accountinfo['blan_realname'])?$accountinfo['blan_realname']:''?></span>
        </div>
        <div class="group ">
        	<span>银行卡号：</span><span><?php echo !empty($accountinfo['blan_card'])?$accountinfo['blan_card']:''?></span>
        </div>
<?php }else{?>          
        <div class="group ">
        	<span>支付宝账号：</span><span><?php echo !empty($accountinfo['alipay_account'])?$accountinfo['alipay_account']:''?></span>
        </div>
        <div class="group ">
        	<span>姓名：</span><span><?php echo !empty($accountinfo['alipay_realname'])?$accountinfo['alipay_realname']:''?></span>
        </div>
<?php } ?>           
    </div>
    <div style="margin-bottom:30px;">
        <!--<button class="btn_x1">驳回 </button>-->
        <a style="cursor:hand;" href="index.php"><button class="btn_xx" href="javascript:void(0)">返回</button></a>
       
    </div>
</div> 
    
  </div>
</div>
        
        
         </div>
        </div>
</section>        
    <script>
    $("#btn").click(function(){
        var apply_id = $("#btn").attr('item');
        $.get('?',{apply_id:apply_id,action:'read'},function(data){
            alert(data.info);
            if (data.status == 1) {
                window.location = 'apply.php';
            }
        }, 'json')
    })
    </script>    
</body>
</html>