<?php

declare(strict_types=1);

namespace Smic\Webcomponents\ContentObject;

use Smic\Webcomponents\DataProvider\DataProviderInterface;
use Smic\Webcomponents\Rendering\WebcomponentRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

class WebcomponentContentObject extends AbstractContentObject
{
    public function render($conf = []): string
    {
        $tagName = null;
        $properties = null;
        $content = null;

        $inputData = array_merge($this->cObj->data ?? [], $conf['inputData.'] ?? []);

        // Method 1: Evaluate dataProvider
        if (isset($conf['dataProvider'])) {
            $dataProvider = GeneralUtility::makeInstance($conf['dataProvider']);
            if ($dataProvider instanceof DataProviderInterface) {
                $dataProvider->setInputData($inputData);
                $dataProvider->setContentObjectRenderer($this->cObj);
                $tagName = $dataProvider->getTagName();
                $properties = $dataProvider->getProperties();
                $content = $dataProvider->getContent();
            }
        }

        // Method 2: Evaluate TypoScript configuration
        if (isset($conf['properties.'])) {
            if (!is_array($properties)) {
                $properties = [];
            }
            $keys = array_unique(array_map(fn (string $key) => rtrim($key, '.'), array_keys($conf['properties.'])));
            foreach ($keys as $key) {
                $properties[$key] = $this->cObj->stdWrap(
                    $conf['properties.'][$key] ?? '',
                    $conf['properties.'][$key . '.'] ?? []
                );
            }
        }
        if (isset($conf['tagName']) || isset($conf['tagName.'])) {
            $tagName = $this->cObj->stdWrap($conf['tagName'] ?? '', $conf['tagName.'] ?? []) ?: null;
        }

        // Don't render the Web Component if tagName or properties are null
        if ($tagName === null || $properties === null) {
            return '';
        }

        // Render
        $renderer = GeneralUtility::makeInstance(WebcomponentRenderer::class, $tagName);
        if (!empty($content)) {
            $renderer->setContent($content);
        }
        $renderer->addAttributes($properties);

        if (isset($conf['slot']) || isset($conf['slot.'])) {
            $slot = $this->cObj->stdWrap($conf['slot'] ?? '', $conf['slot.'] ?? []) ?: null;
            if (!empty($slot)) {
                $renderer->setSlot($slot);
            }
        }
        
        $renderedTag = $renderer->render();
        return $this->cObj->stdWrap($renderedTag, $conf['stdWrap.'] ?? []);
    }
}
