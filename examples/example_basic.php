<?php

use Adquesto\SDK\CurlHttpClient;
use Adquesto\SDK\InMemoryStorage;

include './vendor/autoload.php';

$content = new \Adquesto\SDK\Content(
    'https://api.adquesto.com/v1/publishers/services/',  // API url
    'SERVICE-UUID',
    new InMemoryStorage,
    new CurlHttpClient
);

$js = $content->javascript(
    new \Adquesto\SDK\SubscriptionsContextProvider([])
);
