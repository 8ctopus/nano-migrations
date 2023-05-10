<?php

declare(strict_types=1);

namespace Demo;

use Apix\Log\Format\ConsoleColors;
use Apix\Log\Logger\Stream;
use Clue\Commander\Router;
use Exception;

require_once __DIR__ . '/../vendor/autoload.php';

(new \NunoMaduro\Collision\Provider)->register();

$logger = (new Stream('php://stdout'))
    // intercept logs that are >=
    ->setMinLevel('debug')
    // propagate to other loggers
    ->setCascading(true)
    ->setFormat(new ConsoleColors());

if (\PHP_SAPI !== 'cli') {
    throw new Exception('migrations must be run from cli');
}

// add routes
$router = new Router();

// add route helper
$router->add('[--help | -h]', function () use ($router) : void {
    echo 'Usage:' . PHP_EOL;

    foreach ($router->getRoutes() as $route) {
        echo '  ' . $route . PHP_EOL;
    }
});

$migration = new Migration(__DIR__ . '/migrations.txt', 'localhost', 'root', '123', 'test', $logger);

$router->add('migrate [<count>]', function (array $args) use ($migration) : void {
    $count = array_key_exists('count', $args) ? (int) $args['count'] : null;
    $migration->migrate($count);
});

$router->add('rollback [<count>]', function (array $args) use ($migration) : void {
    $count = array_key_exists('count', $args) ? (int) $args['count'] : 0;
    $migration->rollback($count);
});

// run router
$router->handleArgv();
