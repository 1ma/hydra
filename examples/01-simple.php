<?php

declare(strict_types=1);

use Nyholm\Psr7\Request;
use UMA\Hydra;

require_once __DIR__ . '/../vendor/autoload.php';

$options = new Hydra\ClientOptions();
$options->responseTimeout = 2000;

$handler = new Hydra\Tests\Fixtures\DebuggingHandler();
$client = new Hydra\Client($options);

$time = \microtime(true);
$client->load(new Request('GET', 'https://www.google.com/'), $handler);
$client->load(new Request('GET', 'https://www.youtube.com/'), $handler);
$client->load(new Request('GET', 'https://www.facebook.com/'), $handler);
$client->load(new Request('GET', 'https://www.baidu.com/'), $handler);
$client->load(new Request('GET', 'https://www.wikipedia.org/'), $handler);
$client->load(new Request('GET', 'https://www.qq.com/'), $handler);
$client->load(new Request('GET', 'https://www.tmall.com/'), $handler);
$client->load(new Request('GET', 'https://world.taobao.com/'), $handler);
$client->load(new Request('GET', 'https://www.yahoo.com/'), $handler);
$client->load(new Request('GET', 'https://www.amazon.com/'), $handler);
$client->send();

$elapsedTime = \microtime(true) - $time;
$overhead = (1 - $handler->lastTotalTime() / $elapsedTime);

echo \sprintf("\nElapsed time: %f seconds (%.2f%% overhead)\n", $elapsedTime, 100*$overhead);
