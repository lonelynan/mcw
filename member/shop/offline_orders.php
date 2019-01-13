<?php 
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/lib_products.php');

if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}
if(isset($_GET["cliid"])){	
	$subid = $_GET["cliid"];
}
$_GET = daddslashes($_GET,1);

//获取可用的支付方式列表
$Pay_List = get_enabled_pays($DB,$_SESSION["Users_ID"]);

$condition = "where Users_ID='".$_SESSION["Users_ID"]."' and (Order_Type='offline' or Order_Type='offline_st' or Order_Type='offline_qrcode')";
if(isset($_GET["search"])){
	if($_GET["search"]==1){
		if(!empty($_GET["Keyword"])){
			$condition .= " and `".$_GET["Fields"]."` like '%".$_GET["Keyword"]."%'";
		}
		if(!empty($_GET["OrderNo"])){
			$OrderID =  substr($_GET["OrderNo"],8);
			$OrderID =  empty($OrderID) ? 0 : intval($OrderID);
			$condition .= " and Order_ID=".$OrderID;
		}
		if($_GET['BizID']>0){
			$condition .= " and Biz_ID=".$_GET['BizID'];
		}		
		if(!empty($_GET["AccTime_S"])){
			$condition .= " and Order_CreateTime>=".(strtotime($_GET["AccTime_S"])==-1 || strtotime($_GET["AccTime_S"])==false?0:strtotime($_GET["AccTime_S"]));
		}
		if(!empty($_GET["AccTime_E"])){
			$condition .= " and Order_CreateTime<=".(strtotime($_GET["AccTime_E"])==-1 || strtotime($_GET["AccTime_E"])==false?0:strtotime($_GET["AccTime_E"]));
		}
	}
}

$condition .= " order by Order_CreateTime desc";

$bizs = array();
$DB->get("biz","Biz_ID,Biz_Name","where Users_ID='".$_SESSION["Users_ID"]."'");
while($r = $DB->fetch_assoc()){
	$bizs[$r["Biz_ID"]] = $r;
}
$order_type_arr = array('offline_st' => '在线买单', 'offline_qrcode' => '线下扫码');
$jcurid = 8;
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title></title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
	<script type='text/javascript' src='/static/js/jquery.datetimepicker.js'></script>
<link href='/static/css/jquery.datetimepicker.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/shop.js?t=1'></script>
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/shop/product_menubar.php');?>
    <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script> 
    <script language="javascript">
$(document).ready(shop_obj.orders_init);
</script>
<script>
$(function(){
	  $("#checkall").click(function(){
	     if($(this).prop("checked")==true){
	      $("input[name='OrderID[]']").attr("checked","checked");
	      }else{
	        $("input[name='OrderID[]']").removeAttr("checked");
	      }
	  });
	  $("#print").click(function(){
	      var ids = new Array;
	      $("input[name='OrderID[]']:checked").each(function(){
	          ids.push($(this).val());
	      });
	      if(ids.length<1)
	      {
	          alert("至少选择1个");
	          return false;
	      }
	      var idlist = "";
	      for(var i=0;i<ids.length;i++)
	      {
	          if(i==ids.length-1){
	              idlist +=ids[i];
	          }else{
	              idlist +=ids[i]+",";
	          }
	          
	      }
	      location.href = "/member/shop/order_print.php?OrderID="+idlist;
	  });
	});

function CheckAll(form1){
	for(var i=0;i<form1.elements.length;i++){
		var e = form1.elements[i];
		if(e.name != 'chkall'){
			e.checked = form1.chkall.checked;
		}
	}
}
function SelectThis(index){
	if(typeof(form1.ID[index-1])=='undefined'){
		form1.ID.checked=!form1.ID.checked;
	}else{
		form1.ID[index-1].checked=!form1.ID[index-1].checked;
	}
}
</script>
    <div id="orders" class="r_con_wrap">
      <form class="search" id="search_form" method="get" action="?">
        <select name="Fields">			
			<option value='Address_Mobile'>购买手机</option>			
		</select>
        <input type="text" name="Keyword" value="" class="form_input" size="15" />&nbsp;
		订单号：<input type="text" name="OrderNo" value="" class="form_input" size="15" />&nbsp;
        商家
        <select name='BizID'>
          <option value='0'>--请选择--</option>
          <?php
          	foreach($bizs as $value){
		  		echo '<option value="'.$value["Biz_ID"].'">'.$value["Biz_Name"].'</option>';
		  	}
		  ?>
        </select>&nbsp;        
        时间：
        <input type="text" class="input" name="AccTime_S" value="" maxlength="20" />
        -
        <input type="text" class="input" name="AccTime_E" value="" maxlength="20" />
		<input type="hidden" value="1" name="search" />
        <input type="submit" class="search_btn" value="搜索" />
        <input type="button" class="output_btn" value="导出" />
      </form>	  
      <table border="0" cellpadding="5" cellspacing="0" class="r_con_table" id="order_list">
        <thead>
          <tr>
            <td width="6%" nowrap="nowrap"><input type="checkbox" id="checkall"/></td>
            <td width="5%" nowrap="nowrap">序号</td>
            <td width="15%" nowrap="nowrap">订单号</td>
            <td width="8%" nowrap="nowrap">下单类型</td>
            <td width="15" nowrap="nowrap">商家</td>
            <td width="5%" nowrap="nowrap">分销商</td>            
            <td width="12%" nowrap="nowrap">金额</td>
            <td width="9%" nowrap="nowrap">配送方式</td>
            <td width="9%" nowrap="nowrap">订单状态</td>
            <td width="12%" nowrap="nowrap">时间</td>
            <td width="10%" nowrap="nowrap" class="last">操作</td>
          </tr>
        </thead>
        <tbody>
          <?php		  
		  $DB->getPage("user_order","*",$condition,10);
