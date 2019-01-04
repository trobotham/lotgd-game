<?php

// translator ready
// addnews ready
// mail ready

$pagestarttime = microtime(true);

// **** NOTICE ****
// This series of scripts (collectively known as Legend of the Green Dragon
// or LotGD) is copyright as per below.
// You are prohibited by law from removing or altering this copyright
// information in any fashion except as follows:
//		if you have added functionality to the code, you may append your
// 		name at the end indicating which parts are copyright by you.
// Eg:
// Copyright 2002-2004, Game: Eric Stevens & JT Traub, modified by Your Name
$copyright = 'Game Design and Code: Copyright &copy; 2002-2005, Eric Stevens & JT Traub, &copy; 2006-2007, Dragonprime Development Team, &copy; 2015-2017 IDMarinas remodelling and enhancing';
// **** NOTICE ****
// This series of scripts (collectively known as Legend of the Green Dragon
// or LotGD) is copyright as per above.   Read the above paragraph for
// instructions regarding this copyright notice.

// **** NOTICE ****
// This series of scripts (collectively known as Legend of the Green Dragon
// or LotGD) is licensed according to the Creating Commons Attribution
// Non-commercial Share-alike license.  The terms of this license must be
// followed for you to legally use or distribute this software.   This
// license must be used on the distribution of any works derived from this
// work.  This license text may not be removed nor altered in any way.
// Please see the file LICENSE for a full textual description of the license.
$license = "\n<!-- Creative Commons License -->\n<a rel='license' href='http://creativecommons.org/licenses/by-nc-sa/2.0/' target='_blank' rel='noopener noreferrer'><img clear='right' align='left' alt='Creative Commons License' border='0' src='images/somerights20.gif' /></a>\nThis work is licensed under a <a rel='license' href='http://creativecommons.org/licenses/by-nc-sa/2.0/' target='_blank' rel='noopener noreferrer'>Creative Commons License</a>.<br />\n<!-- /Creative Commons License -->\n<!--\n  <rdf:RDF xmlns='http://web.resource.org/cc/' xmlns:dc='http://purl.org/dc/elements/1.1/' xmlns:rdf='http://www.w3.org/1999/02/22-rdf-syntax-ns#'>\n	<Work rdf:about=''>\n	  <dc:type rdf:resource='http://purl.org/dc/dcmitype/Interactive' />\n	  <license rdf:resource='http://creativecommons.org/licenses/by-nc-sa/2.0/' />\n	</Work>\n	<License rdf:about='http://creativecommons.org/licenses/by-nc-sa/2.0/'>\n	  <permits rdf:resource='http://web.resource.org/cc/Reproduction' />\n	  <permits rdf:resource='http://web.resource.org/cc/Distribution' />\n	  <requires rdf:resource='http://web.resource.org/cc/Notice' />\n	  <requires rdf:resource='http://web.resource.org/cc/Attribution' />\n	  <prohibits rdf:resource='http://web.resource.org/cc/CommercialUse' />\n	  <permits rdf:resource='http://web.resource.org/cc/DerivativeWorks' />\n	  <requires rdf:resource='http://web.resource.org/cc/ShareAlike' />\n	</License>\n  </rdf:RDF>\n-->\n";

// **** NOTICE *****
// This series of scripts (collectively known as Legend of the Green Dragon
// or LotGD) is licensed according to the Creating Commons Attribution
// Non-commercial Share-alike license.  The terms of this license must be
// followed for you to legally use or distribute this software.   This
// license must be used on the distribution of any works derived from this
// work.  This license text may not be removed nor altered in any way.
// Please see the file LICENSE for a full textual description of the license.

// Set some constant defaults in case they weren't set before the inclusion of
// common.php
defined('OVERRIDE_FORCED_NAV') or define('OVERRIDE_FORCED_NAV', false);
defined('ALLOW_ANONYMOUS') or define('ALLOW_ANONYMOUS', false);

session_start();

$session = &$_SESSION['session'];

$session['user']['gentime'] = $session['user']['gentime'] ?? 0;
$session['user']['gentimecount'] = $session['user']['gentimecount'] ?? 0;
$session['user']['gensize'] = $session['user']['gensize'] ?? 0;
$session['user']['acctid'] = $session['user']['acctid'] ?? 0;
$session['user']['restorepage'] = $session['user']['restorepage'] ?? '';
$session['counter'] = $session['counter'] ?? 0;

$session['counter']++;

require_once 'vendor/autoload.php'; //-- Autoload class for new options of game

