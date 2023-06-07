<?php
declare(strict_types=1);

namespace PicturaeInstaller\App;

use Symfony\Component\Filesystem\Filesystem;

class Template
{
    public static $templateName;
    public static $templateFolder;
    public static $destinationFolder;
    public static $templateDirectory;
    public static $baseTemplateFolder = 'src/Template/starter-template';

    public function __construct($destinationFolder)
    {
        // Define the template name
        self::$templateName         = getenv('TEMPLATE_NAME');

        // Define the template folder
        self::$templateFolder       = getenv('TEMPLATE_FOLDER');

        if(self::$templateFolder == "" || !isset(self::$templateFolder)) {
            self::$templateFolder = './template';
        }

        // Define the destination folder
        self::$destinationFolder    = $destinationFolder;
    }

    public static function create()
    {
        /// Define the base template
        $baseTemplateLocation = dirname(__FILE__) . '/../' . self::$baseTemplateFolder;

        // Define the templateDirectory
        self::$templateDirectory = getcwd() . '/' . str_replace('./', '', self::$templateFolder) . '/' . self::$templateName;

        // Mirror the baseTemplateLocation to the starter-theme templateDirectory
        $filesystem = new Filesystem();
        $filesystem->mirror($baseTemplateLocation, self::$templateDirectory);

        return self::$templateDirectory;
    }

    public static function symlink()
    {
        symlink(
            getcwd() . '/' . self::$templateFolder . '/' . self::$templateName,
            self::$destinationFolder . '/templates/' . self::$templateName,
        );

        return true;
    }
}
