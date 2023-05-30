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



//require_once __DIR__ . '/../autoload.php';
//require_once __DIR__ . '/../../functions.php';

use Symfony\Component\Console\Application;
use Joomla\CMS\Installation\Console\InstallCommand;
use Console\DownloadJoomlaCommand;

$app = new Application();

$app->add(new DownloadJoomlaCommand());

$app->run();
