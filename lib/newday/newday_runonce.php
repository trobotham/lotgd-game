<?php

//newday runonce

//Let's do a new day operation that will only fire off for
//one user on the whole server.
//run the hook.
modulehook('newday-runonce', []);

//only if not done by cron
if (! getsetting('newdaycron', 0))
{
    require_once 'lib/gamelog.php';

    //Do some high-load-cleanup
    if (LotgdCache::clearExpired())
    {
        gamelog('Expired cache data has been deleted', 'maintenance');
    }

    if (LotgdCache::optimize())
    {
        gamelog('Cache data has been optimized', 'maintenance');
    }

    require 'lib/newday/commentcleanup.php';
    require 'lib/newday/charcleanup.php';
    require 'lib/newday/logoutaccts.php';
    require 'lib/newday/petition_cleanup.php';
}
