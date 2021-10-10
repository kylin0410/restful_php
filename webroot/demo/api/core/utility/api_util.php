<?php

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Utility function for WebAPI.
 */
class ApiUtil
{
    /**
     * List PHP file recursively under given folder.
     */
    public static function listPhpFiles($dir, &$results = array())
    {
        $files = scandir($dir);
        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                if (strtolower($ext) == "php") {
                    $results[] = $path;
                }
            } elseif ($value != "." && $value != "..") {
                ApiUtil::listPhpFiles($path, $results);
                // $results[] = $path; // Do not need to put dir.
            }
        }
        return $results;
    }

    public static function mapMime($filename)
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (strtolower($ext) === "jpg" || strtolower($ext) === "jpeg") {
            return "image/jpeg";
        }
        if (strtolower($ext) === "ini") {
            return "text/plain";
        }
        return "application/octet-stream";
    }

    /**
     * Get API route path from http request path.
     * @example: "/api/document?page=1"
     */
    public static function getApiPath()
    {
        $array = explode("/api/", $_SERVER['REQUEST_URI']);
        if (count($array) != 2) {
            throw new Exception("Invalid request API path format.");
        }
        $apiPath = $array[1];
        return "/api/$apiPath";
    }

    /**
     * Get web application name from http request path. Exclude slash in first character.
     * @example "docapture"
     */
    public static function getAppName()
    {
        $array = explode("/api/", $_SERVER['REQUEST_URI']);
        if (count($array) != 2) {
            throw new Exception("Invalid request API path format.");
        }
        $appName = $array[0];

        // Remove first ? character.
        if (!empty($appName)) {
            $first = substr($appName, 0, 1);
            if ($first === "/") {
                $appName = substr($appName, 1);
            }
        }
        return $appName;
    }
}
