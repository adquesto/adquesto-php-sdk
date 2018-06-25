<?php

include './vendor/autoload.php';

use Adquesto\SDK\SubscriberManager;
use Adquesto\SDK\OAuth2Client;
use Adquesto\SDK\CurlHttpClient;
use Adquesto\SDK\Subscriber;
use Adquesto\SDK\SubscriberSessionStorage;

$manager = new SubscriberManager(
    $oauth2Client = new OAuth2Client(
        new CurlHttpClient, 
        'Hc5ANQY0aAAff3s7Iq9yTtkU2S1SMEynWmbzVRUM',  
        'http://localhost:3000/subscriber', 
        'http://localhost:5000/api/oauth2/token', 
        'http://localhost:5000/api/oauth2/me',
        'http://localhost:5000/api/v1/echo'
    ),
    $subscriberStorage = new SubscriberSessionStorage
);
var_dump($oauth2Client->authorizationUrl());
exit;
$subscriber = $manager->handleRedirect('secret_code');
var_dump($subscriber == $subscriberStorage->get(), $subscriber);
