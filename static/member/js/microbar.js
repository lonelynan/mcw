/*
Powered by ly200.com		http://www.ly200.com
广州联雅网络科技有限公司		020-83226791
*/

var microbar_obj={
	config_init:function(){
		$('#config_form').submit(function(){
			if(global_obj.check_form($('*[notnull]'))){return false};
			$('#config_form .submit').attr('disabled', true);
			return true;
		});
	},
}