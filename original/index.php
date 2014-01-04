<?
$page_title = "";
$page_type = "index";  
require("./inc/control.php");
include($HTMLHEADER);
include($JAVASCRIPT_PATH);
include($BODYHEAD);
if ( $LOGIN_ONLY=="OFF" )
{ ?>
<table border="0" cellpadding="0" cellspacing="0" class="table" width="700" class="main">
<tr valign="top">
    <td width="580">
    <?	 require($INCLUDE_PATH . "quests.all.php"); ?>
    <!-- END main body area -->
    </td>
    <td width="200" align="right">
    <!-- BEGIN tools nav bar -->
    <?	require($INCLUDE_PATH . "menus.php");	?>
    <!-- END tools nav bar -->
    </td>
</tr>
</table>
<?  include($COPYRIGHT);
}
check_include($HTMLFOOTER);
exit; ?>
