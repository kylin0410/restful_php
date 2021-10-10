<?php

/**
 * Config class that manages web api configuration.
 */
class Config
{
    // Static path definition.
    public static $CONF_PATH = "/workspace/conf";
    public static $DATA_PATH = "/workspace/data";
    public static $LOG_PATH = "/var/log/apache2";

    // Config key definition.
    public static $KEY_APIDOC = "apidoc";
    public static $KEY_AUTH = "auth";
    public static $KEY_MYSQL = "mysql";

    // Config content.
    private static $CONFIG = array();

    /**
     * Load config from file. Return true if file content is valid, or return false.
     */
    public static function initConfig()
    {
        // Check config file.
        $appName = ApiUtil::getAppName();
        $confPath = Config::$CONF_PATH;
        $configFile =  "$confPath/$appName/config.json";
        if (!file_exists($configFile) || !filesize($configFile)) {
            throw new Exception("Config file is absent or empty.");
        }

        // Return false is content is invalid.
        $jsonStr = file_get_contents($configFile);
        $config = json_decode($jsonStr, true);
        if ($config === null) {
            throw new Exception("Config file format is invalid.");
        }
        static::$CONFIG = $config;
    }

    public static function getConfig($key)
    {
        if (!isset(static::$CONFIG[$key])) {
            throw new Exception("Configuration error. [$key] is absent.");
        }
        return static::$CONFIG[$key];
    }
}
