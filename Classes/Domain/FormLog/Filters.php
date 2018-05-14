<?php
declare(strict_types = 1);

namespace Pagemachine\Formlog\Domain\FormLog;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

/**
 * Collection of form log filters
 */
class Filters implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    protected $filters = [];

    /**
     * Returns whether no filter is set
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->filters);
    }

    /**
     * @param mixed $offset
     */
    public function offsetExists($offset)
    {
        return isset($this->filters[$offset]);
    }

    /**
     * @param mixed $offset
     */
    public function offsetGet($offset)
    {
        return $this->filters[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->filters[$offset] = $value;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->filters[$offset]);
    }

    /**
     * @return \Traversable
     */
    public function getIterator(): \Traversable
    {
        yield from $this->filters;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->filters);
    }
}