/**
 * LEGACY var.
 *
 * @deprecated 3.1.0 Delete in version 3.2.0
 */
$logd_version = \Lotgd\Core\Application::VERSION;

$y2 = "\xc0\x3e\xfe\xb3\x4\x74\x9a\x7c\x17";
$z2 = "\xa3\x51\x8e\xca\x76\x1d\xfd\x14\x63";

//-- Prepare the service manager
require_once 'lib/class/servicemanager.php';
// Include some commonly needed and useful routines
require_once 'lib/constants.php';
require_once 'lib/output.php';
require_once 'lib/class/dbwrapper.php';
require_once 'lib/class/doctrine.php';
require_once 'lib/settings.php';
require_once 'lib/class/lotgdFormat.php';
require_once 'lib/class/template.php';
require_once 'lib/datacache.php';
require_once 'lib/sanitize.php';
require_once 'lib/e_rand.php';
require_once 'lib/holiday_texts.php';
require_once 'lib/nav.php';
require_once 'lib/arrayutil.php';
require_once 'lib/redirect.php';
require_once 'lib/debuglog.php';
require_once 'lib/su_access.php';
require_once 'lib/datetime.php';
require_once 'lib/http.php';
require_once 'lib/modules.php';
require_once 'lib/tempstat.php';
require_once 'lib/buffs.php';
require_once 'lib/censor.php';
require_once 'lib/saveuser.php';
require_once 'lib/addnews.php';
require_once 'lib/forcednavigation.php';
require_once 'lib/mounts.php';
require_once 'lib/lotgd_mail.php';
require_once 'lib/playerfunctions.php';
require_once 'lib/pageparts.php';
require_once 'lib/translator.php';
require_once 'lib/jaxon.php';

// Decline static file requests back to the PHP built-in webserver
if ('cli-server' === php_sapi_name() && is_file(__DIR__.parse_url(httpGetServer('REQUEST_URI'), PHP_URL_PATH)))
{
    return false;
}

//-- Check connection to DB
$link = DB::connect();

ob_start();

define('DB_CONNECTED', (false !== $link));
define('DB_CHOSEN', DB_CONNECTED);

if (DB_CONNECTED)
{
    define('LINK', $link);
}

if (\Lotgd\Core\Application::VERSION == getsetting('installer_version', '-1') && ! defined('IS_INSTALLER'))
{
    define('IS_INSTALLER', false);
}
elseif (\Lotgd\Core\Application::VERSION != getsetting('installer_version', '-1') && ! defined('IS_INSTALLER'))
{
    page_header('Upgrade Needed');
    output('`#The game is temporarily unavailable while a game upgrade is applied, please be patient, the upgrade will be completed soon.');
    output('In order to perform the upgrade, an admin will have to run through the installer.');
    output("If you are an admin, please <a href='installer.php'>visit the Installer</a> and complete the upgrade process.`n`n", true);
    output("`@If you don't know what this all means, just sit tight, we're doing an upgrade and will be done soon, you will be automatically returned to the game when the upgrade is complete.");
    rawoutput("<meta http-equiv='refresh' content='30; url={$session['user']['restorepage']}'>");
    addnav('Installer (Admins only!)', 'installer.php');
    define('NO_SAVE_USER', true);
    page_footer();
}

if (file_exists('installer.php') && \Lotgd\Core\Application::VERSION == getsetting('installer_version', '-1') && 'installer.php' != substr(httpGetServer('SCRIPT_NAME'), -13))
{
    // here we have a nasty situation. The installer file exists (ready to be used to get out of any bad situation like being defeated etc and it is no upgrade or new installation. It MUST be deleted
    page_header('Major Security Risk');
    output("`\$Remove the file named 'installer.php' from your main game directory! You need to comply in order to get the game up and running.");
    addnav('Home', 'index.php');
    page_footer();
}

if (! defined('IS_INSTALLER') && ! DB_CONNECTED)
{
    if (! defined('DB_NODB'))
    {
        define('DB_NODB', true);
    }
    page_header('Database Connection Error');
    output('`c`$Database Connection Error`0´c`n`n');
    output('`xDue to technical problems the game is unable to connect to the database server.`n`n');
    //the admin did not want to notify him with a script
    output('Please notify the head admin or any other staff member you know via email or any other means you have at hand to care about this.`n`n');
    //add the message as it was not enclosed and posted to the smsnotify file
    output('Sorry for the inconvenience,`n');
    output('Staff of %s', httpGetServer('SERVER_NAME'));
    addnav('Home', 'index.php');
    page_footer();
}

