# AdQuesto PHP SDK

Easy custom integration with only few steps required to start displaying ads.

## Install

`composer require adquesto/adquesto-php-sdk`

## Usage

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

## Automatic Ad placement

```php
$adquesto->prepare(
    $htmlContent,
    $adContainerHtml,
    $reminderAdContainerHtml,
    $javascript
);
```
