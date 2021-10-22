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
    protected ?\DateTime $startDate;

    protected ?\DateTime $endDate;

    public function __construct(\DateTime $startDate = null, \DateTime $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
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
