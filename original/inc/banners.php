<?		if($_GET["banners"] == "show")
		{ 	?>
			<!-- BEGIN mini banner table -->
			<br /><br /><br />
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
			</table><br /><br />
			<!-- END mini banner table -->
<?		} 	?>