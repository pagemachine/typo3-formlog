<?php

declare(strict_types = 1);

namespace Pagemachine\Formlog\Domain\FormLog;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

/**
 * Filter form log entries by date range
 */
class DateRangeFilter implements FilterInterface
{
    public function __construct(protected ?\DateTime $startDate = null, protected ?\DateTime $endDate = null)
    {
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    /**
     * Returns whether the filter is set
     */
    public function isEmpty(): bool
    {
        return empty($this->startDate) && empty($this->endDate);
    }

    public function toArray(): array
    {
        return [
            'startDate' => $this->startDate ? $this->startDate->format('c') : null,
            'endDate' => $this->endDate ? $this->endDate->format('c') : null,
        ];
    }
}
