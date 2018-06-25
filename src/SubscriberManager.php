<?php

namespace Adquesto\SDK;

/**
 * Handle OAuth2 authorization back response to get information about the Subscriber
 */
class SubscriberManager
{
    protected $oauth2Client;
    protected $storage;

    public function __construct(OAuth2Client $oauth2Client, SubscriberStorage $storage)
    {
        $this->oauth2Client = $oauth2Client;
        $this->storage = $storage;
    }

    public function handleRedirect($token)
    {
        $accessToken = $this->oauth2Client->accessToken($token);
        $me = $this->oauth2Client->me($accessToken);
        $subscriber = new Subscriber($me['email'], new \DateTime($me['subscriptionDate']));
        $this->storage->persist($subscriber);
        return $subscriber;
    }
}
