<?php

class autoload
{

    private static function updatePhpFiles($dir_level)
    {
        /**Get all files and directory using iterator.*/
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir_level));
        $filePaths = array();

        /**Get all php files in this directory level.*/
        foreach ($iterator as $path) {
            if (is_string(strval($path)) and pathinfo($path, PATHINFO_EXTENSION) == 'php') {
                $filePaths[] = strval($path);
            }
        }

        /**Encode and save php files dir in a local json file */
        return json_encode($filePaths);

    }

    private static function includeMatchingFiles($dir_level, $class_file_name)
    {

        $files = json_decode(self::updatePhpFiles($dir_level), true);
        $inc_is_done = false;

        /**Include matching files here.*/
        foreach ($files as $path) {

            if (stripos($class_file_name, basename($path))) {
                require_once $path;
                $inc_is_done = true;
            }
        }
        return $inc_is_done;
    }

    public static function include_system_files($dir_level, $class_name)
    {
        $class_file_name = $class_name . '.php';

        return self::includeMatchingFiles($dir_level, $class_file_name);

    }

}

/**
 * Register autoloader.
 */
spl_autoload_register(function ($className) {
    autoload::include_system_files(__DIR__ . '/app', $className);
});

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/bootstrap.php';

