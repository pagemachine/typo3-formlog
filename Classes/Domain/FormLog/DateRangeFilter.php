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
    /**
     * @var string
     */
    protected $propertyName;

    /**
     * @var \DateTime
     */
    protected $startDate;

    /**
     * @var \DateTime
     */
    protected $endDate;

    /**
     * @param string $propertyName
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     */
    public function __construct(string $propertyName, \DateTime $startDate = null, \DateTime $endDate = null)
    {
        $this->propertyName = $propertyName;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * @return string
     */
    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Returns whether the filter is set
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->startDate) && empty($this->endDate);
    }
}
