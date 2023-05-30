#!/usr/bin/env php
<?php

if (php_sapi_name() !== 'cli') {
    exit;
}

$root_app = dirname(__DIR__);

if (!is_file($root_app . '/vendor/autoload.php')) {
    $root_app = dirname(__DIR__, 4);
}

require_once $root_app . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Joomla\CMS\Installation\Console\InstallCommand;

use PicturaeInstaller\App\InstallJoomlaCommand;

$app = new Application();

$app->add(new InstallJoomlaCommand());

$app->run();
