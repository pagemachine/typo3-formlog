<?php

declare(strict_types=1);

namespace Pagemachine\Formlog\ViewHelpers\Format;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Pagemachine\Formlog\Rendering\ValueFormatter;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class FormValueViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('value', 'mixed', 'Form value');
    }

    public function render(): string {
        $value = $this->buildRenderChildrenClosure()();
        $formatter = GeneralUtility::makeInstance(ValueFormatter::class);

        return $formatter->format($value);
    }
}
