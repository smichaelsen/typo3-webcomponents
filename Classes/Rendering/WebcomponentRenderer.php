<?php

declare(strict_types=1);

namespace Smic\Webcomponents\Rendering;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

class WebcomponentRenderer
{
    private array $attributes = [];
    private ?string $content;
    private ?string $slot;

    public function __construct(private string $tagName) {}

    public function addAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function addAttributes(array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            $this->addAttribute($key, $value);
        }
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function setSlot(?string $slot): void
    {
        $this->slot = $slot;
    }

    public function render(): ?string
    {
        $tagBuilder = GeneralUtility::makeInstance(TagBuilder::class);
        $tagBuilder->setTagName($this->tagName);

        if (!empty($this->content)) {
            $tagBuilder->setContent($this->content);
        }

        foreach ($this->attributes as $key => $value) {
            if ($value === null) {
                continue;
            }
            if (!is_scalar($value)) {
                $value = json_encode($value);
            }
            $tagBuilder->addAttribute($key, $value);
        }

        if (!empty($this->slot)) {
            $tagBuilder->addAttribute('slot', $this->slot);
        }

        $tagBuilder->forceClosingTag(true);
        return $tagBuilder->render();
    }
}
