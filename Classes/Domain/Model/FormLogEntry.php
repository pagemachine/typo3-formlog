<?php
declare(strict_types = 1);

namespace Pagemachine\Formlog\Domain\Model;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Pagemachine\Formlog\Domain\Model\FormLogEntry\Language;
use Pagemachine\Formlog\Domain\Model\FormLogEntry\Page;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * A form log entry
 */
class FormLogEntry extends AbstractEntity
{
    public ?Page $page = null;

    public ?\DateTime $submissionDate = null;

    public ?Language $language = null;

    public string $identifier = '';

    /**
     * @var string
     */
    protected string $data = '';

    /**
     * Runtime cache for loaded data
     */
    protected ?array $cachedData = null;

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
    protected string $finisherVariables = '';

    /**
     * Runtime cache for loaded finisher variables
     */
    protected ?array $cachedFinisherVariables = null;

    public function getFinisherVariables(): array
    {
        if (!is_array($this->cachedFinisherVariables)) {
            $this->cachedFinisherVariables = json_decode($this->finisherVariables, true) ?: [];
        }

        return $this->cachedFinisherVariables;
    }
}
