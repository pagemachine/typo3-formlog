<?php
declare(strict_types = 1);

namespace Pagemachine\Formlog\ViewHelpers\Uri;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Generate an URI for editing a record
 */
class EditRecordViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('uid', 'int', 'Record identifier', true);
        $this->registerArgument('tableName', 'string', 'Name of the record table', true);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string Value of constant
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $parameters = [
            'edit' => [
                $arguments['tableName'] => [
                    $arguments['uid'] => 'edit',
                ],
            ],
            'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI'),
        ];
        /** @var UriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        return (string)$uriBuilder->buildUriFromRoute('record_edit', $parameters);
    }
}
