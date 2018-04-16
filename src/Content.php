<?php

namespace Adquesto\SDK;

use simplehtmldom_1_5\simple_html_dom_node;
use Sunra\PhpSimple\HtmlDomParser;

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
    private $contentProcessors;

    /**
     * @param string $apiUrl Base Adquesto API URL
     * @param string $serviceId Service UUID
     * @param Storage $storage Implementation to persist javascript file contents
     * @param ContextProvider[] $contextProcessors Used to render template values
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
     * @param mixed $contextProviders An array or single ContextProvider instance
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
     * @param mixed $contextProviders An array or single ContextProvider instance
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

    /**
     * @return string
     */
    protected function getStructureDataPaywall()
    {
        return <<< STR
        <script type="application/ld+json"> 
        {"@context": "http://schema.org", "@type": "NewsArticle", "mainEntityOfPage": {"@type": "WebPage", "@id": "https://example.org/article"}, 
        "isAccessibleForFree": "False", "hasPart": [{"@type": "WebPageElement", "isAccessibleForFree": "False", "cssSelector" : ".questo-paywall"} ] }
        </script>
STR;
    }

    /**
     * @param simple_html_dom_node $parent
     * @param string               $type
     * @param boolean              $allowFalseValues
     * @return int
     * @return bool
     */
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

    /**
     * @param string $str
     * @return int
     */
    private static function safeStrlen($str)
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($str, '8bit');
        }

        return strlen($str);
    }

    /**
     * @param string $originalContent
     * @param string $containerMainQuest
     * @param string $containerReminderQuest
     * @param string $javascript
     * @return string
     */
    public function prepare($originalContent, $containerMainQuest, $containerReminderQuest, $javascript)
    {
        $content = $originalContent;
        $containerQuestoHere = '<div class="questo-here"';
        //check if the content has questo-here from tinyMCE plugin
        $hasQuestoHereInContent = strrpos($content, $containerQuestoHere) !== false;

        $wrapperId = 'adquestoWrapper';
        $dom = HtmlDomParser::str_get_html(sprintf('<div id="%s">%s</div>', $wrapperId, $content));
        $paragraphs = $dom->getElementById($wrapperId)->childNodes();

        $content = $this->getStructureDataPaywall();
        $questoHereIncluded = false;

        if ($hasQuestoHereInContent) {
            foreach ($paragraphs as $key => $paragraph) {
                if ($paragraph->class == 'questo-here') {
                    $content .= $containerMainQuest;
                    $questoHereIncluded = true;
                    continue;
                }

                if ($questoHereIncluded) {
                    $paragraph->class = 'questo-paywall';
                }
                $content .= $paragraph->outertext();
            }

            $content .= $containerReminderQuest;
            $content .= $javascript;

            return $content;
        }

        $numberOfCharacters = 0;
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
