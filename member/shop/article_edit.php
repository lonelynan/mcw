<?php


if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}

$DB->showErr=false;

if(!empty($_GET['ID'])){
	$id = $_GET['ID'];
}else{
	echo '没有文章ID';
	exit();
}

$Article=$DB->GetRs("shop_articles","*","where Article_ID=".$id);
if(empty($Article)){
	echo '<script language="javascript">alert("此文章已经不存在！");window.location="javascript:history.back()";</script>';
	exit();
}

if($_POST){
	$_POST["content"] = str_replace('"','&quot;',$_POST["content"]);
	$_POST["content"] = str_replace("'","&quot;",$_POST["content"]);
	$_POST["content"] = str_replace('>','&gt;',$_POST["content"]);
	$_POST["content"] = str_replace('<','&lt;',$_POST["content"]);
	
	$Data=array(
		"Users_ID"=>$_SESSION["Users_ID"],
		"Article_Title"=>$_POST["title"],
		"Category_ID"=>empty($_POST["CategoryID"]) ? 0 : $_POST["CategoryID"],
		"Article_Content"=>$_POST["content"],
		"Article_Status"=>$_POST["status"],
		"Article_Editor"=>$_POST["Editor"],
		"Article_CreateTime"=>time()
	);
	
	
	$flag=$DB->Set("shop_articles",$Data,"where Article_ID=".$id);
	if($flag){
		echo '<script language="javascript">alert("修改成功！");window.open("articles.php","_self");</script>';
		exit();
	}else{
		echo '<script language="javascript">alert("修改失败！");window.location="javascript:history.back()";</script>';
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
            uploadJson : '/member/upload_json.php',
            fileManagerJson : '/member/file_manager_json.php',
            allowFileManager : true
        });
    });
</script>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
  <div class="iframe_content">
	<div class="r_nav">
	 <ul>
        <li class="cur"><a href="articles.php">文章管理</a></li>
		<li><a href="articles_category.php">分类管理</a></li>
      </ul>
	</div>
    <div class="r_con_wrap">
		<form class="r_con_form" method="post" action="?ID=<?php echo $id;?>">
            <div class="rows">
                <label>标题</label>
                <span class="input"><input type="text" name="title" value="<?php echo $Article["Article_Title"];?>" size="30" class="form_input" /></span>
                <div class="clear"></div>
            </div>
			
			<div class="rows">
                <label>发布者</label>
                <span class="input"><input type="text" name="Editor" value="<?php echo $Article["Article_Editor"];?>" size="30" class="form_input" /></span>
                <div class="clear"></div>
            </div>
            
            <div class="rows">
                <label>所属分类</label>
                <span class="input">
                 <select name="CategoryID" notnull>
                <?php
                	$DB->get("shop_articles_category","*","where Users_ID='".$_SESSION["Users_ID"]."' order by Category_Index asc");
					while($rsCategory=$DB->fetch_assoc()){
				?>
                  <option value="<?php echo $rsCategory["Category_ID"];?>"<?php echo $Article['Category_ID']==$rsCategory["Category_ID"] ? ' selected' : ''?>><?php echo $rsCategory["Category_Name"];?></option>
                <?php }?>
                 </select>
                </span>
                <div class="clear"></div>
            </div>            
            
            <div class="rows">
                <label>详细内容</label>
                <span class="input">
                    <textarea name="content" style="width:100%;height:400px;visibility:hidden;"><?php echo $Article["Article_Content"];?></textarea>
                </span>
                <div class="clear"></div>
            </div>
            
            <div class="rows">
                <label>状态</label>
                <span class="input">
                    <label><input name="status" type="radio" value="1" <?php if($Article['Article_Status'] == 1){ echo 'checked';}?>>显示</label>
                    <label><input name="status" type="radio" value="0" <?php if($Article['Article_Status'] == 0){ echo 'checked';}?> >不显示</label>
                </span>
                <div class="clear"></div>
            </div>
      
            <div class="rows">
                <label></label>
                <span class="input"><input type="submit" name="Submit" value="确定" class="submit">
                  <input type="reset" value="重置"></span>
                <div class="clear"></div>
            </div>
        </form>
    </div>
  </div>
</div>
</body>
</html>