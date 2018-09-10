# Adquesto PHP SDK

Easy custom integration with only few steps required to start displaying ads.

## Install

`composer require adquesto/adquesto-php-sdk`

## Quick start

Create `Content` with your defaults to start serving ads.

```php
use Adquesto\SDK\Content;
use Adquesto\SDK\InMemoryStorage;
use Adquesto\SDK\CurlHttpClient;

$adquesto = new Content(
    # Adquesto API endpoint that provides latest javascript
    'https://api.adquesto.com/v1/publishers/services/',
    # Unique Service identifier
    'Paste Service UUID here',
    # Implementation that will hold javascript file contents
    new InMemoryStorage,
    new CurlHttpClient
);
```

It is a façade which provides all of the functionalities for displaying ads and maintaining its placement.

Constructor arguments are:

`apiUrl` Adquesto services endpoint

`serviceId` Can be either a Service UUID or callable which is used when fetching javascript file contents from API. You can find yours by navigating to details of a service from Dashboard.

`storage` Instance of Storage implementation which holds javascript file contents to prevent performing API requests everytime we need to display it.
We provide two implementations: `WordpressStorage` and `InMemoryStorage`

`httpClient` Implementation of HTTP client which is used to fetch JavaScript

`contextProviders` An array of context providers which are used to get template values used in rendering javascript file.
There are ready to use implementations which are functionaly divided, i.e. `ElementsContextProvider`.

Now, we can fetch javascript file that will eventually render ads.

```php
use Adquesto\SDK\ElementsContextProvider;

try {
    $javascript = $adquesto->javascript([
        $elementsProvider = new ElementsContextProvider,
        new \Adquesto\SDK\SubscriptionsContextProvider,
    ]);
} catch (Adquesto\SDK\NetworkErrorException $e) {
    // Handle exception here
}
```

We can pass an array of context providers just like for constructor. They are
responsible for replacing placeholders inside javascript file fetched from API with
integration specific values.

Example above will return javascript source code what will use random IDs as containers for ads.
It's then possible to fetch generated IDs by using methods: 

```php
$mainQuestElementId = $elementsProviders->mainQuestId();
$reminderQuestElementId = $elementsProviders->reminderQuestId();
```

### Automatic Ad placement

We provide method to help you place ad in best spot inside HTML content. To make use of it call `prepare` method with arguments:

* `htmlContent` which is an HTML of your content (eg. blog post)
* `adContainerHtml` is an HTML of a container, which will hold ad (eg. `<div id="$mainQuestElementId"></div>`)
* `reminderAdContainerHtml` being the same as above, only difference it will hold reminder ad (`<div id="$reminderQuestElementId"></div>`)
*  `javascript` it output of the method above

NOTE: `adContainerHtml` and `reminderAdContainerHtml` must be the same as those used when fetching javascript file.

```php
$preparedContent = $adquesto->autoPrepare(
    $htmlContent,
    $adContainerHtml,
    $reminderAdContainerHtml,
    $javascript
);
```

Now `preparedContent` is ready to be placed on website.

You can also use function below to find `<div class="questo-here"></div>` and replace with the ad:

```php
$preparedContent = $adquesto->manualPrepare(
    $htmlContent,
    $adContainerHtml,
    $reminderAdContainerHtml,
    $javascript
);
```

If you just want to check if there is a div in the content use:

```php
$hasQuesto = $adquesto->hasQuestoInContent($content);
```

The function will return `true` if `<div class="questo-here"></div>` exists in the content.

### Forced Javascript update (using Webhook)

From time to time we might call your endpoint to tell that there is new Javascript file available so that you can update it in your Storage.

Best practice is to expose publicly available endpoint that can accept GET and trigger fetching javascript once again, replace only when it succeeded.

You should respond with JSON `{"status": "OK"}`. If not, we will retry every 30 minutes for 3 hours, then once a day for a week.

