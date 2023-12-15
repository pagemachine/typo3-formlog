<?php

declare(strict_types=1);

namespace Pagemachine\Formlog\Domain\FormLog;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

/**
 * Filter form log entries by value
 */
class ValueFilter implements FilterInterface
{
    public function __construct(protected string $value = '') {}

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Returns whether the filter is set
     */
    public function isEmpty(): bool
    {
        return empty($this->value);
    }

    public function toArray(): array
    {
        return ['value' => $this->value];
    }
}
