# Adquesto PHP SDK

Easy custom integration with only few steps required to start displaying ads.


[![Travis-ci](https://api.travis-ci.org/adquesto/adquesto-php-sdk.svg?branch=master)](https://travis-ci.org/adquesto/adquesto-php-sdk/)


## Install

`composer require adquesto/adquesto-php-sdk`

## Quick start

Create `Content` with your defaults to start serving ads.

```php
use Adquesto\SDK\Content;
use Adquesto\SDK\InMemoryStorage;
use Adquesto\SDK\CurlHttpClient;
use Adquesto\SDK\PositioningSettings;

$adquesto = new Content(
    # Adquesto API endpoint that provides latest javascript
    'https://api.adquesto.com/v1/publishers/services/',
    # Unique Service identifier
    'Paste Service UUID here',
    # Implementation that will hold javascript file contents
    new InMemoryStorage,
    new CurlHttpClient,
    PositioningSettings::factory(PositioningSettings::STRATEGY_UPPER)
);
```

It is a façade which provides all of the functionalities for displaying ads and maintaining its placement.

Constructor arguments are:

`apiUrl` Adquesto services endpoint

`serviceId` Can be either a Service UUID or callable which is used when fetching javascript file contents from API. You can find yours by navigating to details of a service from Dashboard.

`storage` Instance of Storage implementation which holds javascript file contents to prevent performing API requests everytime we need to display it.
We provide two implementations: `WordpressStorage` and `InMemoryStorage`

`httpClient` Implementation of HTTP client which is used to fetch JavaScript

`positioningSettings` Instance of an PositioningSettings that returns values that are used to position a quest during rendering
the content. There are two pre-defined strategies: `PositioningSettings::STRATEGY_UPPER` and `PositioningSettings::STRATEGY_LOWER`.
Use `factory` method as a best practice. 

`contextProviders` (`optional`) An array of context providers which are used to get template values used in rendering javascript file.
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

NOTE: `adContainerHtml` and `reminderAdContainerHtml` must be the same as those used when fetching javascript file.

```php
$preparedContent = $adquesto->autoPrepare(
    $htmlContent,
    $adContainerHtml,
    $reminderAdContainerHtml
);
```

### Manual Ad placement

You can also use function below to find `<div class="questo-should-be-inserted-here"></div>` and replace with the ad:

```php
$preparedContent = $adquesto->manualPrepare(
    $htmlContent,
    $adContainerHtml,
    $reminderAdContainerHtml
);
```

If you just want to check if there is a div in the content use:

```php
$hasQuesto = $adquesto->hasQuestoInContent($content);
```

The function will return `true` if `<div class="questo-here"></div>` exists in the content.

### Prepared content

Both `autoPrepare` and `manualPrepare` return `PreparedContent` instances which in addition to hold the content also
has a flag which tells wether it is valid to display a questo - `isAdReady` method.

To apply JavaScript plugin source:
```php
$preparedContent->setJavaScript($javascript);
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
* `hasActiveCampaigns` bool or callable that should hold information about existing campaigns for your Service - changes are 
triggered using webhook and value is in the response along Service status

By default both `mainQuestId` and `reminderQuestId` are generated with random string.

*NOTE*: For all testing purposes, including preview mode in CMS of any kind, 
`isDraft` should be set to `true`.

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

### Webhooks

We will send POST request to you Service webhook URL with `form-data` with one of the following actions. Each
action represent changes that need to be undertaken on your end for coherent experience. 

You should respond with JSON `{"status": "OK"}`. If not, we will retry using exponential back-off strategy for 10 times.

#### Service status update

Action: `questo_update_service_status_option`

Once received you should ask API back for current status. Example:

```php
$serviceApiUrl = new \Adquesto\SDK\ServiceAPIUrl(
    'https://api.adquesto.com/v1/publishers/services/',
    'Paste Service UUID here'
);
$service = new \Adquesto\SDK\Service(
    $serviceApiUrl,
    new CurlHttpClient
);
$serviceStatusResponse = $service->fetchStatus();
```

In response there is an array with following keys:

* `status` tells if Service is accepted (`bool`)

Use this one to turn on and off any ad displaying related activity.

* `subscription` has subscriptions enabled (`bool`)

This one should be used when fetching Javascript plugin, as it tells whether to turn Subscriptions feature on or off.

* `hasActiveCampaigns` are there any campaigns and quests that are eligible to display by your Service.

#### Subscription status update

Action: `questo_update_subscription_option`

This one is fired when Subscription feature is toggled either on or off. Now to fetch the actual value please use same
method as described for `Service status update`.

#### Forced Javascript update

Action: `questo_force_update_javascript`

From time to time we might call your endpoint to tell that there is new Javascript file available 
so that you can update it in your Storage.

![Image](https://www.websequencediagrams.com/cgi-bin/cdraw?lz=dGl0bGUgSmF2YXNjcmlwdCBmb3JjZSB1cGRhdGUgcHJvY2VkdXJlCgpBZHF1ZXN0by0-SW50ZWdyYXRpb246IFBPU1QgL3lvdXItAC4GLWVuZHBvaW50IHdpdGggAC8GXwBRBV8AUAZfagBkCmFjdGlvbgojIG5vdGUgcmlnaHQgb2YgQmFja2VuZDogUmVhZGVyIFVVSUQgaXMgZ2VuZXJhdGVkCgAiDgCBBg1JbnZhbGkAgTsFU3RvcmFnZQoAgSgLLT4AgUIIOiBHRVQgbmV3AIF0CwCBTxlOABkOAGQbU2F2ZSBpdCBpbgB2CQBoGFJlc3BvbmQAgiQGSlNPTiBzdGF0dXMgT0sAgVsGbGVmAIILBQCBKQpJZiBub3QsIHdlIHdpbGwgcmV0cnkKCiNCcm93c2VyLT4AAgc6IE5leHQgZ2V0IHIAgj4GcmUAgzwFCiMAglwPACgIVXNlIGRpc2sgY2FjaGUARg0AgwYIR2V0IFF1ZXMAg1UHACQFZACDGAwK&s=patent)

Example below shows details of how javascript could be replaced with new one:

```php
$javascript = $adquesto->requestJavascript();
if ($javascript) {
    $content->getStorage()->set($javascript);
}
```
