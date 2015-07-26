<?php

$op2 = httpget('op2');
page_header("Actions Management");

if($op2 == ""){
	output("This function simply deserializes the player's Actions array, making it easier to edit them.  This searching bit is pretty much unashamedly stolen from Nicolas Harter's Abilities system.  What poor unfortunate dingbat would you like to screw with?`n`n");
	rawoutput("<form action='runmodule.php?module=staminasystem&op=editplayer&op2=search' method='post'>");
	rawoutput("<input name='name'>");
	rawoutput("<input type='submit' class='button' value='".translate_inline("search")."'>");
	rawoutput("</form>");
	addnav("","runmodule.php?module=staminasystem&op=editplayer&op2=search");
	addnav("Return");
	addnav("Return","runmodule.php?module=staminasystem&op=superuser");
}
if($op2 == "search"){
	$search="%";
	$n = httppost('name');
	for ($x=0;$x<strlen($n);$x++){
		$search .= substr($n,$x,1)."%";
	}
	$search=" AND name LIKE '".addslashes($search)."' ";
	$sql = "SELECT acctid, name FROM " . db_prefix("accounts") . " WHERE locked=0 $search ORDER BY name DESC ";
	$result = db_query($sql);
	
	output("Following players were found, and would be trembling in their boots if they knew what you were doing:`n`n");
	
	for($i=0;$i < db_num_rows($result);$i++){
		$row = db_fetch_assoc($result);
		addnav("","runmodule.php?module=staminasystem&op=editplayer&op2=edit&id=".$row['acctid']."");
		output_notl("Player: ".$row['name']."<a href='runmodule.php?module=staminasystem&op=editplayer&op2=edit&id=".$row['acctid']."'> (EDIT)</a> | <a href='runmodule.php?module=staminasystem&op=editplayer&op2=defaults&id=".$row['acctid']."'> (SET TO DEFAULT)</a>",true);
		output("`n");
	}
	if(db_num_rows($result) == 0 ){
		output("Nobody came up in the search!  Computer says no.");
	}
	addnav("Return");
	addnav("Search again","runmodule.php?module=staminasystem&op=editplayer");
	addnav("Return","runmodule.php?module=staminasystem&op=superuser");
}
if($op2 == "edit" ){
	$id = httpget('id');
	$actions = get_player_action_list($id);
	output("Here we have the editing screen.  Follow the format you see already, seperating parameters from their values with '=>' and using ';;' to denote a new line.  Leave the last line without the ';;' and everything should be fine.`n`n");
	rawoutput("<form action='runmodule.php?module=staminasystem&op=editplayer&op2=save&id=".$id."' method='POST'>");
	foreach($actions AS $action => $details){
		rawoutput($action);
		rawoutput("<br /><textarea name='$action' cols='40' rows='12'>");
		$size=count($details);
		$i = 0;
		foreach($details AS $detail => $value){
			$textboxoutput = $detail."=>".$value;
			$i++;
			if ($i != $size){
				$textboxoutput .=";;";
			}
			rawoutput($textboxoutput);
		}
		rawoutput("</textarea><br /><br />");
	}
	rawoutput("<button type='submit' class='button'>".translate_inline("Save")."</button>");
	rawoutput("</form>");
	addnav("","runmodule.php?module=staminasystem&op=editplayer&op2=save&id=".$id."");
	addnav("Return");
	addnav("Search again","runmodule.php?module=staminasystem&op=editplayer");
	addnav("Return","runmodule.php?module=staminasystem&op=superuser");
}
if($op2 == "save"){
	$userid = httpget('id');
	$postedactions = httpallpost();
	function trim_value(&$value) {
		$value = trim($value);
	}
	foreach($postedactions AS $action => $details){
		$action = str_replace("_"," ",$action);
		$action = trim($action);
		$detailsexploded = explode(";;",$details);
		foreach($detailsexploded as $detail){
			$detailandvalue = explode("=>",$detail);
			$size=count($detailandvalue);
			for ($i=0; $i < $size/2; $i++){
				$detailandvalue[$i] = trim($detailandvalue[$i]);
				$actionsarray[$action][$detailandvalue[$i]]=$detailandvalue[$i+($size/2)];
			}
		}
	}
	
	debug("Is this a full, reconstructed Actions array for this player?");
	debug($actionsarray);
	debug($actionsarray);
	set_module_pref("actions",serialize($actionsarray),"staminasystem",$userid);
	output("Actions have been saved.");
	
	addnav("Return");
	addnav("Search again","runmodule.php?module=staminasystem&op=editplayer");
	addnav("Return","runmodule.php?module=staminasystem&op=superuser");
}
if($op2 == "defaults"){
	$userid = httpget('id');
	
	set_module_pref("actions",array(0),"staminasystem",$userid);
	output("Actions have been saved.");
	
	addnav("Return");
	addnav("Search again","runmodule.php?module=staminasystem&op=editplayer");
	addnav("Return","runmodule.php?module=staminasystem&op=superuser");
}

page_footer();

?>