![Image](https://www.websequencediagrams.com/cgi-bin/cdraw?lz=dGl0bGUgSmF2YXNjcmlwdCBmb3JjZSB1cGRhdGUgcHJvY2VkdXJlCgpBZHF1ZXN0by0-SW50ZWdyYXRpb246IEdFVCAveW91ci0ALQYtZW5kcG9pbnQKIyBub3RlIHJpZ2h0IG9mIEJhY2tlbmQ6IFJlYWRlciBVVUlEIGlzIGdlbmVyYXRlZAoAIg4AWg1JbnZhbGkAgQ8FU3RvcmFnZQoAfAstPgCBFggAgQsGbmV3AIFICwCBIxlOABkOAGQbU2F2ZSBpdCBpbgB2CQBoGFJlc3BvbmQgd2l0aCBKU09OIHN0YXR1cyBPSwCBWwZsZWYAggsFAIEpCklmIG5vdCwgd2Ugd2lsbCByZXRyeQoKI0Jyb3dzZXItPgACBzogTmV4dCBnZXQgcgCCPgZyZQCDEAUKIwCCXA8AKAhVc2UgZGlzayBjYWNoZQBGDQCDBghHZXQgUXVlc3QAgSwGACQFZACDGAwK&s=patent)

Example below shows details of how javascript could be replaced with new one:

```php
$javascript = $adquesto->requestJavascript();
if ($javascript) {
    $content->getStorage()->set($javascript);
}
```

## Overview

### Javascript Storages

We use Storage interface to communicate intentions related to javascript contents persistance not to perform multiple API calls and thus provide better user experience.

#### WordpressStorage

Uses Wordpress `get_option` function to persist Javascript file contents and caches it for 24 hours.

#### InMemoryStorage

Basic implementation which holds Javascript file contents in memory. Most likely used for poc use only.

### Context Providers

To properly render javascript that displays ads we use template that should be populated with values that set few important variables. There are two ready to use implementations that provide them with nice interface.

#### ElementsContextProvider

Most important context provider which tells names of HTML containers that are used for rendering ads. Constructor parameters are optional:

* `mainQuestId` Main ad element ID name (eg. `questo-container`)
* `reminderQuestId` Reminder ad element ID (eg. `questo-reminder-container`)
* `isDraft` Should Ad be displayed as a draft (false as default)

By default both are generated with random string.

#### SubscriptionsContextProvider

This provides variables that are crucial to enable Subscription feature alowing to identify readers authenticated via OAuth2.

```php
$subscriberStorage = new SubscriberSessionStorage;
$subscriber = $subscriberStorage->get();
$daysLeft = $subscriber->valid_to->diff(new \DateTime)->days;

new SubscriptionsContextProvider(array(
    SubscriptionsContextProvider::IS_SUBSCRIPTION_ACTIVE => (int)$subscriber->isSubscriptionValid(),
    SubscriptionsContextProvider::IS_SUBSCRIPTION_RECURRING => (int)$isSubscriptionRecurring,
    SubscriptionsContextProvider::IS_SUBSCRIPTION_DAYS_LEFT => $daysLeft,
    SubscriptionsContextProvider::IS_SUBSCRIPTION_AVAILABLE => true,
    SubscriptionsContextProvider::AUTHORIZATION_ERROR => (string)$authorizationError,
    SubscriptionsContextProvider::IS_LOGGED_IN => $subscriber !== null,
    SubscriptionsContextProvider::AUTHORIZATION_URI => $authorizationUri,
    SubscriptionsContextProvider::LOGOUT_URI => $logoutUri,
    SubscriptionsContextProvider::USER_LOGIN => $subscriber->email,
    SubscriptionsContextProvider::IS_PUBLISHED => true /* Is Draft? */,
));
```

### Subscriber support (via Adquesto OAuth2)

We provide OAuth2 client that utilizes Adquesto backend to authorize users as subscribers. The steps are:

* Generate authorization link to Adquesto:

```php
use Adquesto\SDK\OAuth2Client;
use Adquesto\SDK\CurlHttpClient;

$oauth2Client = new OAuth2Client(
    new CurlHttpClient,
    'client_id_here',
    'http://adquesto.com/subscriber', 
    'http://api.adquesto.com/oauth2/token', 
    'http://api.adquesto.com/oauth2/me',
    'your_redirect_uri'
);

$authorizationUrl = $oauth2Client->authorizationUrl();
```

* When User is redirected back, issue SubscriberManager method that will handle obtaining Subscriber information (including expire date)

```php
$subscriberManager = new SubscriberManager($oauth2Client, new SubscriberSessionStorage);
$subsciber = $subscriberManager->handleRedirect($_GET['code']);
```

* Subscriber is now ready to be used. Thanks to `SubscriberSessionStorage` that information is persisted in session, so each request will
have information about current Subscriber.

NOTE: It's important to run `$subscriber->isSubscriptionValid()` before anything that is related to ad-free experience.