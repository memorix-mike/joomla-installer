<?php
declare(strict_types=1);

namespace PicturaeInstaller\App;

use Dotenv\Dotenv;

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


}