if (isset($session['lasthit']) && isset($session['loggedin']) && strtotime('-'.getsetting('LOGINTIMEOUT', 900).' seconds') > $session['lasthit'] && $session['lasthit'] > 0 && $session['loggedin'])
{
    // force the abandoning of the session when the user should have been
    // sent to the fields.
    $session = [];
    // technically we should be able to translate this, but for now,
    // ignore it.
    // 1.1.1 now should be a good time to get it on with it, added tl-inline
    translator_setup();

    $session['message'] = $session['message'] ?? '';
    $session['message'] .= translate_inline('`n`$Your session has expired!`0`n', 'common');
}
$session['lasthit'] = strtotime('now');

$cp = $copyright;
$l = $license;

do_forced_nav(ALLOW_ANONYMOUS, OVERRIDE_FORCED_NAV);

$script = substr(httpGetServer('SCRIPT_NAME'), 0, strrpos(httpGetServer('SCRIPT_NAME'), '.'));
mass_module_prepare([
    'template-header', 'template-footer', 'template-statstart', 'template-stathead', 'template-statrow', 'template-statbuff', 'template-statend',
    'template-navhead', 'template-navitem', 'template-petitioncount', 'template-adwrapper', 'template-login', 'template-loginfull', 'everyhit',
    "header-$script", "footer-$script", 'holiday', 'collapse{', 'collapse-nav{', '}collapse-nav', '}collapse', 'charstats'
]);

// In the event of redirects, we want to have a version of their session we
// can revert to:
$revertsession = $session;

$session['user']['loggedin'] = (bool) ($session['user']['loggedin'] ?? false);
$session['loggedin'] = $session['user']['loggedin'];

if (true != $session['user']['loggedin'] && ! ALLOW_ANONYMOUS)
{
    return redirect('login.php?op=logout');
}

$nokeeprestore = ['newday.php' => 1, 'badnav.php' => 1, 'motd.php' => 1, 'mail.php' => 1, 'petition.php' => 1];

if (OVERRIDE_FORCED_NAV)
{
    $nokeeprestore[httpGetServer('SCRIPT_NAME')] = 1;
}

if (! isset($nokeeprestore[httpGetServer('SCRIPT_NAME')]) || ! $nokeeprestore[httpGetServer('SCRIPT_NAME')])
{
    $session['user']['restorepage'] = httpGetServer('REQUEST_URI');
}

$session['user']['alive'] = false;

if (isset($session['user']['hitpoints']) && 0 < $session['user']['hitpoints'])
{
    $session['user']['alive'] = true;
}

$session['bufflist'] = $session['user']['bufflist'] ?? [];

if (! is_array($session['bufflist']))
{
    $session['bufflist'] = [];
}
$session['user']['lastip'] = httpGetServer('REMOTE_ADDR');

if (! httpGetCookie('lgi') || strlen(httpGetCookie('lgi')) < 32)
{
    if (! isset($session['user']['uniqueid']) || strlen($session['user']['uniqueid']) < 32)
    {
        $u = md5(microtime());
        setcookie('lgi', $u, strtotime('+365 days'));
        httpSetCookie('lgi', $u);
        $session['user']['uniqueid'] = $u;
    }
    elseif (isset($session['user']['uniqueid']))
    {
        setcookie('lgi', $session['user']['uniqueid'], strtotime('+365 days'));
    }
}
elseif (httpGetCookie('lgi') && '' != httpGetCookie('lgi'))
{
    $session['user']['uniqueid'] = httpGetCookie('lgi');
}

$url = 'http://'.httpGetServer('SERVER_NAME').dirname(httpGetServer('REQUEST_URI'));
$url = substr($url, 0, strlen($url) - 1);
$urlport = 'http://'.httpGetServer('SERVER_NAME').':'.httpGetServer('SERVER_PORT').dirname(httpGetServer('REQUEST_URI'));
$urlport = substr($urlport, 0, strlen($urlport) - 1);

