<?php

namespace Adquesto\SDK;

class Subscriber
{
    public $email;
    public $valid_to;
    public $hasRecurringPayments;
    public $uid;

    public function __construct($uid, $email, $subscriptionDate, $hasRecurringPayments)
    {
        $this->uid = $uid;
        $this->email = $email;
        $this->valid_to = $subscriptionDate;
        $this->hasRecurringPayments = $hasRecurringPayments;
    }

    public function isSubscriptionValid()
    {
        return $this->valid_to > (new \DateTime);
    }

    public function daysLeft($now = null)
    {
        if (empty($now)) {
            $now = new \DateTime();
        }

        return ((int) $now->diff($this->valid_to)->format('%a')) + 1;
    }
}
