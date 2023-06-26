<?php
declare(strict_types=1);

namespace PicturaeInstaller\App;

final class Upgrade extends Install
{
    public static string $templateName;
    public static string $templateFolder;
    public static string $templateTarget;
    public static string $upgradeFolder = './upgrade';

    public function __construct()
    {
        parent::__construct();

        // Define the template name
        self::$templateName         = getenv('TEMPLATE_NAME');

        // Define the template folder
        self::$templateFolder       = getenv('TEMPLATE_FOLDER');

        if(self::$templateFolder == "" || !isset(self::$templateFolder)) {
            self::$templateFolder = './template';
        }

        self::$templateTarget       = getenv('TEMPLATE_TARGET');
    }

    /**
     * Get the version Url for the upgrade package
     *
     * @return string
     */
    private static function versionUrl(string $version): string|bool
    {
        switch($version) {
            case '3.10.11':
                return 'https://downloads.joomla.org/cms/joomla3/3-10-11/Joomla_3-10-11-Stable-Update_Package.zip';

            case '4.0.0':
                return 'https://downloads.joomla.org/cms/joomla4/4-0-0/Joomla_4-0-0-Stable-Update_Package.zip';
        }

        return false;
    }

    /**
     * Download a selected version of Joomla Upgrade package from the Joomla website
     *
     * @param $version
     * @return string
     */
    public static function download($version = null): string
    {
        $source         = self::versionUrl($version);
        $destination    = 'joomla-' . str_replace('.', '-', $version) . '-upgrade.zip';

        file_put_contents($destination, file_get_contents($source));

        return $destination;
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

        return 'cd ./public && php cli/joomla.php core:update';
    }


    public static function move(): string|bool
    {
        $destinationFolder = getenv('TEMPLATE_TARGET');

        if($destinationFolder == "" || !isset($destinationFolder)) {
            $destinationFolder = './public';
        }

        // Empty the destination folder
        system('rm -rf -- ' . escapeshellarg('./' . $destinationFolder));

        if(!rename(self::$upgradeFolder, $destinationFolder)) {
            return false;
        }

        return $destinationFolder;
    }


    public static function symlink()
    {
        symlink(
            getcwd() . '/' . self::$templateFolder . '/templates/' . self::$templateName,
            self::$templateTarget . '/templates/' . self::$templateName,
        );

        return true;
    }

    public static function removeCache(): bool
    {
        system('rm ' . escapeshellarg('./public/administrator/cache/autoload_psr4.php'));
        return true;
    }
}
