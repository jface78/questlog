<?
$page_title = "Questlog is offline.";
$page_type="SITE_STATUS_PAGE";
require("./inc/control.php");
require($HTMLHEADER);
require($JAVASCRIPT_PATH);
?>
<table border="0" cellpadding="0" cellspaceing="0" width="700" height="80" class="main">
<tr valign="top">
	<td width="520" align="left"class="borders">
		<img src="./img/title.01.gif" width="100" height="25" alt="QUESTLOG" border="0"><br />
		<!-- &nbsp;&nbsp;you are not currently logged in. -->
	</td>
	<td width="180" align="right"class="borders"><? #$SUBMITPATH = "quests.entry.php"; require($INCLUDE_PATH . "login.form.php"); ?></td>
</tr>
</table>
<hr color="#868684" width="700" align="left" size="1">

<table border="0" cellpadding="0" cellspacing="0" class="table" width="700" class="main">
<tr valign="top">
	<td width="580">
	<!-- BEGIN main body area-->
	
	<b>The Questlog is currently down for maintenance, service should be returned shortly.</b>
		
	<!-- END main body area -->
	</td>
	<td width="200" align="right">
	<!-- BEGIN tools nav bar -->
		-------------------<br />
		<a href="javascript: post_window('./signup.form.php');" onMouseOver="window.status='account register'; return 0" onMouseOut="window.status=''; return 0">account register</a><br />
		<a href="javascript: post_window('./contact.php');" onMouseOver="window.status='contact the questlog admin'; return 0" onMouseOut="window.status=''; return 0">contact form</a>
	
		<br /><br />
		<br /><br />
		
		<!-- BEGIN mini banner table -->
		<table border="0" cellpadding="0" cellspacing="0" width="100">
		<tr>
			<td bgcolor="#868684" colspan="3"><img src="./img/px.gif" width="1" height="1" border="0"></td>
		</tr>
		<tr>
			<td bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0"></td>
			<td bgcolor="#4D5053" align="center">
			<? 
				$paperpimpsrc = rand_array("banner.pimp.01.gif,banner.pimp.02.gif,banner.pimp.03.gif,banner.pimp.07.gif");
				$beastsrc = rand_array("beastbanner.gif");
			?>
				<img src="./img/px.gif" width="1" height="7" border="0"><br />
				<a href="http://www.beastmeat.com/" target="new"><img src="./img/<? echo $beastsrc; ?>" width="88" height="31" alt="beastmeat.com" border="0"></a><br />
				<img src="./img/px.gif" width="1" height="7" border="0"><br />
				<a href="http://www.paperpimp.com/" target="new"><img src="./img/<? echo $paperpimpsrc; ?>" width="88" height="31" border="0" alt="paperpimp.com"></a><br />
				<img src="./img/px.gif" width="1" height="7" border="0"><br />
				<img src="img/banner.gif" width="88" height="31" border="0"><br />
				<img src="./img/px.gif" width="1" height="7" border="0"><br />
			</td>
			<td bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0"></td>
		</tr>
		<tr>
			<td bgcolor="#868684" colspan="3"><img src="./img/px.gif" width="1" height="1" border="0"></td>
		</tr>
		</table>
		<!-- END mini banner table -->
		
		<br /><br />
	<!-- END tools nav bar -->
	</td>
</tr>
</table>
<?
require($INCLUDE_PATH . "copyright.php");
check_include($HTMLFOOTER);
?>
