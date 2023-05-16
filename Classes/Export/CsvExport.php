<?php

declare(strict_types = 1);

namespace Pagemachine\Formlog\Export;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use League\Csv\EscapeFormula;
use League\Csv\Writer;

final class CsvExport extends AbstractExport
{
    protected string $fileExtension = 'csv';

    public function dump(iterable $items): void
    {
        $headers = $this->getHeaders();
        $columnPaths = $this->getColumnPaths();
        $filename = $this->getOutputFilename();

        $csv = Writer::createFromString('');
        $csv->addFormatter(new EscapeFormula());
        $csv->insertOne($headers);
        $csv->insertAll($this->generateRows($items, $columnPaths));

        $csv->output($filename);
    }
}
