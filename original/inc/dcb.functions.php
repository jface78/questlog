<?
#### TEXT FORMATING FUNCTION FILE (dcb.functions.php) ####
##

function formatInsertedContent($post_content)
{
	### strip out html special characters, protect the questlog from malicious code ###
	$html_safe = htmlspecialchars($post_content, ENT_QUOTES);
	
	### basic content markup codes, restore safe html characters ###
	$ind = str_replace("[ind]", "<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $html_safe);
	$b = str_replace("[b]", "<b>", $ind);
	$b_close = str_replace("[/b]", "</b>", $b);
	$i = str_replace("[i]", "<i>", $b_close);
	$i_close = str_replace("[/i]", "</i>", $i);
	$u = str_replace("[u]", "<u>", $i_close);
	$u_close = str_replace("[/u]", "</u>", $u);
	$c = str_replace("[c]", "<center>", $u_close);
	$c_close = str_replace("[/c]", "</center>", $c);
	
	### smart quote support for content pased from word2000 ###
	$smartquotes1 = str_replace("&amp;#8217;", "&#039;", $c_close);
	$smartquotes2 = str_replace("&amp;#8216;", "&#039;", $smartquotes1);
	$smartquotes3 = str_replace("&amp;#8220;", "&quot;", $smartquotes2);
	$smartquotes4 = str_replace("&amp;#8221;", "&quot;", $smartquotes3);
	$threedots = str_replace("&amp;#8230;", "...", $smartquotes4);
	
	### natural syntax ###
	$linebreaks = str_replace("\n", "<br />", $threedots);
	$tabs = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $linebreaks);
	
	### img support, could be better ###
	$img = str_replace("[img]", "<img ", $tabs);
	$close_bracket = str_replace("[/cb]", ">", $img);
	
	### set final variable ###
	$formated_post = $close_bracket;
	return($formated_post);
}

function formatQueriedContent($post_content)
{
	### basic content markup codes, restore safe html characters ###
	$ind = str_replace("<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", "[ind]", $post_content);
	$b = str_replace("<b>", "[b]", $ind);
	$b_close = str_replace("</b>", "[/b]", $b);
	$i = str_replace("<i>", "[i]", $b_close);
	$i_close = str_replace("</i>", "[/i]", $i);
	$u = str_replace("<u>", "[u]", $i_close);
	$u_close = str_replace("</u>", "[/u]", $u);
	$c = str_replace("<center>", "[c]", $u_close);
	$c_close = str_replace("</center>", "[/c]", $c);
	
	### smart quote support for content pased from word2000 ###
	$smartquotes1 = str_replace("&amp;#8217;", "&#039;", $c_close);
	$smartquotes2 = str_replace("&amp;#8216;", "&#039;", $smartquotes1);
	$smartquotes3 = str_replace("&amp;#8220;", "&quot;", $smartquotes2);
	$smartquotes4 = str_replace("&amp;#8221;", "&quot;", $smartquotes3);
	$threedots = str_replace("&amp;#8230;", "...", $smartquotes4);
	
	### natural syntax ###
	$linebreaks = str_replace("<br />", "\n", $threedots);
	$tabs = str_replace("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", "\t", $linebreaks);
	
	### img support, could be better ###
	$img = str_replace("<img ", "[img]", $tabs);
	$close_bracket = str_replace(">", "[/cb]", $img);
	
	### set final variable ###
	$formated_post = $close_bracket;
	return($formated_post);
}

#### EOF ####
?>