<?php

namespace Lotgd\Ajax\Core;

class Timeout
{
    public function status()
    {
        global $session;

        $response = new \Jaxon\Response\Response();

        //-- Do nothing if there is no active session
        if (! $session['user']['loggedin'])
        {
            return $response;
        }

        $timeout = strtotime($session['user']['laston']) - strtotime(date('Y-m-d H:i:s', strtotime('-'.getsetting('LOGINTIMEOUT', 900).' seconds')));

        if ($timeout <= 1)
        {
            $text = translate_inline('Your session has timed out!');
            $warning = '<b>'.$text.'</b>';
        }
        elseif ($timeout < 120)
        {
            $text = translate_inline('TIMEOUT in %s seconds!');
            $warning = sprintf($text, $timeout);
        }
        else
        {
            return $response;
        }

        $response->dialog->warning($warning);

        return $response;
    }
}