$Order_Status=array("待确认","待付款","已付款","已发货","已完成","申请退款中");
/*获取订单列表牵扯到的分销商*/
while($rsOrder=$DB->fetch_assoc()){
	//获取所有分销商列表
$ds_list = Dis_Account::with('User')
			->where(array('Users_ID' => $_SESSION["Users_ID"],'User_ID' => $rsOrder['User_ID']))
			->get(array('User_ID'))
			->toArray();
			
$ds_list_dropdown = array();
foreach($ds_list as $key=>$item){
	if(!empty($item['user'])){
		$ds_list_dropdown[$item['User_ID']] = $item['user']['User_NickName'];
	}
}
$Shipping=json_decode(htmlspecialchars_decode($rsOrder["Order_Shipping"]), true);
?>


          <tr class="<?php echo empty($rsOrder["Order_IsRead"])?"is_not_read":"" ?>" IsRead="<?php echo $rsOrder["Order_IsRead"] ?>" OrderId="<?php echo $rsOrder["Order_ID"] ?>">
            <td nowrap="nowrap"><input type="checkbox" name="OrderID[]" value="<?php echo $rsOrder["Order_ID"];?>" /></td>
            <td nowrap="nowrap"><?php echo $rsOrder["Order_ID"] ?></td>
         
            <td nowrap="nowrap"><?php echo date("Ymd",$rsOrder["Order_CreateTime"]).$rsOrder["Order_ID"] ?></td>
            <td nowrap="nowrap"><?=$order_type_arr[$rsOrder["Order_Type"]]?></td>
            <td nowrap="nowrap"><?php echo empty($bizs[$rsOrder["Biz_ID"]]) ? '' : $bizs[$rsOrder["Biz_ID"]]["Biz_Name"] ?></td>
            <td nowrap="nowrap">
			<?php
          
			if($rsOrder["Owner_ID"] == 0 ){
				echo '无';
			}else{
				
				if(!empty($ds_list_dropdown[$rsOrder["Owner_ID"]])){
					echo $ds_list_dropdown[$rsOrder["Owner_ID"]];
				}else{
					echo '无昵称';
				}
				
				
			}	
			
			?></td>            
             <td nowrap="nowrap">￥<?php echo $rsOrder["Order_TotalPrice"] ?><?php echo $rsOrder["Back_Amount"]>0 ? '<br /><font style="text-decoration:line-through; color:#999">&nbsp;退款金额：￥'.$rsOrder["Back_Amount"].'&nbsp;</font>' : "";?></td> 
            <td nowrap="nowrap"><?php		
				if(empty($Shipping)){
					echo "免运费";
				}else{
					if(isset($Shipping["Express"])){
						echo $Shipping["Express"];
					}else{
						echo '无配送信息';
					}
				}
			?></td>
			
	
            <td nowrap="nowrap"><?php if(($rsOrder["Order_TotalPrice"]<=$rsOrder["Back_Amount"] || $rsOrder['Order_Status']==4) && $rsOrder['Is_Backup']==1){?><font style="color:#999; text-decoration:line-through">已退款</font><?php }else{?><?php echo $Order_Status[$rsOrder["Order_Status"]] ?><?php }?></td>
            <td nowrap="nowrap"><?php echo date("Y-m-d H:i:s",$rsOrder["Order_CreateTime"]) ?></td>
            <td class="last" nowrap="nowrap"><a href="<?php echo $rsOrder["Order_IsVirtual"]==1 ? 'virtual_' : '';?>orders_view.php?OrderID=<?php echo $rsOrder["Order_ID"] ?>"><img src="/static/member/images/ico/view.gif" align="absmiddle" alt="查看" /></a></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
      <div style="height:10px; width:100%;"></div>
       <label style="display:block; width:120px; border-radius:5px; height:32px; line-height:30px; background:#3AA0EB; color:#FFF; text-align:center; font-size:12px; cursor:pointer" id="print">打印订单</label>       
      <div class="blank20"></div>
      <?php $DB->showPage(); ?>
    </div>
  </div>
</div>
</body>
</html>