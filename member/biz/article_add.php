<?php


if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}

$DB->showErr=false;


if($_POST){
        if (empty($_POST["title"]) || empty($_POST["content"])) {
            echo '<script language="javascript">alert("文章名称和内容必填");history.back();</script>';
            exit;
        }
	$_POST["content"] = str_replace('"','&quot;',$_POST["content"]);
	$_POST["content"] = str_replace("'","&quot;",$_POST["content"]);
	$_POST["content"] = str_replace('>','&gt;',$_POST["content"]);
	$_POST["content"] = str_replace('<','&lt;',$_POST["content"]);
	$Data=array(
		"Users_ID"=>$_SESSION["Users_ID"],
		"atricle_title"=>$_POST["title"],
		"category_id"=>empty($_POST["CategoryID"]) ? 0 : $_POST["CategoryID"],
		"atricle_content"=>$_POST["content"],
		"Article_Editor"=>$_POST["Editor"],
		"addtime"=>time()
	);
	
	
	$flag=$DB->Add("biz_article",$Data);
	if($flag){
		echo '<script language="javascript">alert("添加成功！");window.open("article_man.php","_self");</script>';
		exit();
	}else{
		echo '<script language="javascript">alert("添加失败！");window.location="javascript:history.back()";</script>';
		exit();
	}
}
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
<link rel="stylesheet" href="/third_party/kindeditor/themes/default/default.css" />
<script charset="utf-8" src="/third_party/kindeditor/kindeditor-min.js"></script>
<script charset="utf-8" src="/third_party/kindeditor/lang/zh_CN.js"></script>
<script>
    KindEditor.ready(function(K) {
        K.create('textarea[name="content"]', {
            themeType : 'simple',
			filterMode : false,
            uploadJson : '/third_party/kindeditor/php/upload_json.php',
            fileManagerJson : '/third_party/kindeditor/php/file_manager_json.php',
            allowFileManager : true
        });
    });
</script>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
  <div class="iframe_content">
	<div class="r_nav">
	  <ul>
        <li class="cur"><a href="article_man.php">文章管理</a></li>
		<li><a href="articlecate_man.php">分类管理</a></li>
      </ul>
	</div>
    <div class="r_con_wrap">
        <form class="r_con_form" method="post" action="?">
        	<div class="rows">
                <label>标题</label>
                <span class="input"><input type="text" name="title" value="" size="30" class="form_input" /></span>
                <div class="clear"></div>
            </div>
			
			<div class="rows">
                <label>发布者</label>
                <span class="input"><input type="text" name="Editor" value="管理员" size="30" class="form_input" /></span>
                <div class="clear"></div>
            </div>
            
            <div class="rows">
                <label>所属分类</label>
                <span class="input">
                 <select name="CategoryID" notnull>
                <?php
                	$DB->get("biz_article_cate","*","where Users_ID='".$_SESSION["Users_ID"]."' order by Category_Index asc");
                                    while($rsCategory=$DB->fetch_assoc()){
					//if($rsCategory["Category_Type"] == '列表'){
		?>
                                            <option value="<?php echo $rsCategory["id"];?>"><?php echo $rsCategory["category_name"];?></option>
                                 <?php //}?>
                            <?php }?>
                 </select>
                </span>
                <div class="clear"></div>
            </div>
            
            <div class="rows">
                <label>详细内容</label>
                <span class="input">
                    <textarea name="content" style="width:100%;height:400px;visibility:hidden;"></textarea>
                </span>
                <div class="clear"></div>
            </div>
            
             
            <div class="rows">
                <label></label>
                <span class="input">
                    <input type="submit" name="Submit" value="确定" class="submit btn_green btn_w_120">
                </span>
                <div class="clear"></div>
            </div>
        </form>
    </div>
  </div>
</div>
</body>
</html>