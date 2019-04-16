<?php

declare(strict_types=1);

use Nyholm\Psr7\Request;
use UMA\Hydra;

require_once __DIR__ . '/../vendor/autoload.php';

echo "Pay attention to the pre_transfer column:\n\n";

$options = new Hydra\ClientOptions();
$options->fixedPool = 1;

$request = new Request('GET', 'https://www.google.com/');
$callback = new Hydra\Tests\Fixtures\DebuggingCallback();
$client = new Hydra\Client($options);

$time = \microtime(true);
$client->load($request, $callback);
$client->load($request, $callback);
$client->load($request, $callback);
$client->load($request, $callback);
$client->load($request, $callback);
$client->send();

$elapsedTime = \microtime(true) - $time;
$overhead = (1 - $callback->lastTotalTime() / $elapsedTime);

echo \sprintf("\nElapsed time: %f seconds (%.2f%% overhead)\n", $elapsedTime, 100*$overhead);
