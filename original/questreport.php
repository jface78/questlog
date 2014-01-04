#!/usr/local/bin/php
<?php
require("/usr/www/webroot/questlog/inc/control.php");
require($FUNCTIONS);
if ( $db = databaseConnection($DATABASE)  )
{
	$sql = "SELECT q.qid,q.quest_name,COUNT(p.pid) AS totalposts FROM quests q,posts p WHERE q.quest_status<'4' AND q.qid=p.qid GROUP BY q.qid ORDER BY totalposts DESC";
	$all_quests_query = mysql_query($sql) or die("database error [all quest table]");
	$quest_number = mysql_num_rows($all_quests_query);
	if( $quest_number!="0" )
	{
		if ( is_readable(".quests") )
		{
			#echo "found the file";
			if ( $quests_file = @fopen(".quests", "r") )
			{
				$quests = fread( $quests_file, filesize(".quests") );
				#echo $quests;
				$your_quests = explode("\n", $quests);
				fclose($quests_file);
			}
		}
		$row_counter="1";
		while( $quests = mysql_fetch_array($all_quests_query) )
		{
			$qid =  $quests["qid"];
			$quest_name =  $quests["quest_name"];
			$post_number =  $quests["totalposts"];
			$quest_poster = last2post($qid);
			if ( is_array($your_quests) )
			{
				if ( in_array($quest_name, $your_quests) ) { echo " " . $quest_name . " : " . $post_number . " : " . $quest_poster . "\n"; }
			}
			else { echo " " . $quest_name . " : " . $post_number . " : " . $quest_poster . "\n"; }
			$row_counter++;
		}
	}
	else { echo "there are no current quests registered."; }
}
else { echo "the database server appears to be offline, try again later."; }
?>
