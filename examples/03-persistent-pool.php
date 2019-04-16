<?php

declare(strict_types=1);

use Nyholm\Psr7\Request;
use UMA\Hydra;

require_once __DIR__ . '/../vendor/autoload.php';

echo "Pay attention to the pre_transfer column:\n\n";

$options = new Hydra\ClientOptions();
$options->fixedPool = 3;
$options->persistentPool = true;

$request = new Request('GET', 'https://www.google.com/');
$callback = new Hydra\Tests\Fixtures\DebuggingCallback();
$client = new Hydra\Client($options);

$client->load($request, $callback);
$client->load($request, $callback);
$client->load($request, $callback);
$client->send();

$client->load($request, $callback);
$client->load($request, $callback);
$client->load($request, $callback);
$client->send();
