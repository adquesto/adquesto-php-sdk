<?php

namespace Adquesto\SDK;

class Subscriber
{
    public $email;
    public $valid_to;

    public function __construct($email, $subscriptionDate)
    {
        $this->email = $email;
        $this->valid_to = $subscriptionDate;
    }

    public function isSubscriptionValid()
    {
        return $this->valid_to > (new \DateTime);
    }
}
