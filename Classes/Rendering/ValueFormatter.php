<?php
declare(strict_types = 1);

namespace Pagemachine\Formlog\Rendering;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

/**
 * Format arbitrary values as string
 */
final class ValueFormatter
{
    /**
     * @throws \UnexpectedValueException for unsupported types
     */
    public function format($value): string
    {
        if (is_null($value)) {
            return '';
        }

        if (is_scalar($value)) {
            return (string)$value;
        }

        if (is_array($value)) {
            if ($this->hasStringKeys($value)) {
                $arrayValues = [];

                foreach ($value as $key => $arrayValue) {
                    $arrayValues[] = sprintf('%s: %s', $key, $arrayValue);
                }

                return implode("\n", $arrayValues);
            }

            return implode("\n", $value);
        }

        throw new \UnexpectedValueException(sprintf('Cannot format value of type "%s"', gettype($value)), 1610097797);
    }

    /**
     * @see https://stackoverflow.com/a/4254008/6812729
     */
    private function hasStringKeys(array $array): bool
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }
}
