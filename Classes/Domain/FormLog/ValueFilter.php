<?php
declare(strict_types = 1);

namespace Pagemachine\Formlog\Domain\FormLog;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

/**
 * Filter form log entries by value
 */
class ValueFilter implements FilterInterface
{
    /**
     * @var string
     */
    protected $propertyName;

    /**
     * @var string
     */
    protected $value;

    /**
     * @param string $propertyName
     * @param string $value
     */
    public function __construct(string $propertyName, string $value = '')
    {
        $this->propertyName = $propertyName;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Returns whether the filter is set
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->value);
    }
}