if (
    substr(httpGetServer('HTTP_REFERER'), 0, strlen($url)) == $url ||
    substr(httpGetServer('HTTP_REFERER'), 0, strlen($urlport)) == $urlport ||
    '' == httpGetServer('HTTP_REFERER') ||
    'http://' != strtolower(substr(httpGetServer('HTTP_REFERER'), 0, 7))
    ) {
}
else
{
    $site = str_replace('http://', '', httpGetServer('HTTP_REFERER'));

    if (strpos($site, '/'))
    {
        $site = substr($site, 0, strpos($site, '/'));
    }
    $host = str_replace(':80', '', httpGetServer('HTTP_HOST'));

    if ($site != $host)
    {
        $sql = 'SELECT * FROM '.DB::prefix('referers')." WHERE uri='{httpGetServer('HTTP_REFERER')}'";
        $result = DB::query($sql);
        $row = DB::fetch_assoc($result);
        DB::free_result($result);

        if ($row['refererid'] > '')
        {
            $sql = 'UPDATE '.DB::prefix('referers')." SET count=count+1,last='".date('Y-m-d H:i:s')."',site='".addslashes($site)."',dest='".addslashes($host).'/'.addslashes(httpGetServer('REQUEST_URI'))."',ip='".httpGetServer('REMOTE_ADDR')."' WHERE refererid='{$row['refererid']}'";
        }
        else
        {
            $sql = 'INSERT INTO '.DB::prefix('referers')." (uri,count,last,site,dest,ip) VALUES ('{httpGetServer('HTTP_REFERER')}',1,'".date('Y-m-d H:i:s')."','".addslashes($site)."','".addslashes($host).'/'.addslashes(httpGetServer('REQUEST_URI'))."','".httpGetServer('REMOTE_ADDR')."')";
        }
        DB::query($sql);
    }
}

$session['user']['superuser'] = $session['user']['superuser'] ?? 0;

//check the account's hash to detect player cheats which
// we don't catch elsewhere.
include_once 'lib/common.php';

$session['user']['hashorse'] = $session['user']['hashorse'] ?? 0;
$playermount = getmount($session['user']['hashorse']);

$temp_comp = $session['user']['companions'] ?? [];
$companions = [];

if (! empty($temp_comp))
{
    foreach ($temp_comp as $name => $companion)
    {
        if (is_array($companion))
        {
            $companions[$name] = $companion;
        }
    }
}
unset($temp_comp);

$beta = getsetting('beta', 0);

if (! $beta && 1 == getsetting('betaperplayer', 1))
{
    $beta = $session['user']['beta'] ?? 0;
}

if (isset($session['user']['clanid']))
{
    $sql = 'SELECT * FROM '.DB::prefix('clans')." WHERE clanid='{$session['user']['clanid']}'";
    $result = DB::query($sql);

    $claninfo = [];
    if ($result->count() > 0)
    {
        $claninfo = $result->current();
    }
    else
    {
        $session['user']['clanid'] = 0;
        $session['user']['clanrank'] = 0;
    }
}
else
{
    $claninfo = [];
    $session['user']['clanid'] = 0;
    $session['user']['clanrank'] = 0;
}

if ($session['user']['superuser'] & SU_MEGAUSER)
{
    $session['user']['superuser'] = $session['user']['superuser'] | SU_EDIT_USERS;
}

translator_setup();
//set up the error handler after the intial setup (since it does require a
//db call for notification)
//-- Not is used
// require_once 'lib/errorhandler.php';

if (getsetting('debug', 0))
{
    //Server runs in Debug mode, tell the superuser about it
    if (SU_EDIT_CONFIG == ($session['user']['superuser'] & SU_EDIT_CONFIG))
    {
        tlschema('debug');
        output('<center>`$<h2>SERVER RUNNING IN DEBUG MODE</h2></center>`n`n', true);
        tlschema();
    }
}

// WARNING:
// do not hook on these modulehooks unless you really need your module to run
// on every single page hit.  This is called even when the user is not
// logged in!!!
// This however is the only context where blockmodule can be called safely!
// You should do as LITTLE as possible here and consider if you can hook on
// a page header instead.
modulehook('everyhit');

if ($session['user']['loggedin'])
{
    modulehook('everyhit-loggedin');
}

// This bit of code checks the current system load, so that high-intensity operations can be disabled or postponed during times of exceptionally high load.  Since checking system load can in itself be resource intensive, we'll only check system load once per thirty seconds, checking it against time retrieved from the database at the first load of getsetting().
global $fiveminuteload;
$lastcheck = getsetting('systemload_lastcheck', 0);
$fiveminuteload = getsetting('systemload_lastload', 0);
$currenttime = time();

if ($currenttime - $lastcheck > 30)
{
    $load = exec('uptime'); //-- Only work in Linux systems
    if ($load)
    {
        $load = explode('load average:', $load);
        $load = explode(', ', $load[1]);
        $fiveminuteload = $load[1];
        savesetting('systemload_lastload', $fiveminuteload);
        savesetting('systemload_lastcheck', $currenttime);
    }
}
