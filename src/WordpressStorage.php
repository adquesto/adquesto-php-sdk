<?php

namespace Adquesto\SDK;

class WordpressStorage implements JavascriptStorage
{
    const OPTION_JAVASCRIPT = 'questo_javascript';
    const OPTION_JAVASCRIPT_LAST_UPDATE_TIME = 'questo_javascript_last_update_time';

    public function get()
    {
        return get_option(static::OPTION_JAVASCRIPT);
    }

    public function set($contents)
    {
        update_option(static::OPTION_JAVASCRIPT, $contents);
        update_option(static::OPTION_JAVASCRIPT_LAST_UPDATE_TIME, time());
    }

    public function valid()
    {
        $lastUpdate = (int) get_option(static::OPTION_JAVASCRIPT_LAST_UPDATE_TIME);
        $previousDay = time() - (60 * 60 * 24);

        //update javascript every 24h
        if ($lastUpdate < $previousDay) {
            return false;
        }

        return (bool)$this->get();
    }
}
