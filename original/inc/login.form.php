<!-- BEGIN login form //-->
<form method="post" action="<? echo $LOGIN_SUBMIT; ?>" class="text">
	<input type="hidden" name="FORM" value="ins" />
	<input type="hidden" name="ENTRY_SUBMIT" value="<? echo $ENTRY_SUBMIT; ?>" />
	<b>login</b>:&nbsp;<input name="ins1" type="text" size="15" class="field" /><br />
	<b>passwd</b>:&nbsp;<input name="ins2" type="password" size="15" class="field" /><br />
	<input name="submit" type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;login&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" class="button" />
</form>
<!-- END login form //-->