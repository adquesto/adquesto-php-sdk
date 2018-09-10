<?php

namespace Adquesto\SDK;

class SubscriptionsContextProvider implements ContextProvider
{
    const IS_SUBSCRIPTION_ACTIVE = '__IS_SUBSCRIPTION_ACTIVE__';
    const IS_SUBSCRIPTION_AVAILABLE = '__IS_SUBSCRIPTION_AVAILABLE__';
    const IS_SUBSCRIPTION_RECURRING = '__IS_SUBSCRIPTION_RECURRING__';
    const IS_SUBSCRIPTION_DAYS_LEFT = '__SUBSCRIPTION_DAYS_LEFT__';
    const AUTHORIZATION_ERROR = '__AUTHORIZATION_ERROR__';
    const IS_LOGGED_IN = '__IS_LOGGED_IN__';
    const AUTHORIZATION_URI = '__AUTHORIZATION_URI__';
    const LOGOUT_URI = '__LOGOUT_URI__';
    const USER_LOGIN = '__USER_LOGIN__';
    const IS_PUBLISHED = '__IS_PUBLISHED__';

    private $values;

    public function __construct(array $values = array())
    {
        $this->values = $values;
    }

    public function values()
    {
        $valueKeys = array(
            static::IS_SUBSCRIPTION_ACTIVE,
            static::IS_SUBSCRIPTION_AVAILABLE,
            static::IS_SUBSCRIPTION_RECURRING,
            static::IS_SUBSCRIPTION_DAYS_LEFT,
            static::AUTHORIZATION_ERROR,
            static::IS_LOGGED_IN,
            static::AUTHORIZATION_URI,
            static::LOGOUT_URI,
            static::USER_LOGIN,
            static::IS_PUBLISHED,
        );
        $defaultValues = array_combine(
            $valueKeys,
            array_fill(0, count($valueKeys), false)
        );

        return array_replace($defaultValues, $this->values);
    }
}