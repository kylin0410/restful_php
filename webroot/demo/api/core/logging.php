<?php

/**
 * Simple logging class leverage error_log PHP function.
 */
class Logging
{
    private static $LOG_LEVEL = 0;
    private static $SQL_LOG_FILE = "_sql.log";
    private static $DEBUG_LOG_FILE = "_debug.log";
    private static $INFO_LOG_FILE = "_info.log";
    private static $WARN_LOG_FILE = "_warn.log";
    private static $ERROR_LOG_FILE = "_error.log";
    private static $FATAL_LOG_FILE = "_fatal.log";

    private static function logMessage($caller, $message, $logfile)
    {
        list($file, $line, $function) = array($caller["file"], $caller["line"], $caller["function"]);
        $now = date("Y-m-d H:i:s");  // Use local time.
        $log = "[$now]@$file:$line($function)\n" . $message;
        error_log($log . "\n\n", 3, $logfile);
    }

    private static function logFilePrefix()
    {
        $folder = Config::$LOG_PATH;
        $appName = ApiUtil::getAppName();
        return "$folder/$appName";
    }

    public static function debugSql($sql)
    {
        if (Logging::$LOG_LEVEL > 0) { // The same to debug level log.
            return;
        }
        $logfile = static::logFilePrefix() . static::$SQL_LOG_FILE;
        $caller = debug_backtrace()[0];
        static::logMessage($caller, $sql, $logfile);
    }

    public static function debug($message)
    {
        if (Logging::$LOG_LEVEL > 0) {
            return;
        }
        $logfile = static::logFilePrefix() . static::$DEBUG_LOG_FILE;
        $caller = debug_backtrace()[0];
        static::logMessage($caller, $message, $logfile);
    }

    public static function info($message)
    {
        if (Logging::$LOG_LEVEL > 1) {
            return;
        }
        $logfile = static::logFilePrefix() . static::$INFO_LOG_FILE;
        $caller = debug_backtrace()[0];
        static::logMessage($caller, $message, $logfile);
    }

    public static function warn($message)
    {
        if (Logging::$LOG_LEVEL > 2) {
            return;
        }
        $logfile = static::logFilePrefix() . static::$WARN_LOG_FILE;
        $caller = debug_backtrace()[0];
        static::logMessage($caller, $message, $logfile);
    }

    public static function error($message)
    {
        if (Logging::$LOG_LEVEL > 3) {
            return;
        }
        $logfile = static::logFilePrefix() . static::$ERROR_LOG_FILE;
        $caller = debug_backtrace()[0];
        static::logMessage($caller, $message, $logfile);
    }

    public static function fatal($message)
    {
        if (Logging::$LOG_LEVEL > 4) {
            return;
        }
        $logfile = static::logFilePrefix() . static::$FATAL_LOG_FILE;
        $caller = debug_backtrace()[0];
        static::logMessage($caller, $message, $logfile);
    }
}
