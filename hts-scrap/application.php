<?php

require __DIR__.'/vendor/autoload.php';

use HTS\Command\HtsCommand;
use Symfony\Component\Console\Application;

error_reporting(E_STRICT|E_ALL);

$application = new Application();
$application->add(new HtsCommand());
$application->run();
