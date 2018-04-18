# Adquesto PHP SDK

Easy custom integration with only few steps required to start displaying ads.

## Install

`composer require adquesto/adquesto-php-sdk`

## Quick start

Create `Content` with your defaults to start serving ads.

```php
use Adquesto\SDK\Content;
use Adquesto\SDK\InMemoryStorage;
use Adquesto\SDK\ElementsContextProvider;

$adquesto = new Content(
    # Adquesto API endpoint that provides latest javascript
    'https://dqst.pl/api/v1/publishers/services/',
    # Unique Service identifier
    'Paste Service UUID here',
    # Implementation that will hold javascript file contents
    new InMemoryStorage
);
```

It is a façade which provides all of the functionalities for displaying ads and maintaining its placement.

Constructor arguments are:

`apiUrl` Adquesto services endpoint

`serviceId` Can be either a Service UUID or callable which is used when fetching javascript file contents from API

`storage` Instance of Storage implementation which holds javascript file contents to prevent performing API requests everytime we need to display it.
We provide two implementations: `WordpressStorage` and `InMemoryStorage`.

`contentProcessors` An array of context processors which are used to get template values used in rendering javascript file.
There are ready to use implementations which are functionaly divided into `ElementsContextProvider` and `SubscriptionsContextProvider`.

Now, we can fetch javascript file that will eventually render ads.

```php
$javascript = $adquesto->javascript([
    new ElementsContextProvider('main-adquesto-element-id', 'reminder-adquesto-element-id'),
]);
```

We can pass an array of context providers just like for constructor. They are
responsible for replacing placeholders inside javascript file fetched from API with
integration specific values.

Example above will return javascript source code what will use `main-adquesto-element-id` and `reminder-adquesto-element-id` as containers for ads.

### Automatic Ad placement

We provide method to help you place ad in best spot inside HTML content. To make use of it call `prepare` method with arguments:

* `htmlContent` which is an HTML of your content (eg. blog post)
* `adContainerHtml` is an HTML of a container, which will hold ad (eg. `<div id="questo-container"></div>`)
* `reminderAdContainerHtml` being the same as above, only difference it will hold reminder ad
*  `javascript` it output of the method above

```php
$preparedContent = $adquesto->prepare(
    $htmlContent,
    $adContainerHtml,
    $reminderAdContainerHtml,
    $javascript
);
```

Now `preparedContent` is ready to placed on website.

## Overview

### Storages

We use Storage interface to communicate intentions related to javascript contents persistance not to perform multiple API calls and thus provide better user experience.

#### WordpressStorage

Uses Wordpress `get_option` function to persist Javascript file contents and caches it for 24 hours.

#### InMemoryStorage

Basic implementation which holds Javascript file contents in memory. Most likely used for poc use only.

### Context Providers

To properly render javascript that displays ads we use template that should be populated with values that set few important variables. There are two ready to use implementations that provide them with nice interface.

#### ElementsContextProvider

Most important context provider which tells names of HTML containers that are used for rendering ads. Constructor parameters are:

* `mainQuestId` Main ad element ID name (eg. `questo-container`)
* `reminderQuestId` Reminder ad element ID (eg. `questo-reminder-container`)

#### SubscriptionsContextProvider

This one is optional and enables subscriptions feature.

```php
new SubscriptionsContextProvider(array(
    SubscriptionsContextProvider::IS_SUBSCRIPTION_ACTIVE => (int)$isSubscriptionActive,
    SubscriptionsContextProvider::IS_SUBSCRIPTION_RECURRING => (int)$isSubscriptionRecurring,
    SubscriptionsContextProvider::IS_SUBSCRIPTION_DAYS_LEFT => $subscriptionDaysLeft,
    SubscriptionsContextProvider::IS_SUBSCRIPTION_AVAILABLE => (int)$isSubscriptionAvailable,
    SubscriptionsContextProvider::AUTHORIZATION_ERROR => (string)$authorizationError,
    SubscriptionsContextProvider::IS_LOGGED_IN => (int)$hasAddFreeUser,
    SubscriptionsContextProvider::AUTHORIZATION_URI => $authorizationUri,
    SubscriptionsContextProvider::LOGOUT_URI => $logoutUri,
    SubscriptionsContextProvider::USER_LOGIN => $userLogin,
    SubscriptionsContextProvider::IS_PUBLISHED => get_post_status() == 'publish',
)),
```
