<?php
	if(!empty($_POST)){
		$counts = $DB->GetRs("shop_products","count(Products_ID) as count",$condition);
		$num = 10;//每页记录数
		$p = !empty($_POST['p'])?intval(trim($_POST['p'])):0;
		$total = $counts['count'];//数据记录总数
		$totalpage = ceil($total/$num);//总计页数
		$limitpage = ($p-1)*$num;//每次查询取记录
	    $condition .= "  limit $limitpage,$num";
		$productList = $DB->get("shop_products","*",$condition);	
		$productList = $DB->toArray($productList);
		foreach($productList as $key => $val){
			$productList[$key]['link'] = $shop_url.'products/'.$val["Products_ID"].'/';
			$productList[$key]['JSON'] = json_decode($val['Products_JSON'],true);
		}
		if(count($productList)>0){
			$data = array(
			    'list' => $productList,
				'totalpage' => $totalpage,
			);
		}else{
			$data = array(//没有数据可加载
			    'list' => '',
				'totalpage' => $totalpage,
			);
		}
		echo json_encode($data);
		exit;
	}
?>
<?php require_once('top.php'); ?>
<body>
<style type="text/css" media="all">
    /**
	 *
	 * 加载更多样式
	 *
	 */
	.pullUp {
		background:#fff;
		height:40px;
		line-height:40px;
		padding:5px 10px;
		border-bottom:1px solid #ccc;
		font-weight:bold;
		font-size:14px;
		color:#888;
		text-align:center;
		overflow:hidden;
	}
	.pullUp .pullUpIcon  {
		padding:10px 20px;
		background:url(/static/js/plugin/lazyload/pull-icon@2x.png) 0 0 no-repeat;
		-webkit-background-size:40px 80px; background-size:40px 80px;
		-webkit-transition-property:-webkit-transform;
		-webkit-transition-duration:250ms;	
	}
	.pullUp .pullUpIcon  {
		-webkit-transform:rotate(0deg) translateZ(0);
	}

	.pullUp.flip .pullUpIcon {
		-webkit-transform:rotate(0deg) translateZ(0);
	}

	.pullUp.loading .pullUpIcon {
		background-position:0 100%;
		-webkit-transform:rotate(0deg) translateZ(0);
		-webkit-transition-duration:0ms;

		-webkit-animation-name:loading;
		-webkit-animation-duration:2s;
		-webkit-animation-iteration-count:infinite;
		-webkit-animation-timing-function:linear;
	}

	@-webkit-keyframes loading {
		from { -webkit-transform:rotate(0deg) translateZ(0); }
		to { -webkit-transform:rotate(360deg) translateZ(0); }
	}
 </style>
<?php
ad($UsersID, 1, 1); //第一个数字参数代表广告位置：1顶部2底部；第二个数字参数代表广告位的编号，从后台查看
?>
<div id="shop_page_contents">
  <div id="cover_layer"></div>
  <link href='/static/api/shop/skin/default/css/products.css' rel='stylesheet' type='text/css' />
  <link href='/static/api/shop/skin/default/css/products_media.css' rel='stylesheet' type='text/css' />
  <link href='/static/api/shop/skin/1/products.css' rel='stylesheet' type='text/css' />
  <link href='/static/api/shop/skin/1/products_media.css' rel='stylesheet' type='text/css' />
  <div class="header_search">
     <div class="search_form">
  
     </div>
	 <div class="bottom_div0"></div>
	 <div class="bottom_div"></div>
    </div>
  <div id="products">
	<div class="list_sorts">
    	<?php
		require_once("order_filter.php");
		?>
    </div>
	<?php if($rsMore["More_ListTypeID"]==1){?>
	<div class="list-1"></div>
	<?php }?>
    <div class="pullUp get_more" listtype="<?php echo $rsMore["More_ListTypeID"];?>" page="1"> <span class="pullUpIcon"></span><span class="pullUpLabel">点击加载更多...</span> </div>
  </div>
</div>
<?php require_once($rsBiz["Skin_ID"]."/footer.php");?>
<?php include("yh_more_ajax.php");?>
</body>
</html>