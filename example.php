<?php

use Adquesto\SDK\CurlHttpClient;
use Adquesto\SDK\InMemoryStorage;

include './vendor/autoload.php';

$content = new \Adquesto\SDK\Content(
    'API URL',
    'Service UUID',
    new InMemoryStorage,
    new CurlHttpClient
);

$js = $content->javascript(
    new \Adquesto\SDK\SubscriptionsContextProvider([])
);
