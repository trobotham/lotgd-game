<?php

// translator ready
// addnews ready
// mail ready
require_once 'common.php';
require_once 'lib/dhms.php';

tlschema('referers');

check_su_access(SU_EDIT_CONFIG);

$expire = getsetting('expirecontent', 180);

if ($expire > 0)
{
    $sql = 'DELETE FROM '.DB::prefix('referers')." WHERE last<'".date('Y-m-d H:i:s', strtotime('-'.$expire.' days'))."'";
}
DB::query($sql);
$op = httpget('op');

if ('rebuild' == $op)
{
    $sql = 'SELECT * FROM '.DB::prefix('referers');
    $result = DB::query($sql);

    while ($row = DB::fetch_assoc($result))
    {
        $site = str_replace('http://', '', $row['uri']);

        if (strpos($site, '/'))
        {
            $site = substr($site, 0, strpos($site, '/'));
        }
        $sql = 'UPDATE '.DB::prefix('referers')." SET site='".addslashes($site)."' WHERE refererid='{$row['refererid']}'";
        DB::query($sql);
    }
}
require_once 'lib/superusernav.php';
superusernav();
addnav('Referer Options');
addnav('', $_SERVER['REQUEST_URI']);
$sort = httpget('sort');
addnav('Refresh', 'referers.php?sort='.urlencode($sort).'');
addnav('C?Sort by Count', 'referers.php?sort=count'.('count DESC' == $sort ? '' : '+DESC'));
addnav('U?Sort by URL', 'referers.php?sort=uri'.('uri' == $sort ? '+DESC' : ''));
addnav('T?Sort by Time', 'referers.php?sort=last'.('last DESC' == $sort ? '' : '+DESC'));

addnav('Rebuild Sites', 'referers.php?op=rebuild');

page_header('Referers');
$order = 'count DESC';

if ('' != $sort)
{
    $order = $sort;
}
$sql = 'SELECT SUM(count) AS count, MAX(last) AS last,site FROM '.DB::prefix('referers')." GROUP BY site ORDER BY $order LIMIT 100";
$count = translate_inline('Count');
$last = translate_inline('Last');
$dest = translate_inline('Destination');
$none = translate_inline('`iNone´i');
$notset = translate_inline('`iNot set´i');
$skipped = translate_inline('`i%s records skipped (over a week old)´i');
rawoutput("<table class='ui very compact striped selectable table'><thead><tr><th>$count</th><th>$last</th><th>URL</th><th>$dest</th><th>IP</th></tr></thead>");
$result = DB::query($sql);

while ($row = DB::fetch_assoc($result))
{
    rawoutput("<tr><td valign='top'>");
    output_notl('`b'.$row['count'].'´b');
    rawoutput("</td><td valign='top'>");
    $diffsecs = strtotime('now') - strtotime($row['last']);
    output_notl('`b'.dhms($diffsecs).'´b');
    rawoutput("</td><td valign='top' colspan='3'>");
    output_notl('`b'.('' == $row['site'] ? $none : $row['site']).'´b');
    rawoutput('</td></tr>');

    $sql = 'SELECT count,last,uri,dest,ip FROM '.DB::prefix('referers')." WHERE site='".addslashes($row['site'])."' ORDER BY {$order} LIMIT 25";
    $result1 = DB::query($sql);
    $skippedcount = 0;
    $skippedtotal = 0;
    $number = DB::num_rows($result1);

    for ($k = 0; $k < $number; $k++)
    {
        $row1 = DB::fetch_assoc($result1);
        $diffsecs = strtotime('now') - strtotime($row1['last']);

        if ($diffsecs <= 604800)
        {
            rawoutput('<tr><td>');
            output_notl($row1['count']);
            rawoutput("</td><td valign='top'>");
            output_notl(dhms($diffsecs));
            rawoutput("</td><td valign='top'>");

            if ($row1['uri'] > '')
            {
                rawoutput("<a href='".htmlentities($row1['uri'], ENT_COMPAT, getsetting('charset', 'UTF-8'))."' target='_blank'>".htmlentities(substr($row1['uri'], 0, 100)).'</a>');
            }
            else
            {
                output_notl($none);
            }
            output_notl('`n');
            rawoutput("</td><td valign='top'>");
            output_notl('' == $row1['dest'] ? $notset : $row1['dest']);
            rawoutput("</td><td valign='top'>");
            output_notl('' == $row1['ip'] ? $notset : $row1['ip']);
            rawoutput('</td></tr>');
        }
        else
        {
            $skippedcount++;
            $skippedtotal += $row1['count'];
        }
    }

    if ($skippedcount > 0)
    {
        rawoutput("<tr><td>$skippedtotal</td><td valign='top' colspan='4'>");
        output_notl(sprintf($skipped, $skippedcount));
        rawoutput('</td></tr>');
    }
}
rawoutput('</table>');
page_footer();