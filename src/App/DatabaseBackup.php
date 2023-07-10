<?php
declare(strict_types=1);

namespace PicturaeInstaller\App;

use mysqli;
use Ifsnop\Mysqldump\Mysqldump;

final class DatabaseBackup
{
    protected static mysqli $connection;
    private static string $host;
    private static string $database;
    private static string $user;
    private static string $password;
    protected static string $tablePrefix;

    public static string $location = './backup/dump.sql';

    public function __construct()
    {
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

    public static function check(): bool
    {
        $extensions = self::$connection->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . self::$database . "'");
        if(mysqli_num_rows($extensions)) {
            return true;
        }

        return false;
    }

    /**
     * Create a Mysql-dump of the current database
     *
     * @return bool|string
     */
    public static function dump(): bool|string
    {
        try {
            $dump = new Mysqldump(
                'mysql:host=' . self::$host . ';dbname=' . self::$database,
                self::$user,
                self::$password
            );
            $dump->start(self::$location);
            return true;

        } catch (\Exception $exception) {
            return 'mysqldump-php error: ' . $exception->getMessage();
        }
    }

    /**
     * Database fix due to upgrade columns missing
     *
     * @return true
     */
    public static function fix()
    {
        $extensions = self::$connection->query("SHOW COLUMNS FROM `" . self::$tablePrefix . "extensions` LIKE 'custom_data'");
        if(!mysqli_num_rows($extensions)) {
            self::$connection->query("ALTER TABLE `" . self::$tablePrefix . "extensions` ADD COLUMN `custom_data` text NOT NULL;");
        }

        $template_inheritable = self::$connection->query("SHOW COLUMNS FROM `" . self::$tablePrefix . "template_styles` LIKE 'inheritable'");
        if(!mysqli_num_rows($template_inheritable)) {
            self::$connection->query("ALTER TABLE `" . self::$tablePrefix . "template_styles` ADD COLUMN `inheritable` tinyint(1) NOT NULL DEFAULT 0;");
        }

        $template_parent = self::$connection->query("SHOW COLUMNS FROM `" . self::$tablePrefix . "template_styles` LIKE 'parent'");
        if(!mysqli_num_rows($template_parent)) {
            self::$connection->query("ALTER TABLE `" . self::$tablePrefix . "template_styles` ADD COLUMN `parent` varchar(50) DEFAULT '';");
        }

        return true;
    }
}
