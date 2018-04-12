# Adquesto PHP SDK

Easy custom integration with only few steps required to start displaying ads.

## Install

`composer require adquesto/adquesto-php-sdk`

## Quick start

```php
use Adquesto\SDK\Content;
use Adquesto\SDK\InMemoryStorage;
use Adquesto\SDK\ElementsContextProvider;

$adquesto = new Content(
    'https://qa.dqst.pl/api/v1/publishers/services/',
    'Paste Service UUID here',
    new InMemoryStorage
);
$javascript = $adquesto->javascript([
    new ElementsContextProvider('main-adquesto-element-id', 'reminder-adquesto-element-id'),
]);
```

### Automatic Ad placement

```php
$adquesto->prepare(
    $htmlContent,
    $adContainerHtml,
    $reminderAdContainerHtml,
    $javascript
);
```

## Overview

`Content` is a facade which provides all of the functionalities for displaying ads and maintaining it's placement.

Constructor arguments are:

`$apiUrl` Adquesto services endpoint

`$serviceId` Can be either a Service UUID or callable which is used when fetching javascript file contents from API

`$storage` Instance of Storage implementation which holds javascript file contents to prevent performing API requests everytime we need to display it.
We provide two implementations: `WordpressStorage` and `InMemoryStorage`.

`$contentProcessors` An array of context processors which are used to get template values used in rendering javascript file.
There are ready to use implementations which are functionaly divided into `ElementsContextProvider` and `SubscriptionsContextProvider`.

---

### `WordpressStorage implements Storage`

Uses Wordpress `get_option` function to persist Javascript file contents and caches it for 24 hours.

### `InMemoryStorage implements Storage`

Basic implementation which holds Javascript file contents in memory.
