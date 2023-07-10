<?php
declare(strict_types=1);

namespace PicturaeInstaller\App;

use Dotenv\Dotenv;
use ZipArchive;
use mysqli;

/**
 * Version enums
 */
enum Versions: string {
    case Latest         = '4.3.2';
    case version431     = '4.3.1';
}

/**
 * Status enums
 */
enum Status: string {
    case UPDATE         = 'update';
    case INSTALL        = 'install';
    case UPGRADE        = 'upgrade';
    case UNINSTALL      = 'uninstall';
}

class Install
{
    public static string $installationFolder = './installation';
    public static string $currentVersion;
    protected static mysqli $connection;

    public function __construct()
    {
        Dotenv::createUnsafeImmutable('./')->load();
    }

    /**
     * Check if this is a new installation or an update
     *
     * @return \Status
     */
    public static function check(): string
    {
        if(file_exists('./public/configuration.php')) {
            return Status::UPDATE->value;
        }

        if(file_exists(getEnv('TEMPLATE_FOLDER') . '/configuration.php')) {
            return Status::UPDATE->value;
        }

        return Status::INSTALL->value;
    }

    /**
     * Get the current version of the installed version
     *
     * @return string|bool
     */
    public static function getCurrentVersion(): string|null
    {
        try {
            $versionFile = @file_get_contents(getenv('SITE_URL') . '/language/en-GB/en-GB.xml');

            if($versionFile) {
                if(preg_match('/<version>(.*?)<\/version>/', $versionFile, $version)) {
                    return $version[1];
                }
            }
        } catch(\Exception $exception) {
            return null;
        }

        return null;
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
     * Get the version Url
     *
     * @param $version
     * @return string
     */
    private static function versionUrl($version): string
    {
        return 'https://downloads.joomla.org/cms/joomla4/4-3-2/Joomla_4-3-2-Stable-Full_Package.zip';
    }

    /**
     * Download a selected version of Joomla from the Joomla website
     *
     * @param $version
     * @return string
     */
    public static function download($version = null): string
    {
        $source         = self::versionUrl($version);
        $destination    = 'joomla-' . $version . '.zip';

        file_put_contents($destination, file_get_contents($source));

        return $destination;
    }

    /**
     * Delete the downloaded package file
     *
     * @param string $file
     * @param string $location
     * @return bool
     */
    public static function deleteDownload(string $file)
    {
        $fileLocation = './' . $file;

        if(file_exists($fileLocation)) {
            if(unlink($fileLocation)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Unzip the downloaded version of Joomla
     *
     * @param string $file
     * @param string $location
     * @return bool
     */
    public static function unzip(string $file, string $location): bool
    {
        $zip = new ZipArchive;
        if ($zip->open($file) === true) {

            // Remove the old location folder
            system('rm -rf -- ' . escapeshellarg('./' . $location));

            // Extract to location folder
            $zip->extractTo('./' . $location . '/');
            $zip->close();

            // Remove the download after unzipping
            if(self::deleteDownload($file, $location)) {
                return true;
            }

            return false;
        }

        return false;
    }

    /**
     * The installation command with pre-filled arguments
     *
     * @return string
     */
    public static function install(): string
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

    /**
     * Check for Joomla updates with Joomla CLI
     *
     * @return string|bool
     */
    public static function checkForUpdates(): string|bool
    {
        if(file_exists('./installation/cli/joomla.php')) {
            return 'php ./installation/cli/joomla.php core:check-updates';
        }
        elseif(file_exists('./public/cli/joomla.php')) {
            return 'php ./public/cli/joomla.php core:check-updates';
        }

        return false;
    }

    /**
     * Update Joomla to the latest version with Joomla CLI
     *
     * @return string|bool
     */
    public static function update(): string|bool
    {
        if(file_exists('./installation/cli/joomla.php')) {
            return 'php ./installation/cli/joomla.php core:update';
        }
        elseif(file_exists('./public/cli/joomla.php')) {
            return 'php ./public/cli/joomla.php core:update';
        }

        return false;
    }

    /**
     * Move the installation folder to public
     *
     * @return string|bool
     */
    public static function move(): string|bool
    {
        $destinationFolder = getenv('TEMPLATE_TARGET');

        if($destinationFolder == "" || !isset($destinationFolder)) {
            $destinationFolder = './public';
        }

        if(!rename(self::$installationFolder, $destinationFolder)) {
            return false;
        }

        return $destinationFolder;
    }
}
