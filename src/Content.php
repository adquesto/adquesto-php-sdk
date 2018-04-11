<?php

namespace Adquesto\SDK;

class Content
{
    /** 
     * @var string 
     */
    private $apiUrl;

    /** 
     * @var string 
     */
    private $serviceId;

    /** 
     * @var Storage 
     */
    private $storage;

    /**
     * @var ContextProvider[] 
     */
    private $contextProcessors;

    /**
     * @param $apiUrl string Base Adquesto API URL
     * @param $serviceId string Service UUID
     * @param $storage Storage Implementation to persist javascript file contents
     * @param $contextProcessors ContextProvider[] Used to render template values
    */
    public function __construct($apiUrl, $serviceId, Storage $storage, array $contextProcessors = array())
    {
        $this->apiUrl = $apiUrl;
        $this->serviceId = $serviceId;
        $this->storage = $storage;
        $this->contentProcessors = $contextProcessors;
    }

    /**
     * @return Storage
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @return mixed
     */
    protected function serviceId()
    {
        if (is_callable($this->serviceId)) {
            $serviceId = $this->serviceId;

            return $serviceId();
        }

        return $this->serviceId;
    }

    /**
     * @param $contextProviders mixed An array or single ContextProvider instance
     * @return mixed[]
     */
    protected function contentValues($contextProviders = null)
    {
        $contentValues = array();
        $contextProcessors = array_merge($this->contentProcessors, (array) $contextProviders);
        foreach ($contextProcessors as $contentProcessor) {
            $contentValues = array_merge($contentValues, $contentProcessor->values());
        }

        return $contentValues;
    }

    /**
     * @return string
     */
    public function requestJavascript()
    {
        return @file_get_contents(
            sprintf('%s%s/javascript', $this->apiUrl, $this->serviceId())
        );
    }

    /**
     * @param $contextProviders mixed An array or single ContextProvider instance
     * @return string
     */
    public function javascript($contextProviders = null)
    {
        if (!$this->storage->valid()) {
            $remoteJavascript = $this->requestJavascript();

            if ($remoteJavascript) {
                $this->storage->set($remoteJavascript);
            }
        }

        $javascript = $this->storage->get();
        $mergedContentValues = $this->contentValues($contextProviders);

        return str_replace(array_keys($mergedContentValues), array_values($mergedContentValues), $javascript);
    }

    protected function getStructureDataPaywall()
    {
        return <<< STR
        <script type="application/ld+json"> 
        {"@context": "http://schema.org", "@type": "NewsArticle", "mainEntityOfPage": {"@type": "WebPage", "@id": "https://example.org/article"}, 
        "isAccessibleForFree": "False", "hasPart": [{"@type": "WebPageElement", "isAccessibleForFree": "False", "cssSelector" : ".questo-paywall"} ] }
        </script>
STR;
    }

    private function getNumberOfCharactersBySize($parent, $type, $allowFalseValues)
    {
        $numberOfCharacters = 0;
        $elements = $parent->find($type);
        foreach ($elements as $element) {
            $width = $element->getAttribute('width');
            $height = $element->getAttribute('height');
            $hasCorrectSize = $width >= 150 && $height >= 150;
            if (!$allowFalseValues && $hasCorrectSize) {
                $numberOfCharacters += 500;
                continue;
            }
            if ($allowFalseValues && ($hasCorrectSize || $width === false || $height === false)) {
                $numberOfCharacters += 500;
                continue;
            }
        }

        return $numberOfCharacters;
    }

    private static function safeStrlen($str)
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($str, '8bit');
        }

        return strlen($str);
    }

    public function prepare($originalContent, $containerMainQuest, $containerReminderQuest, $javascript)
    {
        $content = $originalContent;
        $containerQuestoHere = '<div class="questo-here"';
        //check if the content has questo-here from tinyMCE plugin
        $hasQuestoHereInContent = strrpos($content, $containerQuestoHere) !== false;

        if ($hasQuestoHereInContent) {
            $content = preg_replace('/' . $containerQuestoHere . '.*?><\/div>$/mi', $containerMainQuest, $content);
            $content .= $containerReminderQuest;
            $content .= $javascript;

            return $content;
        }

        $wrapperId = 'adquestoWrapper';
        $dom = HtmlDomParser::str_get_html(sprintf('<div id="%s">%s</div>', $wrapperId, $content));
        $paragraphs = $dom->getElementById($wrapperId)->childNodes();

        $content = $this->getStructureDataPaywall();
        $numberOfCharacters = 0;
        $questoHereIncluded = false;

        foreach ($paragraphs as $key => $paragraph) {
            $numberOfCharacters += $this->safeStrlen($paragraph->text());
            $numberOfCharacters += $this->getNumberOfCharactersBySize($paragraph, 'img', true);
            $numberOfCharacters += $this->getNumberOfCharactersBySize($paragraph, 'iframe', false);

            if ($questoHereIncluded) {
                //we have to reset number of character to check number of characters after ad
                $paragraph->class = 'questo-paywall';
            }

            $content .= $paragraph->outertext();

            if ($numberOfCharacters >= 1200 && !$questoHereIncluded) {
                $numberOfCharacters = 0;
                $content .= $containerMainQuest;
                $questoHereIncluded = true;
            }
        }

        if ($numberOfCharacters >= 1000) {
            $content .= $containerReminderQuest;
            $content .= $javascript;

            return $content;
        }

        return $originalContent;
    }
}
