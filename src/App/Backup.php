<?php
declare(strict_types=1);

namespace PicturaeInstaller\App;

use Symfony\Component\Filesystem\Filesystem;
use mysqli;

final class Backup
{
    protected static mysqli $connection;
    private static string $tablePrefix;
    private static string $host;
    private static string $user;
    private static string $password;
    private static string $database;
    public static string $templateName;
    public static string $backupFolder = './backup';

    public function __construct()
    {
        self::$templateName = getenv('TEMPLATE_TARGET');

        self::$connection = new \mysqli(
            getenv('DB_HOST'),
            getenv('DB_USER'),
            getenv('DB_PASS'),
            getenv('DB_NAME'),
        );

        self::$tablePrefix  = getenv('DB_PREFIX');
        self::$host         = getenv('DB_HOST');
        self::$user         = getenv('DB_USER');
        self::$password     = getenv('DB_PASS');
        self::$database     = getenv('DB_NAME');
    }

    /**
     * Create a backup folder if not yet present
     *
     * @return void
     */
    public static function init(): void
    {
        mkdir(self::$backupFolder, 0755);
    }

    /**
     * Configuration backup
     *
     * @return true
     */
    public static function configuration()
    {
        if(!file_exists(self::$backupFolder) && !is_dir(self::$backupFolder)) {
            self::init();
        }

        $filesystem = new Filesystem();
        $filesystem->copy(
            self::$templateName . '/configuration.php',
            self::$backupFolder . '/configuration.php'
        );

        return true;
    }

    /**
     * Template backup
     *
     * @return true
     */
    public static function template()
    {
        if(!file_exists(self::$backupFolder) && !is_dir(self::$backupFolder)) {
            self::init();
        }

        return true;
    }

    /**
     * Restore the configuration file
     *
     * @return true
     */
    public static function restoreConfiguration()
    {
        $filesystem = new Filesystem();
        $filesystem->copy(
            self::$backupFolder . '/configuration.php',
            self::$templateName . '/configuration.php',
        );

        return true;
    }
}
