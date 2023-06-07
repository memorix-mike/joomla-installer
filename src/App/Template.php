<?php
declare(strict_types=1);

namespace PicturaeInstaller\App;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Version enums
 */
enum Version: string {
    case Latest         = '1.0.0';
}

/**
 * Date enums
 */
enum Date: string {
    case Latest         = '07-06-2023';
}


class Template
{
    public static string $templateName;
    public static string $templateFolder;
    public static string $destinationFolder;
    public static string $templateDirectory;
    public static string $baseTemplateFolder = 'Template/starter-template';
    public static string $baseTemplateLocation;
    public static string $templateDescription = 'Custom Joomla template for ';

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
        self::$baseTemplateLocation = dirname(__DIR__, 1) . '/' . self::$baseTemplateFolder;

        // Define the templateDirectory
        self::$templateDirectory = getcwd() . '/' . str_replace('./', '', self::$templateFolder) . '/templates/' . self::$templateName;

        // Mirror the baseTemplateLocation to the starter-theme templateDirectory
        $filesystem = new Filesystem();
        $filesystem->mirror(self::$baseTemplateLocation, self::$templateDirectory);

        //Set-up all other folders
        self::createAdministrator();
        self::createComponents();
        self::createLanguages();
        self::createModules();
        self::createPlugins();

        // Replace the .xml file
        self::newTemplateDetails();

        return self::$templateDirectory;
    }

    public static function symlink()
    {
        symlink(
            getcwd() . '/' . self::$templateFolder . '/templates/' . self::$templateName,
            self::$destinationFolder . '/templates/' . self::$templateName,
        );

        return true;
    }


    private static function createAdministrator()
    {
        mkdir(self::$templateDirectory . '/../../administrator', 0755);
    }

    private static function createComponents()
    {
        mkdir(self::$templateDirectory . '/../../components', 0755);
    }

    private static function createLanguages()
    {
        mkdir(self::$templateDirectory . '/../../languages', 0755);
    }

    private static function createModules()
    {
        mkdir(self::$templateDirectory . '/../../modules', 0755);
    }

    private static function createPlugins()
    {
        mkdir(self::$templateDirectory . '/../../plugins', 0755);
    }

    private static function newTemplateDetails()
    {
        $file           = self::$templateDirectory . '/' . 'templateDetails.xml';

        if(file_exists($file)) {
            $old_contents   = file_get_contents($file);
            $contents       = str_replace('<name>${template_name}</name>','<name>' . getenv('TEMPLATE_NAME') . '</name>', $old_contents);
            $contents       = str_replace('<version>${template_version}</version>','<version>' . Version::Latest->value . '</version>', $contents);
            $contents       = str_replace('<creationDate>${template_creation_date}</creationDate>','<creationDate>' . Date::Latest->value . '</creationDate>', $contents);
            $new_contents   = str_replace('<description>${template_description}</description>','<description>' . self::$templateDescription . ' ' . self::$templateName . '</description>', $contents);

            if(file_put_contents($file, $new_contents)) {
                return true;
            }
        }

        return false;
    }
}
