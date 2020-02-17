<?php
declare(strict_types = 1);

namespace Pagemachine\Formlog\Mvc\View\Export;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Pagemachine\Formlog\Mvc\View\ConfigurableViewInterface;
use TYPO3\CMS\Extbase\Mvc\View\AbstractView;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Base class for export views (CSV, ...)
 */
abstract class AbstractExportView extends AbstractView implements ConfigurableViewInterface
{
    /**
     * @var string
     */
    protected $fileExtension;

    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param array $configuration
     * @return void
     */
    public function setConfiguration(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Get the list of CSV headers
     *
     * @return array
     */
    protected function getHeaders()
    {
        $headers = array_column($this->configuration['columns'], 'label');
        $headers = array_map(function ($header) {

            return  LocalizationUtility::translate($header, 'Formlog') ?: $header;
        }, $headers);

        return $headers;
    }

    /**
     * Get the list of CSV column paths
     *
     * @return array
     */
    protected function getColumnPaths()
    {
        $columnPaths = array_column($this->configuration['columns'], 'property');

        return $columnPaths;
    }

    /**
     * Extends the "__allData" placeholder within export column configuration with columns from first record.
     */
    protected function extendAllDataPlaceholder()
    {
        if (empty($this->configuration['columns']) || !is_array($this->configuration['columns'])) {
            return;
        }

        $newColumnConfiguration = [];

        foreach ($this->configuration['columns'] as $key => $column) {
            if (($column['property'] ?? '') === '__allData') {
                $firstItem = $this->variables['items']->getFirst();
                if ($firstItem !== null) {

                    /** @var \Pagemachine\Formlog\Domain\Model\FormLogEntry $firstItem  */
                    $columns = $firstItem->getData();

                    foreach ($columns as $columnName => $columnValue) {
                        $newColumnConfiguration[] = [
                            'property' => 'data.' . $columnName,
                            'label' => $columnName,
                        ];
                    }
                }
            } else {
                $newColumnConfiguration[] = $column;
            }
        }

        $this->configuration['columns'] = $newColumnConfiguration;
    }

    /**
     * Get the CSV output filename
     *
     * @return string
     */
    protected function getOutputFilename()
    {
        $fileBasename = 'output';

        if (!empty($this->configuration['fileBasename'])) {
            $fileBasename = $this->configuration['fileBasename'];
        }

        return sprintf('%s.%s', $fileBasename, $this->fileExtension);
    }

    /**
     * Get the preferred date/time format
     *
     * @return string
     */
    protected function getDateTimeFormat()
    {
        $dateTimeFormat = \DateTime::W3C;

        if (!empty($this->configuration['dateTimeFormat'])) {
            $dateTimeFormat = $this->configuration['dateTimeFormat'];
        }

        return $dateTimeFormat;
    }

    /**
     * Generate a list of rows
     *
     * @param array|\Traversable $iterable iterable value
     * @param array $columnPaths List of property paths per column
     * @return \Traversable
     */
    protected function generateRows($iterable, array $columnPaths)
    {
        foreach ($iterable as $item) {
            $record = [];

            foreach ($columnPaths as $columnPath) {
                $value = ObjectAccess::getPropertyPath($item, $columnPath);
                $record[] = $this->convertValueToString($value);
            }

            yield $record;
        }
    }

    /**
     * Convert supported values to string
     *
     * @param mixed $value value to convert
     * @return string
     */
    protected function convertValueToString($value)
    {
        if ($value === null) {
            return '';
        }

        if (is_scalar($value)) {
            return (string)$value;
        }

        if (is_array($value)) {
            return implode(', ', $value);
        }

        if ($value instanceof \DateTime) {
            return $value->format($this->getDateTimeFormat());
        }

        throw new \UnexpectedValueException(
            sprintf('Could not convert value of type "%s" to string', is_object($value) ? get_class($value) : gettype($value)),
            1516617588
        );
    }
}
