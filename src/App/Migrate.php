<?php
declare(strict_types=1);

namespace PicturaeInstaller\App;

use mysqli;
use Ifsnop\Mysqldump\Mysqldump;

use PicturaeInstaller\Command\Environments;

final class Migrate
{
    protected static mysqli $targetConnection;
    public static string $migrationFolder = './migration';

    /**
     * @return void
     */
    public static function init()
    {
        mkdir(self::$migrationFolder, 0755);
    }

    /**
     * @param string $environment
     * @return string[]
     */
    protected static function variables(string $environment): array
    {
        if($environment == Environments::DEVELOPMENT->value) {
            return [
                'host'  => getEnv('DB_HOST'),
                'user'  => getEnv('DB_USER'),
                'pass'  => getEnv('DB_PASS'),
                'name'  => getEnv('DB_NAME'),
            ];
        }
        else {
            return [
                'host'  => getEnv('DB_' . strtoupper($environment) . '_HOST'),
                'user'  => getEnv('DB_' . strtoupper($environment) . '_USER'),
                'pass'  => getEnv('DB_' . strtoupper($environment) . '_PASS'),
                'name'  => getEnv('DB_' . strtoupper($environment) . '_NAME')
            ];
        }
    }

    /**
     * Create a Mysql-dump of the current database
     *
     * @param string $environment
     * @return string
     */
    public static function dump(string $environment): string
    {
        if(!file_exists(self::$migrationFolder) && !is_dir(self::$migrationFolder)) {
            self::init();
        }

        $variables = self::variables($environment);
        $location = './migration/dump-' . $environment . '.sql';

        try {
            $dump = new Mysqldump(
                'mysql:host=' . $variables['host'] . ';dbname=' . $variables['name'],
                $variables['user'],
                $variables['pass']
            );
            $dump->start($location);

            return $location;
        } catch (\Exception $exception) {
            return 'mysqldump-php error: ' . $exception->getMessage();
        }
    }

    /**
     * Import a Mysql-dump
     *
     * @param string $dump
     * @param string $environment
     * @return bool|string
     */
    public static function run(string $dump, string $environment): bool|string
    {
        if(!file_exists($dump)) {
            return 'Dump could not be found at' . $dump;
        }

        $variables = self::variables($environment);

        self::$targetConnection = new \mysqli(
            $variables['host'],
            $variables['user'],
            $variables['pass'],
            $variables['name']
        );

        if(self::$targetConnection->multi_query(file_get_contents($dump))) {
            return self::removeDump($dump);
        }

        return false;
    }

    /**
     * Remove the dump-file and migration-folder
     *
     * @param string $dump
     * @return bool|string
     */
    public static function removeDump(string $dump): bool|string
    {
        if(!file_exists($dump)) {
            return 'Dump could not be found at' . $dump;
        }

        // Remove the file
        if(unlink($dump)) {

            // Remove the folder
            if(rmdir(self::$migrationFolder)) {
                return true;
            }

            return 'Could not remove the migration-folder';
        }

        return false;
    }
}
