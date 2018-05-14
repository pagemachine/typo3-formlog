<?php
declare(strict_types = 1);

namespace Pagemachine\Formlog\Domain\FormLog;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

interface FilterInterface
{
    /**
     * The property name to filter
     *
     * @return string
     */
    public function getPropertyName(): string;

    /**
     * Returns whether the filter is set
     *
     * @return bool
     */
    public function isEmpty(): bool;
}
