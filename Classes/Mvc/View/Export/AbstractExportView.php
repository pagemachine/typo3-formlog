<?php
declare(strict_types = 1);

namespace Pagemachine\Formlog\Mvc\View\Export;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Pagemachine\Formlog\Mvc\View\ConfigurableViewInterface;
use Pagemachine\Formlog\Rendering\ValueFormatter;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
        $headers = array_column($this->getColumns(), 'label');
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
        $columnPaths = array_column($this->getColumns(), 'property');

        return $columnPaths;
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
    protected function convertValueToString($value): string
    {
        $formatter = GeneralUtility::makeInstance(ValueFormatter::class);

        return $formatter
            ->setDateTimeFormat($this->getDateTimeFormat())
            ->format($value);
    }

    /**
     * @throws \InvalidArgumentException if the column configuration is empty
     */
    private function getColumns(): array
    {
        $columns = $this->configuration['columns'] ?? [];

        if (empty($columns)) {
            throw new \InvalidArgumentException('Export column configuration is empty', 1516620386);
        }

        ksort($columns);

        return $columns;
    }
}
