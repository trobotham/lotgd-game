<?php

// translator ready
// addnews ready
// mail ready
define('ALLOW_ANONYMOUS', true);
require_once 'common.php';
require_once 'lib/villagenav.php';

tlschema('news');

modulehook('news-intercept', []);

if ($session['user']['loggedin'])
{
    checkday();
}
$newsperpage = 50;

$offset = (int) httpget('offset');
$timestamp = strtotime((0 - $offset).' days');
$sql = 'SELECT count(newsid) AS c FROM '.DB::prefix('news')." WHERE newsdate='".date('Y-m-d', $timestamp)."'";
$result = DB::query($sql);
$row = DB::fetch_assoc($result);
$totaltoday = $row['c'];
$page = (int) httpget('page');

if (! $page)
{
    $page = 1;
}
$pageoffset = $page;

if ($pageoffset > 0)
{
    $pageoffset--;
}
$pageoffset *= $newsperpage;
$sql = 'SELECT * FROM '.DB::prefix('news')." WHERE newsdate='".date('Y-m-d', $timestamp)."' ORDER BY newsid DESC LIMIT $pageoffset,$newsperpage";
$result = DB::query($sql);
page_header('LoGD News');
$date = date('D, M j, Y', $timestamp);

$pagestr = '';

if ($totaltoday > $newsperpage)
{
    $pagestr = sprintf_translate('(Items %s - %s of %s)', $pageoffset + 1,
            min($pageoffset + $newsperpage, $totaltoday), $totaltoday);
}

$sql2 = 'SELECT '.DB::prefix('motd').'.*,name AS motdauthorname FROM '.DB::prefix('motd').' LEFT JOIN '.DB::prefix('accounts').' ON '.DB::prefix('accounts').'.acctid = '.DB::prefix('motd').'.motdauthor ORDER BY motddate DESC LIMIT 1';
$result2 = DB::query_cached($sql2, 'lastmotd');

while ($row = DB::fetch_assoc($result2))
{
    require_once 'lib/motd.php';
    require_once 'lib/nltoappon.php';

    if ('' == $row['motdauthorname'])
    {
        $row['motdauthorname'] = translate_inline('`@Green Dragon Staff`0');
    }

    if (0 == $row['motdtype'])
    {
        motditem($row['motdtitle'], $row['motdbody'], $row['motdauthorname'], $row['motddate'], '');
    }
    else
    {
        pollitem($row['motditem'], $row['motdtitle'], $row['motdbody'], $row['motdauthorname'], $row['motddate'], false);
    }
}
output_notl('`n');
output('`c`b`!News for %s %s`0`b`c', $date, $pagestr);

while ($row = DB::fetch_assoc($result))
{
    output_notl('`c`2-=-`@=-=`2-=-`@=-=`2-=-`@=-=`2-=-`0`c');

    if ($session['user']['superuser'] & SU_EDIT_COMMENTS)
    {
        $del = translate_inline('Del');
        rawoutput("[ <a href='superuser.php?op=newsdelete&newsid=".$row['newsid'].'&return='.urlencode($_SERVER['REQUEST_URI'])."'>$del</a> ]&nbsp;");
        addnav('', "superuser.php?op=newsdelete&newsid={$row['newsid']}&return=".urlencode($_SERVER['REQUEST_URI']));
    }
    tlschema($row['tlschema']);

    if ($row['arguments'] > '')
    {
        $arguments = [];
        $base_arguments = unserialize($row['arguments']);
        array_push($arguments, $row['newstext']);

        foreach ($base_arguments as $val)
        {
            array_push($arguments, $val);
        }
        $news = call_user_func_array('sprintf_translate', $arguments);
        rawoutput(tlbutton_clear());
    }
    else
    {
        $news = translate_inline($row['newstext']);
    }
    tlschema();
    output_notl($news.'`n');
}

if (0 == DB::num_rows($result))
{
    output_notl('`c`2-=-`@=-=`2-=-`@=-=`2-=-`@=-=`2-=-`0`c');
    output('`1`b`c Nothing of note happened this day.  All in all a boring day. `c`b`0');
}
output_notl('`c`2-=-`@=-=`2-=-`@=-=`2-=-`@=-=`2-=-`0`c');

if (! $session['user']['loggedin'])
{
    addnav('Login page');
    addnav('Login Screen', 'index.php');
}
elseif ($session['user']['alive'])
{
    villagenav();
}
else
{
    tlschema('nav');
    addnav('Log Out');
    addnav('Log out', 'login.php?op=logout');

    if (1 == $session['user']['sex'])
    {
        addnav("`!`bYou're dead, Jane!`b`0");
    }
    else
    {
        addnav("`!`bYou're dead, Jim!`b`0");
    }
    addnav('S?Land of Shades', 'shades.php');
    addnav('G?The Graveyard', 'graveyard.php');
    require_once 'lib/battle/extended.php';
    suspend_companions('allowinshades', true);
    tlschema();
}
addnav('News');
addnav('Previous News', 'news.php?offset='.($offset + 1));

if ($offset > 0)
{
    addnav('Next News', 'news.php?offset='.($offset - 1));
}

if ($session['user']['loggedin'])
{
    addnav('Preferences', 'prefs.php');
}
addnav('About this game', 'about.php');

tlschema('nav');

if ($session['user']['superuser'] & SU_EDIT_COMMENTS)
{
    addnav('Superuser');
    addnav(',?Comment Moderation', 'moderate.php');
}

if ($session['user']['superuser'] & ~SU_DOESNT_GIVE_GROTTO)
{
    addnav('Superuser');
    addnav('X?Superuser Grotto', 'superuser.php');
}

if ($session['user']['superuser'] & SU_INFINITE_DAYS)
{
    addnav('Superuser');
    addnav('/?New Day', 'newday.php');
}
tlschema();

addnav('', 'news.php');

if ($totaltoday > $newsperpage)
{
    addnav("Today's news");

    for ($i = 0; $i < $totaltoday; $i += $newsperpage)
    {
        $pnum = $i / $newsperpage + 1;

        if ($pnum == $page)
        {
            addnav(['`b`#Page %s`0`b', $pnum], "news.php?offset=$offset&page=$pnum");
        }
        else
        {
            addnav(['Page %s', $pnum], "news.php?offset=$offset&page=$pnum");
        }
    }
}

page_footer();
