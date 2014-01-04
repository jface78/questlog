
<table border="0" cellpadding="5" cellspacing="0" width="100%" class="newsframe">
<tr>
	<td>
<?		if ( $db ) /* Display the most recent character post */
		{
			$query = mysql_query("SELECT p.post_text,p.qid,q.quest_name FROM posts p,quests q WHERE p.qid=q.qid ORDER BY post_date DESC LIMIT 1");
			$array = mysql_fetch_array($query);
			
			if ( check_session() || 0 < $access = mysql_num_rows(mysql_query("SELECT quest_status FROM quests WHERE qid='" . $array["qid"] . "' AND quest_status='0'")) )
			{
				$last_date = last2post($array["qid"],"18");
				$name = $last_date["0"];
				$date = $last_date["1"];
				echo "<i>From: " . $array["quest_name"] . " by: " . $name . " on: " .  $date . "</i><br /><br />" . $array["post_text"] . "<br /><br />";
			}
			else { echo "No quest access"; }
		}
		else { echo $ERROR_DB_OFFLINE; } ?>
	</td>
</tr>
</table>
