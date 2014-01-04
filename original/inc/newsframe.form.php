<!-- BEGIN postbox form ***if i use this i will need to add a user authentication function b/c the login.auth script if no good here*** //-->
<form method="post" action="./inc/.php" class="text">
	<b>name</b>:&nbsp;
	<input name="login_name" type="text" size="15" class="feild"><br />
	<b>passwd</b>:&nbsp;
	<input name="login_passwd" type="password" size="15" class="feild"><br /><br />
					
	<textarea rows="7" cols="25" class="feild"></textarea>
					
	<input name="FORM" type="hidden" value="postbox">
	<input name="SUBMITPATH" type="hidden" value="<? echo $PHP_SELF; ?>">

	<input name="submit" type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;post&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" class="button">
</form>
<!-- END postbox form //-->