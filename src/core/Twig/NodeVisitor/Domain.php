<?php

/**
 * This file is part of Legend of the Green Dragon.
 *
 * @see https://github.com/idmarinas/lotgd-game
 *
 * @license https://github.com/idmarinas/lotgd-game/blob/master/LICENSE.txt
 * @author IDMarinas
 *
 * @since 4.0.0
 */

namespace Lotgd\Core\Twig\NodeVisitor;

class Domain
{
    private $parent;
    private $data = [];
    private $left = false;

    public function __construct(self $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * Opens a new child domain.
     *
     * @return self
     */
    public function enter()
    {
        return new self($this);
    }

    /**
     * Closes current domain and returns parent one.
     *
     * @return self|null
     */
    public function leave()
    {
        $this->left = true;

        return $this->parent;
    }

    /**
     * Stores data into current domain.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     *
     * @throws \LogicException
     */
    public function set($key, $value)
    {
        if ($this->left)
        {
            throw new \LogicException('Left domain.');
        }

        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Tests if a data is visible from current domain.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        if (array_key_exists($key, $this->data))
        {
            return true;
        }

        if (null === $this->parent)
        {
            return false;
        }

        return $this->parent->has($key);
    }

    /**
     * Returns data visible from current domain.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (array_key_exists($key, $this->data))
        {
            return $this->data[$key];
        }

        if (null === $this->parent)
        {
            return $default;
        }

        return $this->parent->get($key, $default);
    }
}
