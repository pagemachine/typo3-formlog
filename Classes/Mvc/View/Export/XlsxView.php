<?php
declare(strict_types = 1);

namespace Pagemachine\Formlog\Mvc\View\Export;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use OneSheet\Style\Style;
use OneSheet\Writer;

/**
 * A view for XLSX export
 */
class XlsxView extends AbstractExportView
{
    protected string $fileExtension = 'xlsx';

    /**
     * Transform view value to a XLSX representation
     *
     * @return string
     */
    public function render()
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
        $writer->addRows($this->generateRows($this->variables['items'], $columnPaths));

        $writer->writeToBrowser($filename);

        return '';
    }
}
