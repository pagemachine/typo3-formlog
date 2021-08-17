<?php
declare(strict_types = 1);

namespace Pagemachine\Formlog\Domain\Model\FormLogEntry;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * A form log entry language
 */
class Language extends AbstractEntity
{
    public string $title = '';
}
