<?php
declare(strict_types = 1);
namespace Pagemachine\Formlog\Form\Element;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;

/**
 * JSON data FormEngine element
 */
class JSONDataElement extends AbstractFormElement
{
    /**
     * @return array
     */
    public function render()
    {
        $result = $this->initializeResultArray();
        $parameters = $this->data['parameterArray'];
        $value = $parameters['itemFormElValue'];
        $data = json_decode($value, true) ?: [];

        $languageService = $this->getLanguageService();
        $rows[] = sprintf(
            '<tr><th>%s</th><th>%s</th></tr>',
            $languageService->sL('LLL:EXT:formlog/Resources/Private/Language/locallang_element.xlf:serializedData.field'),
            $languageService->sL('LLL:EXT:formlog/Resources/Private/Language/locallang_element.xlf:serializedData.value')
        );

        foreach ($this->flatten($data) as $name => $value) {
            $values = $this->normalizeValues($value);

            foreach ($values as $i => $value) {
                $cells = [];

                if ($i === 0) {
                    $cells[] = sprintf('<th rowspan="%d">%s</th>', count($values), htmlspecialchars((string)$name));
                }
                $cells[] = sprintf('<td>%s</td>', nl2br(htmlspecialchars((string)$value)));

                $rows[] = sprintf('<tr>%s</tr>', implode('', $cells));
            }
        }

        $result['html'] = sprintf("<table class=\"table table-striped table-hover\">\n%s\n</table>", implode("\n", $rows));

        return $result;
    }

    /**
     * Normalize data values to array
     *
     * @param mixed $value
     * @return array
     */
    protected function normalizeValues($value): array
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        return $value;
    }

    /**
     * Converts a multidimensional array to a flat representation
     *
     * Contrary to \TYPO3\CMS\Core\Utility\ArrayUtility::flatten()
     * arrays with sequential keys are left unchanged.
     *
     * @param array $array The (relative) array to be converted
     * @param string $prefix The (relative) prefix to be used (e.g. 'section.')
     * @return array
     */
    protected function flatten(array $array, string $prefix = ''): array
    {
        $flatArray = [];

        foreach ($array as $key => $value) {
            // Ensure there is no trailing dot:
            $key = rtrim($key, '.');

            if (!is_array($value) || $this->isSequentialArray($value)) {
                $flatArray[$prefix . $key] = $value;
            } else {
                $flatArray = array_merge($flatArray, $this->flatten($value, $prefix . $key . '.'));
            }
        }

        return $flatArray;
    }

    /**
     * Determines whether an array is associative
     *
     * @see https://stackoverflow.com/a/173479/6812729
     *
     * @param array $array
     * @return bool
     */
    protected function isSequentialArray(array $array): bool
    {
        return array_keys($array) === range(0, count($array) - 1);
    }
}
