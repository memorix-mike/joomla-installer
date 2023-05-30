<?php
declare(strict_types=1);

namespace PicturaeInstaller\App;

use Dotenv\Dotenv;
use ZipArchive;

/**
 * Enums with versions that should be available within the installation
 */
enum Versions: string {
    case Latest     = '4.3.1';
}

/**
 *
 */
enum Status: string {
    case UPDATE = 'update';
    case INSTALL = 'install';
}

class Install
{
    public function __construct()
    {
        var_dump('This is a test.'); exit;

        // Load Dotenv for environmental variables
        Dotenv::createUnsafeImmutable('./')->load();
    }

    /**
     * Define all possible versions of Joomla (from Enums)
     *
     * @return array
     */
    public static function versions(): array
    {
        $versions = [];
        foreach(Versions::cases() as $version) {
            $versions[] = $version->value;
        }

        return $versions;
    }

    /**
     * Get the version Url based on the selected version
     *
     * @param $version
     * @return string
     */
    private static function versionUrl($version)
    {
        switch($version) {
            case Versions::Latest->value:
            default:
                return 'https://downloads.joomla.org/cms/joomla4/4-3-1/Joomla_4-3-1-Stable-Full_Package.zip';
        }
    }

    /**
     * Check if this is a new installation or an update
     *
     * @return \Status
     */
    public static function check(): string
    {
        if(file_exists('./installation/configuration.php')) {
            return Status::UPDATE->value;
        }

        return Status::INSTALL->value;
    }

    /**
     * Download a selected version of Joomla from the Joomla website
     *
     * @param $version
     * @return string
     */
    public static function download($version = null)
    {
        $source         = self::versionUrl($version);
        $destination    = 'joomla-' . $version . '.zip';

        file_put_contents($destination, file_get_contents($source));

        return $destination;
    }

    /**
     * Unzip the downloaded version of Joomla
     *
     * @param string $file
     * @return bool
     */
    public static function unzip(string $file)
    {
        $zip = new ZipArchive;
        if ($zip->open($file) === TRUE) {

            // Remove the old installation folder if present0
            system('rm -rf -- ' . escapeshellarg('./installation'));

            $zip->extractTo('./installation/');
            $zip->close();

            return true;
        }

        return false;
    }

    /**
     * The installation command with pre-filled arguments
     *
     * @return string
     */
    public static function install()
    {
        $args = '';
        foreach(self::installArguments() as $key => $value) {
            $args .= ' ' . $key . '=' . $value;
        }

        return 'cd ./installation && php installation/joomla.php install' . $args;
    }

    /**
     * Define the installation arguments
     *
     * @return array
     */
    public static function installArguments(): array
    {

        return [
            '--site-name'       => getenv('SITE_NAME'),
            '--admin-user'      => getenv('ADMIN_USER'),
            '--admin-username'  => getenv('ADMIN_USERNAME'),
            '--admin-password'  => getenv('ADMIN_PASSWORD'),
            '--admin-email'     => getenv('ADMIN_EMAIL'),
            '--db-type'         => getenv('DB_TYPE'),
            '--db-host'         => getenv('DB_HOST'),
            '--db-user'         => getenv('DB_USER'),
            '--db-pass'         => getenv('DB_PASS'),
            '--db-name'         => getenv('DB_NAME'),
            '--db-prefix'       => getenv('DB_PREFIX'),
            '--db-encryption'   => getenv('DB_ENCRYPTION'),
        ];
    }

    public static function checkForUpdates()
    {
        return 'php ./installation/cli/joomla.php core:check-updates';
    }


    public static function update()
    {
        return 'php ./installation/cli/joomla.php core:update';
    }

    public static function move()
    {
        dd('moved files');
        return '';
    }

}
