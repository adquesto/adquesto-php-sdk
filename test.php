<?php

include './vendor/autoload.php';

use Adquesto\SDK\Content;
use Adquesto\SDK\InMemoryStorage;

$a = new Content('http://test.pl', 'test', new InMemoryStorage, []);
$a->prepare('test', 'test', 'test', 'test');
