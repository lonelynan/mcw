<?php
require_once('global.php');

$all_distribute_count = $self_distribute_count = $posterity_distribute_count = 0;
										           
//计算全部个数
//$builder = Dis_Account_Record::Multiwhere(array('Users_ID'=>$UsersID,'User_ID'=>$_SESSION[$UsersID.'User_ID']));
//$all_distribute_count = $builder->count();

//自销次数
$builder = Dis_Account_Record::Multiwhere(array('Users_ID'=>$UsersID,'User_ID'=>$_SESSION[$UsersID.'User_ID']));
$self_distribute_count = $builder->whereHas('DisRecord',function($query) use($User_ID){
									$query->where('Buyer_ID','=',$User_ID);
								})->count();
//下级销售次数
$builder = Dis_Account_Record::Multiwhere(array('Users_ID'=>$UsersID,'User_ID'=>$_SESSION[$UsersID.'User_ID']));
$posterity_distribute_count = $builder->whereHas('DisRecord',function($query) use($User_ID){
									$query->where('Buyer_ID','!=',$User_ID);
								})->count();

$all_distribute_count = $self_distribute_count + $posterity_distribute_count;

if(!empty($dis_account_record)){
	$dis_account_record_list = $dis_account_record->toArray();								 
	
	foreach($dis_account_record_list as $key=>$account_record){
		$account_record['Owner_ID'] = $account_record['dis_record']['Owner_ID'];
		unset($account_record['dis_record']);
		$dis_account_record_list[$key]	= 	$account_record;
	}
	
}
//购买级别数组

//获取记录
$builder = Dis_Account_Record::Multiwhere(array('Users_ID'=>$UsersID,'User_ID'=>$_SESSION[$UsersID.'User_ID']));

$url_param = array('UsersID'=>$UsersID);
if($_GET['filter'] != 'all'){
	
	if($_GET['filter'] == 'self'){
		$builder->whereHas('DisRecord',function($query) use($User_ID){
									$query->where('Buyer_ID','=',$User_ID);
								});
		//$DB->Get();
		$url_param['filter'] = 'self';	
	}elseif($_GET['filter'] == 'down'){
		$builder->whereHas('DisRecord',function($query) use($User_ID){
									$query->where('Buyer_ID','!=',$User_ID);
								});		
		$url_param['filter'] = 'down';			
	}
}else{
	$url_param['filter'] = 'all';
}

$builder->orderBy('Record_CreateTime','desc');

$Records_paginate_obj = $builder->simplePaginate(10);
$Records_paginate_obj->setPath(base_url('api/distribute/detaillist.php'));


$Records_paginate_obj->appends($url_param);
$distribute_record  = $Records_paginate_obj->toArray()['data'];

$page_link = $Records_paginate_obj->render();

$header_title = '分销账户明细';
require_once('header.php');
$LevelNames = [
	'1' => '一',
	'2' => '二',
	'3' => '三',
	'4' => '四',
	'5' => '五',
	'6' => '六',
	'7' => '七',
	'8' => '八',
	
	'9' => '九'
];
$recode_stutus = array('已生成', '已付款', '已完成');
?>

<body>
<link href="/static/api/distribute/css/detaillist.css" rel="stylesheet">

<header class="bar bar-nav">
  <a href="javascript:history.back()" class="fa fa-2x fa-chevron-left grey pull-left"></a>
  <a href="<?=$distribute_url.'?love';?>" class="fa fa-2x fa-sitemap grey pull-right"></a>
  <h1 class="title"><?php echo empty($rsConfig['DisSwitch'])?'财富账户明细':'账户明细'?></h1>
  
</header>
<!--<div style="height:50px; line-height:50px; width:100%; background:#FFF; border-bottom:1px #dfdfdf solid">
	<a href="<?php echo $distribute_url;?>detaillist/all/" style="display:block; width:50%; height:50px; line-height:50px; text-align:center; font-size:16px; float:left; box-sizing:border-box; border-right:1px #dfdfdf solid; color:#F60">商城明细</a>
	<a href="<?php echo $distribute_url;?>buylevellist/all/" style="display:block; width:50%; height:50px; line-height:50px; text-align:center; font-size:16px; float:left">购买级别</a>
	<div style="clear:booth"></div>
</div>-->
<div class="wrap">
	<div class="container">    	
        <div class="row">
        <!--<ul id="distribute-brief-info">
         <li class="item"><a href="<?php echo $distribute_url;?>detaillist/all/"><span class="red bold">&nbsp;<?=$all_distribute_count?></span><br/>全部</a></li>
         <li class="item"><a href="<?php echo $distribute_url;?>detaillist/self/"><span class="red bold">&nbsp;<?=$self_distribute_count?></span><br/><?php echo empty($rsConfig['DisSwitch'])?'自己购买':'自销'?></a></li>
         <li class="item"><a href="<?php echo $distribute_url;?>detaillist/down/"><span class="red bold">&nbsp;<?=$posterity_distribute_count?></span><br/><?php echo empty($rsConfig['DisSwitch'])?'朋友购买':'下级分销'?></a></li>
         <li class="clearfix"></li>
      </ul>
      -->
      <ul class="list-group" id="record-panel">
    
      	<?php foreach($distribute_record as $key=>$item) { ?>
        <li class="list-group-item">
        	<p class="record-description">
				<?php 
				$rsUser = User::where("User_ID", $item['User_ID'])->first(['User_NickName','User_Mobile'])->toArray();
				$rsdisrecorde = Dis_Record::where(array('Record_ID'=>$item['Ds_Record_ID']))->first(['Order_ID', 'Record_CreateTime']);
				
				$names = $item['level'] == 110 ? '' : $LevelNames[$item['level']];
				$names = $names . '级' . ($rsUser['User_NickName'] ? $rsUser['User_NickName'] : $rsUser['User_Mobile']) . '&nbsp;&nbsp;购买商品';
				$item['Record_Description'] = str_replace('下属分销商分销',$names,$item['Record_Description']);
				$item['Record_Description'] = str_replace('分销商级别','级别',$item['Record_Description']);
				$item['Record_Description'] = str_replace('分销级别','级别',$item['Record_Description']); 
				$item['Record_Description'] = str_replace('自己销售自己购买',' 自销 ',$item['Record_Description']); 
				?>
				<?=$item['Record_Description']?>&nbsp;&nbsp;<span class="red">&yen;<?=round_pad_zero($item['Record_Money'],2)?></span>
			
                     
            </p>
			<?php if (!empty($rsdisrecorde)) { ?>
			<p>订单号：<?php echo date("Ymd",$rsdisrecorde["Record_CreateTime"]).$rsdisrecorde["Order_ID"] ?></p>
			<?php } ?>
            <p class="record-description" ><?=ldate($item['Record_CreateTime'])?>&nbsp;&nbsp;&nbsp;&nbsp;</p>
            <p >
             <?=$recode_stutus[$item['Record_Status']]?>
             </p>
             
              
        </li>
        <?php } ?>
      </ul>   
      <?php
      	echo $page_link;
      ?>
        </div>

    </div>
    
  	
  
    
</div>

 
<?php require_once('../shop/skin/distribute_footer.php');?> 
 
 
</body>
</html>
