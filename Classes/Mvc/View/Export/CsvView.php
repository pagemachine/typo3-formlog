<?php
declare(strict_types = 1);
namespace Pagemachine\Formlog\Mvc\View\Export;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use League\Csv\Writer;

/**
 * A view for CSV export
 */
class CsvView extends AbstractExportView
{
    /**
     * @var string
     */
    protected $fileExtension = 'csv';

    /**
     * Transform view value to a CSV representation
     *
     * @return string
     * @throws \InvalidArgumentException if the column configuration is empty
     */
    public function render()
    {
        if (empty($this->configuration['columns'])) {
            throw new \InvalidArgumentException('CSV export column configuration is empty', 1516620386);
        }

        $headers = $this->getHeaders();
        $columnPaths = $this->getColumnPaths();
        $filename = $this->getOutputFilename();

        $csv = Writer::createFromString('');
        $csv->insertOne($headers);
        $csv->insertAll($this->generateRows($this->variables['items'], $columnPaths));

        $csv->output($filename);

        return '';
    }
}
