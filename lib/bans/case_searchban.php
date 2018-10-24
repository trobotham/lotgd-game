<?php

$subop = httpget('subop');
$none = translate_inline('NONE');

if ('xml' == $subop)
{
    header('Content-Type: text/xml');
    $sql = 'SELECT DISTINCT '.DB::prefix('accounts').'.name FROM '.DB::prefix('bans').', '.DB::prefix('accounts')." WHERE (ipfilter='".addslashes(httpget('ip'))."' AND ".
        DB::prefix('bans').".uniqueid='".
        addslashes(httpget('id'))."') AND ((substring(".
        DB::prefix('accounts').'.lastip,1,length(ipfilter))=ipfilter '.
        "AND ipfilter<>'') OR (".DB::prefix('bans').'.uniqueid='.
        DB::prefix('accounts').'.uniqueid AND '.
        DB::prefix('bans').".uniqueid<>''))";
    $r = DB::query($sql);
    echo '<xml>';

    while ($ro = DB::fetch_assoc($r))
    {
        echo '<name name="';
        echo urlencode(appoencode("`0{$ro['name']}"));
        echo '"/>';
    }

    if (0 == DB::num_rows($r))
    {
        echo "<name name=\"$none\"/>";
    }
    echo '</xml>';

    exit();
}
$operator = '<=';

$target = httppost('target');
$since = 'WHERE 0';
$submit = translate_inline('Search');

if ('' == $target)
{
    rawoutput("<br><form action='bans.php?op=searchban' method='POST' class='ui form'><div class='inline field'><label>");
    addnav('', 'bans.php?op=searchban');
    output('Search banned user by name: ');
    rawoutput("</label><div class='ui action input'><input name='target' value='$target'>");
    rawoutput("<button type='submit' class='ui button'>$submit</button></div></div></from>");
}
elseif (is_numeric($target))
{
    //none
    $sql = 'SELECT lastip,uniqueid FROM accounts WHERE acctid='.$target;
    $result = DB::query($sql);
    $row = DB::fetch_assoc($result);
    $since = "WHERE ipfilter LIKE '%".$row['lastip']."%' OR uniqueid LIKE '%".$row['uniqueid']."%'";
}
else
{
    require_once 'lib/lookup_user.php';
    $names = lookup_user($target);

    if (false !== $names[0])
    {
        rawoutput("<form action='bans.php?op=searchban' method='POST'>");
        addnav('', 'bans.php?op=searchban');
        output('Search banned user by name: ');
        rawoutput("<select name='target'>");

        while ($row = DB::fetch_assoc($names[0]))
        {
            rawoutput("<option value='".$row['acctid']."'>".$row['login'].'</option>');
        }
        rawoutput('</select>');
        rawoutput("<input type='submit' class='button' value='$submit'></from><br><br>");
    }
}

$sql = 'SELECT * FROM '.DB::prefix('bans')." $since ORDER BY banexpire ASC";
$result = DB::query($sql);
rawoutput("<script language='JavaScript'>
function getUserInfo(ip,id,divid){
	var filename='bans.php?op=removeban&subop=xml&ip='+ip+'&id='+id;
	//set up the DOM object
	var xmldom;
	if (document.implementation &&
			document.implementation.createDocument){
		//Mozilla style browsers
		xmldom = document.implementation.createDocument('', '', null);
	} else if (window.ActiveXObject) {
		//IE style browsers
		xmldom = new ActiveXObject('Microsoft.XMLDOM');
	}
		xmldom.async=false;
	xmldom.load(filename);
	var output='';
	for (var x=0; x<xmldom.documentElement.childNodes.length; x++){
		output = output + unescape(xmldom.documentElement.childNodes[x].getAttribute('name').replace(/\\+/g,' ')) +'<br>';
	}
	document.getElementById('user'+divid).innerHTML=output;
}
</script>
");
rawoutput("<table class='ui very compact striped selectable table'>");
$ops = translate_inline('Ops');
$bauth = translate_inline('Ban Author');
$ipd = translate_inline('IP/ID');
$dur = translate_inline('Duration');
$mssg = translate_inline('Message');
$aff = translate_inline('Affects');
$l = translate_inline('Last');
    rawoutput("<thead><tr><th>$ops</th><th>$bauth</th><th>$ipd</th><th>$dur</th><th>$mssg</th><th>$aff</th><th>$l</th></tr></thead>");
$i = 0;

while ($row = DB::fetch_assoc($result))
{
    $liftban = translate_inline('Lift&nbsp;ban');
    $showuser = translate_inline('Click&nbsp;to&nbsp;show&nbsp;users');
    rawoutput('<tr>');
    rawoutput("<td><a href='bans.php?op=delban&ipfilter=".urlencode($row['ipfilter']).'&uniqueid='.urlencode($row['uniqueid'])."'>");
    output_notl('%s', $liftban, true);
    rawoutput('</a>');
    addnav('', 'bans.php?op=delban&ipfilter='.urlencode($row['ipfilter']).'&uniqueid='.urlencode($row['uniqueid']));
    rawoutput('</td><td>');
    output_notl('`&%s`0', $row['banner']);
    rawoutput('</td><td>');
    output_notl('%s', $row['ipfilter']);
    output_notl('%s', $row['uniqueid']);
    rawoutput('</td><td>');
    // "43200" used so will basically round to nearest day rather than floor number of days

    $expire = sprintf_translate('%s days',
            round((strtotime($row['banexpire']) + 43200 - strtotime('now')) / 86400, 0));

    if ('1 ' == substr($expire, 0, 2))
    {
        $expire = translate_inline('1 day');
    }

    if (date('Y-m-d', strtotime($row['banexpire'])) == date('Y-m-d'))
    {
        $expire = translate_inline('Today');
    }

    if (date('Y-m-d', strtotime($row['banexpire'])) ==
            date('Y-m-d', strtotime('1 day')))
    {
        $expire = translate_inline('Tomorrow');
    }

    if ('0000-00-00 00:00:00' == $row['banexpire'])
    {
        $expire = translate_inline('Never');
    }
    output_notl('%s', $expire);
    rawoutput('</td><td>');
    output_notl('%s', $row['banreason']);
    rawoutput('</td><td>');
    $file = "bans.php?op=removeban&subop=xml&ip={$row['ipfilter']}&id={$row['uniqueid']}";
    rawoutput("<div id='user$i'><a href='$file' target='_blank' onClick=\"getUserInfo('{$row['ipfilter']}','{$row['uniqueid']}',$i); return false;\">");
    output_notl('%s', $showuser, true);
    rawoutput('</a></div>');
    addnav('', $file);
    rawoutput('</td><td>');
    output_notl(LotgdFormat::relativedate($row['lasthit']));
    rawoutput('</td></tr>');
    $i++;
}
rawoutput('</table>');
