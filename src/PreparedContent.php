<?php

namespace Adquesto\SDK;

class PreparedContent
{
    private $content;
    private $isAdReady;
    private $javascript;

    public function __construct($content, $isAdReady)
    {
        $this->content = $content;
        $this->isAdReady = $isAdReady;
    }

    public function isAdRead()
    {
        return $this->isAdReady;
    }

    public function __toString()
    {
        return (string)$this->content . (
            $this->javascript ? ('<script type="text/javascript">' . $this->javascript . '</script>') : null);
    }

    /**
     * @param mixed $javascript
     */
    public function setJavascript($javascript)
    {
        $this->javascript = $javascript;
    }
}
