<?php
declare(strict_types=1);

namespace PicturaeInstaller\App;

class Env
{
    public static string $projectPath;

    /**
     * Check if there is a .env file in this project
     *
     * @return bool
     */
    public static function exists(): bool
    {
        self::$projectPath = getcwd() . '/';
        if(!file_exists(self::$projectPath . '.env')) {
            return false;
        }
        return true;
    }

    /**
     * Create a .env file based on the .env.example
     *
     * @return bool
     */
    public static function create(): bool
    {
        self::$projectPath = getcwd() . '/';
        if(!copy(self::$projectPath . '.env.example',
                   self::$projectPath . '.env')) {
            return false;
        }
        return true;
    }
}
