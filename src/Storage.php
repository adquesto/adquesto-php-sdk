<?php

namespace Adquesto\SDK;

interface Storage
{
    public function get();

    public function set($contents);

    public function valid();
}
