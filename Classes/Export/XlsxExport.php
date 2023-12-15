<?php

declare(strict_types=1);

namespace Pagemachine\Formlog\Export;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use OneSheet\Style\Style;
use OneSheet\Writer;

final class XlsxExport extends AbstractExport
{
    protected string $fileExtension = 'xlsx';

    public function dump(iterable $items): void
    {
        $headers = $this->getHeaders();
        $columnPaths = $this->getColumnPaths();
        $filename = $this->getOutputFilename();

        $headerStyle = (new Style())
            ->setFontBold();

        $writer = new Writer();
        $writer->enableCellAutosizing();
        $writer->addRow($headers, $headerStyle);
        $writer->setFreezePaneCellId('A2');
        $writer->addRows($this->generateRows($items, $columnPaths));

        $writer->writeToBrowser($filename);
    }
}
