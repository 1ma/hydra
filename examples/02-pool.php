<?php

declare(strict_types=1);

use Nyholm\Psr7\Request;
use UMA\Hydra;

require_once __DIR__ . '/../vendor/autoload.php';

echo "Pay attention to the pre_transfer column:\n\n";

$options = new Hydra\ClientOptions();
$options->fixedPool = 1;

$request = new Request('GET', 'https://www.google.com/');
$handler = new Hydra\Tests\Fixtures\DebuggingHandler();
$client = new Hydra\Client($options);

$time = \microtime(true);
$client->load($request, $handler);
$client->load($request, $handler);
$client->load($request, $handler);
$client->load($request, $handler);
$client->load($request, $handler);
$client->send();

$elapsedTime = \microtime(true) - $time;
$overhead = (1 - $handler->lastTotalTime() / $elapsedTime);

echo \sprintf("\nElapsed time: %f seconds (%.2f%% overhead)\n", $elapsedTime, 100*$overhead);
