#!/usr/bin/env php
<?php
//require_once __DIR__ . '/../autoload.php';
//require_once __DIR__ . '/../../functions.php';

use Symfony\Component\Console\Application;
use Joomla\CMS\Installation\Console\InstallCommand;
use Console\DownloadJoomlaCommand;

$app = new Application();

$app->add(new DownloadJoomlaCommand());

$app->run();
