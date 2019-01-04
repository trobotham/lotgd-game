<?php

// translator ready
// addnews ready
// mail ready

/**
 * Return single get parameter.
 *
 * @param string $name
 * @param string $default
 *
 * @return mixed
 */
function httpget(string $name, string $default = null)
{
    return \LotgdLocator::get(\Lotgd\Core\Http::class)->getQuery($name, $default);
}

/**
 * Return all get parameters.
 *
 * @param bool $array For get or not data in array format
 *
 * @return array|object
 */
function httpallget(bool $array = true)
{
    if ($array)
    {
        return \LotgdLocator::get(\Lotgd\Core\Http::class)->getQuery()->toArray();
    }

    return \LotgdLocator::get(\Lotgd\Core\Http::class)->getQuery();
}

/**
 * Set single get parameter.
 *
 * @param string $var
 * @param mixed  $val
 * @param bool   $force
 */
function httpset(string $var, $val, bool $force = false)
{
    $get = \LotgdLocator::get(\Lotgd\Core\Http::class)->getQuery();

    if ($get->offsetExists($var) || $force)
    {
        $get->set($var, $val);
    }
}

/**
 * Return single post parameter.
 *
 * @param string $name
 * @param mixed  $default
 *
 * @return mixed
 */
function httppost($name, $default = null)
{
    return \LotgdLocator::get(\Lotgd\Core\Http::class)->getPost($name, $default);
}

function httppostisset($var)
{
    return \LotgdLocator::get(\Lotgd\Core\Http::class)->getPost()->offsetExists($var);
}

/**
 * Set single post parameter.
 *
 * @param string $var
 * @param mixed  $val
 * @param bool   $sub
 */
function httppostset($var, $val, $sub = false)
{
    $post = \LotgdLocator::get(\Lotgd\Core\Http::class)->getPost();

    if (false === $sub && $post->offsetExists($var))
    {
        $post->set($var, $val);
    }
    elseif (isset($_POST[$var]) && isset($_POST[$var][$sub]))
    {
        $_POST[$var][$sub] = $val;

        \LotgdLocator::get(\Lotgd\Core\Http::class)->setPost($_POST);
    }
}

/**
 * Get all post data.
 *
 * @param bool $array For get or not data in array format
 *
 * @return array|object
 */
function httpallpost($array = true)
{
    if ($array)
    {
        return \LotgdLocator::get(\Lotgd\Core\Http::class)->getPost()->toArray();
    }

    return \LotgdLocator::get(\Lotgd\Core\Http::class)->getPost();
}

function postparse($verify = false, $subval = false)
{
    $var = httpallpost();

    if ($subval)
    {
        $var = httppost($subval);
    }

    reset($var);
    $sql = '';
    $keys = '';
    $vals = '';
    $i = 0;

    foreach ($var as $key => $val)
    {
        if (false === $verify || isset($verify[$key]))
        {
            if (is_array($val))
            {
                $val = addslashes(serialize($val));
            }
            $sql .= (($i > 0) ? ',' : '')."$key='$val'";
            $keys .= (($i > 0) ? ',' : '')."$key";
            $vals .= (($i > 0) ? ',' : '')."'$val'";
            $i++;
        }
    }

    return [$sql, $keys, $vals];
}

/**
 * Get value for a server key. Access to $_SERVER var.
 *
 * @param string|null $name
 * @param string|null $default
 *
 * @return string
 */
function httpGetServer($name = null, $default = null)
{
    $request = \LotgdLocator::get(\Lotgd\Core\Http::class);

    return $request->getServer($name, $default);
}

/**
 * Return base url of game.
 *
 * @param false|string $file
 *
 * @return string
 */
function lotgd_base_url($file = false)
{
    $basename = (! $file ? basename(httpGetServer('SCRIPT_NAME')) : $file);

    if ($basename)
    {
        $path = (httpGetServer('PHP_SELF') ? trim(httpGetServer('PHP_SELF'), '/') : '');
        $basePos = strpos($path, $basename) ?: 0;
        $baseUrl = substr($path, 0, $basePos);
    }

    return  $baseUrl;
}
