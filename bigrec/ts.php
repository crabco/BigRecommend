<?php
ini_set("display_errors", "off");
error_reporting(0);
?>
<html>
<title>测试页面 </title>
<meta charset="utf-8"/>
<head>
<!-- <script src="http://libs.baidu.com/jquery/2.1.1/jquery.min.js"></script> -->
<script src="js/?app_id=1&app_key=1234"></script>
</head>
<body>
<table width="400" border="0" cellpadding="5" cellspacing="1" bgcolor="#666666">
  <tr>
    <td width="75" bgcolor="#FFFFFF">自动推荐</td>
    <td width="302" bgcolor="#FFFFFF">
    <input type="button" name="button" id="button" value="重新推荐" onclick="reco()">
    <div id="reco"></div>
    </td>
  </tr>
  <tr>
    <td bgcolor="#FFFFFF">&nbsp;</td>
    <td bgcolor="#FFFFFF">&nbsp;</td>
  </tr>
</table>
<p>&nbsp;</p>
<form id="data_modify" method="post" action="./?act=data_modify" onsubmit="data_pus(1);">
  <table width="400" border="0" cellpadding="5" cellspacing="1" bgcolor="#666666">
  	<input type="hidden" name="app_id" value="1">
  	<input type="hidden" name="app_password" value="123456">
	<tr>
	  <td colspan="2" align="center" bgcolor="#FFFFFF">资料添加</td>
    </tr>
	<tr>
	  <td bgcolor="#FFFFFF">产品编号</td>
	  <td bgcolor="#FFFFFF"><input type="text" name="pro_no" style="width:90%" id="pro_no" value="<?php echo $_POST['pro_no']?>"></td>
    </tr>
	<tr>
		<td width="75" bgcolor="#FFFFFF">产品名称</td>
		<td width="302" bgcolor="#FFFFFF">
		  <label for="pro_name"></label>
		  <input type="text" name="pro_name" style="width:90%" id="pro_name" value="<?php echo $_POST['pro_name']?>">
	    </td>
	</tr>
	<tr>
	  <td bgcolor="#FFFFFF">封面图片</td>
	  <td bgcolor="#FFFFFF"><input type="text" style="width:90%" name="pro_cover" id="pro_cover" value="<?php echo $_POST['pro_cover']?>"></td>
  </tr>
	<tr>
	  <td bgcolor="#FFFFFF">标签分类</td>
	  <td bgcolor="#FFFFFF"><input type="text" style="width:90%" name="pro_tags" id="pro_tags" value="<?php echo $_POST['pro_tags']?>"></td>
    </tr>
	<tr>
	  <td bgcolor="#FFFFFF">是否显示</td>
	  <td bgcolor="#FFFFFF"><input name="pro_show" type="radio" id="pro_show" value="true">是 
        <input name="pro_show" type="radio" id="pro_show" value="false">否</td>
    </tr>
	<tr>
	  <td colspan="2" align="center" bgcolor="#F6F6F6">
	  <input type="button" name="button" id="button" value="提交" onclick="data_pus(1)">
	  <input type="button" name="button" id="button" value="批量添加10条" onclick="data_pus(10)">
	  </td>
    </tr>
</table>
</form>
<br>
<form id="user_form" method="post" action="./?act=user_modify" onsubmit="user_post()">
  <table width="400" border="0" cellpadding="5" cellspacing="1" bgcolor="#666666">
  	<input type="hidden" name="app_id" value="1">
  	<input type="hidden" name="app_password" value="123456">
	<tr>
	  <td colspan="2" align="center" bgcolor="#FFFFFF">用户添加</td>
    </tr>
	<tr>
	  <td bgcolor="#FFFFFF">用户编号</td>
	  <td bgcolor="#FFFFFF"><input type="text" name="user_no" style="width:90%" value="<?php echo $_POST['user_no']?>"></td>
    </tr>
	<tr>
		<td width="75" bgcolor="#FFFFFF">用户年龄</td>
		<td width="302" bgcolor="#FFFFFF">
		  <label for="pro_name"></label>
		  <input type="text" name="user_age" style="width:90%" value="<?php echo $_POST['user_age']?>">
	    </td>
	</tr>
	<tr>
	  <td bgcolor="#FFFFFF">手机号码</td>
	  <td bgcolor="#FFFFFF"><input type="text" style="width:90%" name="user_phone" value="<?php echo $_POST['user_phone']?>"></td>
  </tr>
	<tr>
	  <td bgcolor="#FFFFFF">性别</td>
	  <td bgcolor="#FFFFFF">
	    <input name="user_sex" type="radio" value="男">男
        <input name="user_sex" type="radio"  value="女">女
        </td>
    </tr>
	<tr>
	  <td colspan="2" align="center" bgcolor="#F6F6F6"><input type="button" name="button" id="button" value="提交" onclick="user_post()"></td>
    </tr>
</table>
</form>
<script language="javascript">
function reco(){
	$("#reco").html('');
	$.get("./?act=reco&app_id=1&app_key=123412&user_no=<?php echo $_GET['user_no']?>&size=<?php echo $_GET['size']?>&debug=1",function(Json){
		if( Json['val'].length<=0 )return;
		var ValTab = '';
		for(var i=0;i<Json['val'].length;i++){
			var Val	= Json['val'][i];
			ValTab += "<a href='javascript:' onclick=\"reco_up('"+Val['pro_no']+"','"+Val['pro_tags']+"')\">"+Val['pro_name']+"</a><br>";
		}
		
		ValTab += "<br>推荐方式:<br>";
		
		var Val	= Json['info']["choice"];
		for(var i=0;i<Val.length;i++){
			ValTab += Val[i] + "<br>";
		}
		$('#reco').html(ValTab);
	},'json');
}

function reco_up(ID,Tag){
	URL = "./?act=up&app_id=1&app_key=123412&user_no=<?php echo $_GET['user_no']?>&pro_no="+ID+"&pro_tags="+Tag;
	$.get(URL,function(Json){
		if( Json['status']==false ) alert(Json['error']);
	},'json');
}

var PushInt = 1;
var ValName = "";
var ValNo	= "";

function data_pus( Sum ){

	if( ValName=="" ){
		ValName = $("#pro_name").val();
		ValNo	= $("#pro_no").val();
	}
	
	for(i=0;i<Sum;i++){
		if( Sum>1 ){
			PushInt	= parseInt(PushInt)+1;
			$("#pro_name").val( ValName + PushInt )
			$("#pro_no").val( 	ValNo + PushInt );
		}
		
		$.ajax({
		    type: 'post',
		    url: './?act=data_modify',
		    data: $("#data_modify").serialize(),
		    success: function(Json) {
			    if( Json['status']==false ) alert(Json['error']);
		    }
		});
	}
	return false;
}

function user_post(){
	
	$.ajax({
	    type: 'post',
	    url: './?act=user_modify',
	    data: $("#user_form").serialize(),
	    success: function(Json) {
		    if( Json['status']==false ) alert(Json['error']);
	    }
	});
	
	return false;
}
reco();
</script>
</body>
</html>