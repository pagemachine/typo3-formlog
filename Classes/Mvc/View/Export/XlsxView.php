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
    /**
     * @var string
     */
    protected $fileExtension = 'xlsx';

    /**
     * Transform view value to a XLSX representation
     *
     * @return string
     * @throws \InvalidArgumentException if the column configuration is empty
     */
    public function render()
    {
        if (empty($this->configuration['columns'])) {
            throw new \InvalidArgumentException('XLSX export column configuration is empty', 1517391761);
        }

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
