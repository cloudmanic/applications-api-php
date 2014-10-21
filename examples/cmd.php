<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('display_startup_errors', 'On');

include_once 'vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new Cloudmanic\System\Console\Unit);
$application->add(new Cloudmanic\Api\Console\Auth);
$application->run();