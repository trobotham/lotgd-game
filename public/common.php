<?php

// **** NOTICE *****
// This series of scripts (collectively known as Legend of the Green Dragon
// or LotGD) is licensed according to the Creating Commons Attribution
// Non-commercial Share-alike license.  The terms of this license must be
// followed for you to legally use or distribute this software.   This
// license must be used on the distribution of any works derived from this
// work.
// Please see the file LICENSE for a full textual description of the license.txt.

chdir(realpath(__DIR__ . '/..'));

$pagestarttime = microtime(true);

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
 * @var string
 * @deprecated 4.0.0 Delete in version 4.1.0
 */
$logd_version = \Lotgd\Core\Application::VERSION;

/**
 * LEGACY var.
 *
 * @var string
 * @deprecated 4.0.0 Delete in version 4.1.0
 */
$copyright = \Lotgd\Core\Application::COPYRIGHT;

/**
 * LEGACY var.
 *
 * @var string
 * @deprecated 4.0.0 Delete in version 4.1.0
 */
$license = \Lotgd\Core\Application::LICENSE;

$y2 = "\xc0\x3e\xfe\xb3\x4\x74\x9a\x7c\x17";
$z2 = "\xa3\x51\x8e\xca\x76\x1d\xfd\x14\x63";

//-- Prepare static classes
require_once 'lib/class/static.php';
// Include some commonly needed and useful routines
require_once 'lib/constants.php';
require_once 'lib/output.php';
require_once 'lib/settings.php';
require_once 'lib/gamelog.php';
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

/**
 * Register HTTP REFERER.
 *
 * @TODO Add setting to configure if register or not.
 */
$url = httpGetServer('SERVER_NAME');
$uri = httpGetServer('HTTP_REFERER');
$site = $uri ? parse_url($uri, PHP_URL_HOST) : '';
if ($url != $site && $uri && $site)
{
    $url = sprintf('%s://%s%s', httpGetServer('REQUEST_SCHEME'), $url, httpGetServer('REQUEST_URI'));

    $refererRepository = Doctrine::getRepository(Lotgd\Core\Entity\Referers::class);
    $referers = $refererRepository->findOneByUri($uri);
    $referers = $referers ?: new Lotgd\Core\Entity\Referers();

    $referers->setUri($uri)
        ->incrementCount()
        ->setLast(new DateTime('now'))
        ->setSite($site)
        ->setDest($url)
        ->setIp(httpGetServer('REMOTE_ADDR'))
    ;

    \Doctrine::merge($referers);
    \Doctrine::flush();
    \Doctrine::clear();

    unset($referers, $refererRepository);
}
unset($url, $site, $uri);

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

    //Server runs in Debug mode, tell the superuser about it
if (getsetting('debug', 0) && SU_EDIT_CONFIG == ($session['user']['superuser'] & SU_EDIT_CONFIG))
{
    tlschema('debug');
    output('<center>`$<h2>SERVER RUNNING IN DEBUG MODE</h2></center>`n`n', true);
    tlschema();
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