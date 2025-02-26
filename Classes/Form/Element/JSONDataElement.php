<?php

declare(strict_types=1);

namespace Pagemachine\Formlog\Form\Element;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Pagemachine\Formlog\Json;
use Pagemachine\Formlog\Rendering\ValueFormatter;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * JSON data FormEngine element
 */
class JSONDataElement extends AbstractFormElement
{
    /**
     * @return array
     */
    public function render(): array
    {
        $result = $this->initializeResultArray();
        $parameters = $this->data['parameterArray'];
        $data = Json::decode($parameters['itemFormElValue']);
        $formatter = GeneralUtility::makeInstance(ValueFormatter::class);

        $languageService = $this->getLanguageService();
        $rows[] = sprintf(
            '<tr><th>%s</th><th>%s</th></tr>',
            $languageService->sL('LLL:EXT:formlog/Resources/Private/Language/locallang_element.xlf:jsonData.field'),
            $languageService->sL('LLL:EXT:formlog/Resources/Private/Language/locallang_element.xlf:jsonData.value')
        );

        foreach ($data as $name => $value) {
            $rows[] = sprintf(
                '<tr><th>%s</th><td style="white-space: pre">%s</td></tr>',
                $name,
                $formatter->format($value)
            );
        }

        $result['html'] = sprintf("<table class=\"table table-striped table-hover\">\n%s\n</table>", implode("\n", $rows));

        return $result;
    }
}
