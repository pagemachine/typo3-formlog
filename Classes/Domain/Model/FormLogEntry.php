<?php

declare(strict_types = 1);

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
    /**
     * @var Page|null
     */
    public ?Page $page = null;

    /**
     * @var \DateTime|null
     */
    public ?\DateTime $submissionDate = null;

    /**
     * @var int|null
     */
    public ?int $language = null;

    /**
     * @var string
     */
    public string $identifier = '';

    /**
     * @var JsonData|null
     */
    public ?JsonData $data = null;

    /**
     * @var JsonData|null
     */
    public ?JsonData $finisherVariables = null;

    public function getSiteLanguage(): SiteLanguage
    {
        return $this->getSite()->getLanguageById($this->language);
    }

    private function getSite(): SiteInterface
    {
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);

        return $siteFinder->getSiteByPageId($this->page->uid);
    }
}
