<?php
declare(strict_types = 1);

namespace Pagemachine\Formlog\Domain\Model;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * A form log entry
 */
class FormLogEntry extends AbstractEntity
{
    /**
     * @var \Pagemachine\Formlog\Domain\Model\FormLogEntry\Page
     */
    protected $page;

    /**
     * @return \Pagemachine\Formlog\Domain\Model\FormLogEntry\Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @var \DateTime
     */
    protected $submissionDate;

    /**
     * @return \DateTime
     */
    public function getSubmissionDate()
    {
        return $this->submissionDate;
    }

    /**
     * @var \Pagemachine\Formlog\Domain\Model\FormLogEntry\Language
     */
    protected $language;

    /**
     * @return \Pagemachine\Formlog\Domain\Model\FormLogEntry\Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @var string
     */
    protected $identifier = '';

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @var string
     */
    protected $data = '';

    /**
     * Runtime cache for loaded data
     *
     * @var array
     */
    protected $cachedData;

    /**
     * @return array
     */
    public function getData(): array
    {
        if (!is_array($this->cachedData)) {
            $this->cachedData = json_decode($this->data, true) ?: [];
        }

        return $this->cachedData;
    }

    /**
     * @var string
     */
    protected $finisherVariables = '';

    /**
     * Runtime cache for loaded finisher variables
     *
     * @var array
     */
    protected $cachedFinisherVariables;

    /**
     * @return array
     */
    public function getFinisherVariables(): array
    {
        if (!is_array($this->cachedFinisherVariables)) {
            $this->cachedFinisherVariables = json_decode($this->finisherVariables, true) ?: [];
        }

        return $this->cachedFinisherVariables;
    }
}
