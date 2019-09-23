<?php

declare(strict_types=1);

use Nyholm\Psr7\Request;
use UMA\Hydra;

require_once __DIR__ . '/../vendor/autoload.php';

echo 'Pay attention to the pre_transfer column:' . PHP_EOL . PHP_EOL;

$options = new Hydra\ClientOptions();
$options->fixedPool = 3;
$options->persistentPool = true;

$request = new Request('GET', 'https://www.google.com/');
$client = new Hydra\Client($options);

$handler = new Hydra\Tests\Fixtures\DebuggingHandler();
$client->load($request, $handler);
$client->load($request, $handler);
$client->load($request, $handler);
$client->send();

echo PHP_EOL;

$handler = new Hydra\Tests\Fixtures\DebuggingHandler();
$client->load($request, $handler);
$client->load($request, $handler);
$client->load($request, $handler);
$client->send();
