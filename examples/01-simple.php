<?php

declare(strict_types=1);

use Nyholm\Psr7\Request;
use UMA\Hydra;

require_once __DIR__ . '/../vendor/autoload.php';

$options = new Hydra\ClientOptions();
$options->responseTimeout = 2000;

$callback = new Hydra\Tests\Fixtures\DebuggingCallback();
$client = new Hydra\Client($options);

$time = \microtime(true);
$client->load(new Request('GET', 'https://www.google.com/'), $callback);
$client->load(new Request('GET', 'https://www.youtube.com/'), $callback);
$client->load(new Request('GET', 'https://www.facebook.com/'), $callback);
$client->load(new Request('GET', 'https://www.baidu.com/'), $callback);
$client->load(new Request('GET', 'https://www.wikipedia.org/'), $callback);
$client->load(new Request('GET', 'https://www.qq.com/'), $callback);
$client->load(new Request('GET', 'https://www.tmall.com/'), $callback);
$client->load(new Request('GET', 'https://world.taobao.com/'), $callback);
$client->load(new Request('GET', 'https://www.yahoo.com/'), $callback);
$client->load(new Request('GET', 'https://www.amazon.com/'), $callback);
$client->send();

$elapsedTime = \microtime(true) - $time;
$overhead = (1 - $callback->lastTotalTime() / $elapsedTime);

echo \sprintf("\nElapsed time: %f seconds (%.2f%% overhead)\n", $elapsedTime, 100*$overhead);
