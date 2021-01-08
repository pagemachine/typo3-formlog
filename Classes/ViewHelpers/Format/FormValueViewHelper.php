<?php
declare(strict_types = 1);

namespace Pagemachine\Formlog\ViewHelpers\Format;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

final class FormValueViewHelper extends AbstractViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;

    public function __construct()
    {
        $this->contentArgumentName = 'value';
    }

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('value', 'mixed', 'Form value');
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $value = $renderChildrenClosure();

        if (is_null($value)) {
            return '';
        }

        if (is_scalar($value)) {
            return (string)$value;
        }

        if (is_array($value)) {
            if (self::hasStringKeys($value)) {
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
    private static function hasStringKeys(array $array): bool
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }
}
