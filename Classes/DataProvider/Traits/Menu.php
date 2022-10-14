<?php

namespace Smic\Webcomponents\DataProvider\Traits;

use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Typolink\LinkResult;

trait Menu
{
    protected PageRepository $pageRepository;

    public function injectPageRepository(PageRepository $pageRepository): void
    {
        $this->pageRepository = $pageRepository;
    }

    protected function getLinkResultsOfSubpages(int $pageId, ContentObjectRenderer $contentObjectRenderer): ?array
    {
        $subpages = $this->getMenuOfSubpages($pageId);
        if ($subpages === null) {
            return null;
        }

        $linkResults = [];
        foreach ($subpages as $subpage) {
            $linkResults[] = $contentObjectRenderer->typoLink(
                $subpage['nav_title'] ?? $subpage['title'],
                [
                    'parameter' => $subpage['uid'],
                    'returnLast' => 'result',
                ]
            );
        }
        return $linkResults;
    }

    protected function getMenuOfSubpages(int $pageId): ?array
    {
        $page = $this->pageRepository->getPage($pageId);
        $page = $this->resolveShortcut($page);
        if (count($page) === 0) {
            return null;
        }
        return $this->pageRepository->getMenu($pageId, 'uid, doktype, title, nav_title', 'sorting', 'doktype < 200 AND nav_hide = 0', false);
    }

    private function resolveShortcut(array $originalPage): array
    {
        if ($originalPage['doktype'] !== PageRepository::DOKTYPE_SHORTCUT) {
            return $originalPage;
        }
        switch ($originalPage['shortcut_mode']) {
            case 3:
                // mode: parent page of current page (using PID of current page)
                return $this->pageRepository->getPage($originalPage['pid']);
            case 2:
                // mode: random subpage of selected or current page
                $subpages = $this->getMenuOfSubpages($originalPage['shortcut'] > 0 ? $originalPage['shortcut'] : $originalPage['uid']);
                if (count($subpages) === 0) {
                    return $originalPage;
                }
                return $subpages[array_rand($subpages)];
            case 1:
                // mode: first subpage of selected or current page
                $subpages = $this->getMenuOfSubpages($originalPage['shortcut'] > 0 ? $originalPage['shortcut'] : $originalPage['uid']);
                if (count($subpages) === 0) {
                    return $originalPage;
                }
                return reset($subpages);
            case 0:
            default:
                // mode: selected page
                return $this->pageRepository->getPage($originalPage['shortcut']);
        }
    }
}
