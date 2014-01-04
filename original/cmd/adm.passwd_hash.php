<?	if ( $_POST["passwd"]!="" && $_POST["name"]!="" ) 
	{
		$hash = hashPasswd($_POST["name"], $_POST["passwd"]);
		echo $hash . "\n";
	}
	else { ?>
		<form method="post" action="<? echo $POST_TO; ?>" class="text">
			<b>name</b>:<br /><input name="name" type="text" size="30" class="field" /><br /><br />
			<b>passwd</b>:<br /><input name="passwd" type="password" size="30" class="field" /><br /><br />
			<input name="submit" type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;hash&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" class="button" />
		</form>
<?	} ?>
