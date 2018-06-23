<?php

class Setting
{
    private static $instances = array();
    private $settings;

    private function __construct($ini_file)
    {
        $this->settings = parse_ini_file($ini_file, true);
    }

    public static function &getInstance($ini_file, $errorPrint = false)
    {
        if ($ini_file == null || $ini_file == "") {
            if ($errorPrint) {
                echo "Settings File Parameter Null!";
            }
            return null;
        } else if (!file_exists($ini_file)) {
            if ($errorPrint) {
                echo "Settings File Not Exists!";
            }
            return null;
        } else if (!is_readable($ini_file)) {
            if ($errorPrint) {
                echo "Settings File Not Readable!";
            }
            return null;
        }
        $basename = basename($ini_file);
        if (!isset(self::$instances[md5($ini_file) . "_" . $basename])) {
            self::$instances[md5($ini_file) . "_" . $basename] = new Setting($ini_file);
        }
        return self::$instances[md5($ini_file) . "_" . $basename];
    }

    public function __get($setting)
    {
        if (array_key_exists($setting, $this->settings)) {
            return $this->settings[$setting];
        } else {
            foreach ($this->settings as $section) {
                if (array_key_exists($setting, $section)) {
                    return $section[$setting];
                }
            }
        }

        return null;
    }

    public function getAllByPrefix($key_prefix)
    {
        $allFound = array();
        foreach ($this->settings as $key => $section) {
            if (stripos($key, $key_prefix) === 0) {
                $allFound[$key] = $this->settings[$key];
            }
        }

        return count($allFound) > 0 ? $allFound : null;
    }
} // end class 
