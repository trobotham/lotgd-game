<?php

class settings
{
	protected $tablename;
    protected $settings = [];
    protected $settingsKey = 'game-settings-';

	public function __construct($tablename = false)
	{
		if ($tablename === false) $tablename = DB::prefix('settings');
		else $tablename = DB::prefix($tablename);

		$this->tablename = $tablename;
		$this->settings = [];
		$this->loadSettings();
	}

    /**
     * Save setting in to Data Base
     *
     * @param string $settingname
     * @param mixed $value
     *
     * @return bool
     */
	public function saveSetting($settingname, $value)
    {
        if (defined('DB_NODB') && ! defined('LINK')) return false;

        $key = $this->getCacheKey();
        $settingname = (string) $settingname;
        $settings = $this->getAllSettings();

        //-- To ensure that a new record is inserted or the existing one is updated
        $sql = sprintf("REPLACE INTO `%s` (`setting`, `value`) VALUES (%s, %s)", $this->tablename, DB::quoteValue($settingname), DB::quoteValue($value));
        DB::query($sql);

        $settings[$settingname] = $value;

        updatedatacache($key, $settings, true);

        $this->settings = $settings;

		if (DB::affected_rows() > 0) return true;
		else return false;
	}

    /**
     * Load all settings in table
     *
     * @return void
     */
	public function loadSettings()
	{
        if (defined('DB_NODB') && ! defined('LINK')) return false;

        $key = $this->getCacheKey();
        $this->settings = datacache($key, 86400, true);

		if (! is_array($this->settings) || empty($this->settings))
		{
			try
            {
                $this->settings = [];
                $select = DB::select($this->tablename);
                $result = DB::execute($select);

                if (! $result->count()) return;

                foreach($result as $row)
                {
                    $this->settings[$row['setting']] = $row['value'];
                }
                updatedatacache($key, $this->settings, true);
            }
            catch( \Exception $ex)
            {
                debug('Cant get Settings.');
            }
		}
	}

    /**
     * Force to reload all settings
     *
     * @return void
     */
    public function clearSettings()
    {
		//scraps the $this->loadSettings() data to force it to reload.
		invalidatedatacache($this->settingsKey . $this->tablename, true);
		$this->settings = [];
	}

    /**
     * Get a value of a setting
     *
     * @param string $settingname
     * @param string|false $default
     *
     * @return string
     */
	public function getSetting($settingname, $default = false)
    {
		global $DB_USEDATACACHE,$DB_DATACACHEPATH;

		if ($settingname == 'usedatacache') return $DB_USEDATACACHE;
		elseif ($settingname == 'datacachepath') return $DB_DATACACHEPATH;

        if (! is_array($this->settings) || ('object' == gettype($this) && !isset($this->settings[$settingname])) )
        {
			$this->loadSettings();
		}

		if (! isset($this->settings[$settingname]))
        {
			//nothing set, we have to use the default value
			if (file_exists("lib/data/{$this->tablename}.php")) { require "lib/data/{$this->tablename}.php"; }
			if ($default === false)
            {
				if (isset($defaults[$settingname])) $setDefault = $defaults[$settingname];
				else $setDefault = '';
			}
            else $setDefault = $default;

            $this->saveSetting($settingname, $setDefault);

			return $setDefault;
		}
        else
        {
			return $this->settings[$settingname];
		}
    }

    /**
     * Get all settings of game
     *
     * @return array
     */
    public function getAllSettings()
    {
		if (! is_array($this->settings) || empty($this->settings))
		{
			$this->loadSettings();
        }

        return $this->settings;
    }

    /**
     * Get key of cache
     *
     * @return string
     */
    private function getCacheKey()
    {
        return $this->settingsKey . $this->tablename;
    }

    /**
     * Alias of getAllSettings()
     *
     * @return array
     */
	public function getArray() { return $this->getAllSettings(); }
}
