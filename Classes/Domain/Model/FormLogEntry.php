<?php

declare(strict_types=1);

namespace Pagemachine\Formlog\Domain\Model;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Pagemachine\Formlog\Domain\Data\JsonData;
use Pagemachine\Formlog\Domain\Model\FormLogEntry\Page;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * A form log entry
 */
class FormLogEntry extends AbstractEntity
{
    public ?Page $page = null;

    public ?\DateTime $submissionDate = null;

    public ?int $language = null;

    public string $identifier = '';

    public ?JsonData $data = null;

    public ?JsonData $finisherVariables = null;

    public function getSiteLanguage(): ?SiteLanguage
    {
        $site = $this->getSite();

        if ($site === null) {
            return null;
        }

        return $this->getSite()->getLanguageById($this->language);
    }

    private function getSite(): ?SiteInterface
    {
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);

        if ($this->page === null) {
            return null;
        }

        return $siteFinder->getSiteByPageId($this->page->getUid());
    }
}
