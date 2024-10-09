<?php

declare(strict_types=1);

namespace Pagemachine\Formlog\Domain\FormLog;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

/**
 * Collection of form log filters
 */
class Filters implements \IteratorAggregate, \Countable
{
    protected ValueFilter $pageTitle;

    protected ValueFilter $identifier;

    protected DateRangeFilter $submissionDate;

    public function __construct(
        ValueFilter $pageTitle = null,
        ValueFilter $identifier = null,
        DateRangeFilter $submissionDate = null
    ) {
        $this->pageTitle = $pageTitle ?: new ValueFilter();
        $this->identifier = $identifier ?: new ValueFilter();
        $this->submissionDate = $submissionDate ?: new DateRangeFilter();
    }

    /**
     * @return ValueFilter
     */
    public function getPageTitle(): ValueFilter
    {
        return $this->pageTitle;
    }

    /**
     * @return ValueFilter
     */
    public function getIdentifier(): ValueFilter
    {
        return $this->identifier;
    }

    public function setIdentifier(ValueFilter $identifier): void
    {
        $this->identifier = $identifier;
    }

    /**
     * @return DateRangeFilter
     */
    public function getSubmissionDate(): DateRangeFilter
    {
        return $this->submissionDate;
    }

    /**
     * Returns whether no filter is set
     */
    public function isEmpty(): bool
    {
        return count($this) === 0;
    }

    public function getIterator(): \Traversable
    {
        if (!$this->pageTitle->isEmpty()) {
            yield 'page.title' => $this->pageTitle;
        }

        if (!$this->identifier->isEmpty()) {
            yield 'identifier' => $this->identifier;
        }

        if (!$this->submissionDate->isEmpty()) {
            yield 'submissionDate' => $this->submissionDate;
        }

        yield from [];
    }

    public function count(): int
    {
        return count(iterator_to_array($this));
    }

    public function toArray(): array
    {
        return [
            'pageTitle' => $this->getPageTitle()->toArray(),
            'identifier' => $this->getIdentifier()->toArray(),
            'submissionDate' => $this->getSubmissionDate()->toArray(),
        ];
    }
}
