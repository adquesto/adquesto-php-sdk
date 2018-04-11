<?php

namespace Adquesto\SDK;

class InMemoryStorage implements Storage
{
    private $contents;
    
    public function get()
    {
        return $this->contents;
    }

    public function set($value)
    {
        $this->contents = $value;
    }

    public function valid()
    {
        return !is_null($this->contents);
    }
}
