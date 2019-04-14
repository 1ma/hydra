<?php

declare(strict_types=1);

use Nyholm\Psr7\Request;
use UMA\Hydra;

require_once __DIR__ . '/../vendor/autoload.php';

$options = new Hydra\ClientOptions();
$options->fixedPool = 1;

$request = new Request('GET', 'https://www.google.com/');
$callback = new Hydra\Tests\Fixtures\DebuggingCallback();
$client = new Hydra\Client($options);

$client->load($request, $callback);
$client->load($request, $callback);
$client->load($request, $callback);
$client->load($request, $callback);
$client->load($request, $callback);
$client->send();
