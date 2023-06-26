<?php
declare(strict_types=1);

namespace PicturaeInstaller\App;

use Symfony\Component\Filesystem\Filesystem;

final class Backup
{
    public static string $backupFolder = './backup';
    public static string $templateName;

    public function __construct()
    {
        self::$templateName = getenv('TEMPLATE_TARGET');
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

    public static function template()
    {
        if(!file_exists(self::$backupFolder) && !is_dir(self::$backupFolder)) {
            self::init();
        }

        var_dump('Ready to backup the template file.'); exit;
    }

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
