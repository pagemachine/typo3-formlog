<?php

declare(strict_types = 1);

namespace Pagemachine\Formlog\Domain\Model;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Pagemachine\Formlog\Domain\Data\JsonData;
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

    public ?JsonData $data = null;

    public ?JsonData $finisherVariables = null;
}
