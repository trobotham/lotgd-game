<?php

define('OVERRIDE_FORCED_NAV', true);

//-- Allow anonymous conections to Jaxon functions
//-- This avoid some errors and allow to use with not registers users.
define('ALLOW_ANONYMOUS', true);

require_once 'common_jaxon.php';

$jaxon = \LotgdLocator::get(Lotgd\Core\Jaxon::class);

if($jaxon->canProcessRequest())
{
    $jaxon->processRequest();

    saveuser();

    $jaxon->sendResponse();

    exit;
}
