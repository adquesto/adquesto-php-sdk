<?php

namespace Adquesto\SDK;

use simplehtmldom_1_5\simple_html_dom_node;
use Sunra\PhpSimple\HtmlDomParser;

class Content
{
    const PAYWALL_CLASS = 'questo-paywall';
    const MANUAL_QUEST_CLASS = 'questo-should-be-inserted-here';

    /**
     * @var string
     */
    private $apiUrl;

    /**
     * @var string
     */
    private $serviceId;

    /**
     * @var JavascriptStorage
     */
    private $javascriptStorage;

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var ContextProvider[]
     */
    private $contextProviders;

    /**
     * @param string            $apiUrl Base Adquesto API URL
     * @param string            $serviceId Service UUID
     * @param JavascriptStorage $javascriptStorage Implementation to persist javascript file contents
     * @param HttpClient        $httpClient Implementation to fetch data from API
     * @param ContextProvider[] $contextProviders Used to render template values
     */
    public function __construct($apiUrl, $serviceId, JavascriptStorage $javascriptStorage, HttpClient $httpClient, 
        array $contextProviders = array())
    {
        $this->apiUrl = $apiUrl;
        $this->serviceId = $serviceId;
        $this->javascriptStorage = $javascriptStorage;
        $this->httpClient = $httpClient;
        $this->contextProviders = $contextProviders;
    }

    /**
     * @return JavascriptStorage
     */
    public function getStorage()
    {
        return $this->javascriptStorage;
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
    protected function contextValues($contextProviders = null)
    {
        $contextValues = array();
        $contextProviders = array_merge($this->contextProviders, (array)$contextProviders);
        foreach ($contextProviders as $contextProvider) {
            $contextValues = array_merge($contextValues, $contextProvider->values());
        }

        return $contextValues;
    }

    /**
     * @return string
     */
    public function requestJavascript()
    {
        return $this->httpClient->get(
            sprintf('%s%s/javascript', $this->apiUrl, $this->serviceId())
        );
    }

    /**
     * @param mixed $contextProviders An array or single ContextProvider instance
     * @return string
     */
    public function javascript($contextProviders = null)
    {
        if (!$this->javascriptStorage->valid()) {
            $remoteJavascript = $this->requestJavascript();

            if ($remoteJavascript) {
                $this->javascriptStorage->set($remoteJavascript);
            }
        }

        $javascript = $this->javascriptStorage->get();
        $mergedContextValues = $this->contextValues($contextProviders);

        return str_replace(array_keys($mergedContextValues), array_values($mergedContextValues), $javascript);
    }

    /**
     * @return string
     */
    protected function getStructureDataPaywall()
    {
        return sprintf('
        <script type="application/ld+json"> 
        {"@context": "http://schema.org", "@type": "NewsArticle", "mainEntityOfPage": {"@type": "WebPage", "@id": "https://example.org/article"}, 
        "isAccessibleForFree": "False", "hasPart": [{"@type": "WebPageElement", "isAccessibleForFree": "False", "cssSelector" : ".%s"} ] }
        </script>', self::PAYWALL_CLASS);
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
     * @param string $content
     * @return null|simple_html_dom_node|simple_html_dom_node[]
     */
    public function getParagraphs($content)
    {
        $wrapperId = 'adquestoWrapper';
        $dom = HtmlDomParser::str_get_html(sprintf('<div id="%s">%s</div>', $wrapperId, $content));
        return $dom->getElementById($wrapperId)->childNodes();
    }

    /**
     * @param string $string
     * @return bool
     */
    public function hasQuestoInString($string)
    {
        return strpos($string, self::MANUAL_QUEST_CLASS) !== false;
    }

    /**
     * @param string $html
     * @return bool
     */
    public function hasQuestoClassInHTML($html)
    {
        return (bool)preg_match('/class="(.*)' . self::MANUAL_QUEST_CLASS . '(.*)"/m', $html);
    }

    /**
     * Check Content::MANUAL_QUEST_CLASS class in the content, if exists put quests in the content
     *
     * @param string $originalContent
     * @param string $containerMainQuest
     * @param string $containerReminderQuest
     * @param string $javascript
     * @return string|bool
     */
    public function manualPrepare($originalContent, $containerMainQuest, $containerReminderQuest, $javascript)
    {
        $content = $this->getStructureDataPaywall();
        $paragraphs = $this->getParagraphs($originalContent);
        $questoHereIncluded = false;
        $hasQuestoHereInContent = $this->hasQuestoInString($originalContent);

        if ($hasQuestoHereInContent) {
            foreach ($paragraphs as $key => $paragraph) {
                if ($this->hasQuestoInString($paragraph->class)) {
                    if (!$questoHereIncluded) {
                        $content .= $containerMainQuest;
                        $questoHereIncluded = true;
                    }
                    continue;
                }

                if ($questoHereIncluded) {
                    $paragraph->class = self::PAYWALL_CLASS;
                }
                $content .= $paragraph->outertext();
            }

            $content .= $containerReminderQuest;
            $content .= '<script type="text/javascript">' . $javascript . '</script>';

            return $content;
        }

        return $originalContent;
    }

    /**
     * Try to automatically put quests in the content based on the number of characters, images, iframe
     *
     * @param string $originalContent
     * @param string $containerMainQuest
     * @param string $containerReminderQuest
     * @param string $javascript
     * @return string
     */
    public function autoPrepare($originalContent, $containerMainQuest, $containerReminderQuest, $javascript)
    {
        $paragraphs = $this->getParagraphs($originalContent);
        $content = $this->getStructureDataPaywall();

        $questoHereIncluded = false;

        $numberOfCharacters = 0;
        foreach ($paragraphs as $key => $paragraph) {
            $numberOfCharacters += $this->safeStrlen($paragraph->text());
            $numberOfCharacters += $this->getNumberOfCharactersBySize($paragraph, 'img', true);
            $numberOfCharacters += $this->getNumberOfCharactersBySize($paragraph, 'iframe', false);

            if ($questoHereIncluded) {
                //we have to reset number of character to check number of characters after ad
                $paragraph->class = self::PAYWALL_CLASS;
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
            $content .= '<script type="text/javascript">' . $javascript . '</script>';

            return $content;
        }

        return $originalContent;
    }
}
