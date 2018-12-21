<?php

namespace Lotgd\Core\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SettingsExtended.
 *
 * @ORM\Table(name="settings_extended")
 * @ORM\Entity
 */
class SettingsExtended
{
    /**
     * @var string
     *
     * @ORM\Column(name="setting", type="string", length=50)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $setting;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text", length=65535, nullable=false)
     */
    private $value;

    /**
     * Set the value of Setting.
     *
     * @param string setting
     *
     * @return self
     */
    public function setSetting($setting)
    {
        $this->setting = $setting;

        return $this;
    }

    /**
     * Get the value of Setting.
     *
     * @return string
     */
    public function getSetting(): string
    {
        return $this->setting;
    }

    /**
     * Set the value of Value.
     *
     * @param string value
     *
     * @return self
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the value of Value.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}