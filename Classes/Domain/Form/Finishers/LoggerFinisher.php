<?php

declare(strict_types=1);

namespace Pagemachine\Formlog\Domain\Form\Finishers;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Pagemachine\Formlog\Json;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Resource\FileReference as CoreFileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference as ExtbaseFileReference;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use TYPO3\CMS\Form\Domain\Finishers\Exception\FinisherException;
use TYPO3\CMS\Form\Domain\Model\FormElements\StringableFormElementInterface;

/**
 * Finisher which logs all form values into the database
 */
class LoggerFinisher extends AbstractFinisher implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var array
     */
    protected $defaultOptions = [
        'finisherVariables' => [],
        'includeHiddenElements' => true,
        'skipElementsTypes' => 'ContentElement,StaticText',
    ];

    protected function executeInternal(): ?string
    {
        $formRuntime = $this->finisherContext->getFormRuntime();
        $formDefinition = $formRuntime->getFormDefinition();
        $context = GeneralUtility::makeInstance(Context::class);
        $now = $context->getPropertyFromAspect('date', 'timestamp');
        $formValues = $this->getFormValues();
        $finisherVariables = $this->getFinisherVariables();

        try {
            $encodedFormValues = Json::encode($formValues);
        } catch (\JsonException $e) {
            $this->logger->critical('Failed to encode form values', [
                'exception' => $e,
                'formValues' => serialize($formValues),
            ]);

            throw new FinisherException(sprintf('Failed to encode form values: %s', $e->getMessage()), 1677581834, $e);
        }

        try {
            $encodedFinisherVariables = Json::encode($finisherVariables);
        } catch (\JsonException $e) {
            $this->logger->critical('Failed to encode finisher variables', [
                'exception' => $e,
                'finisherVariables' => serialize($finisherVariables),
            ]);

            throw new FinisherException(sprintf('Failed to encode finisher variables: %s', $e->getMessage()), 1677581959, $e);
        }

        $data = [
            'crdate' => $now,
            'tstamp' => $now,
            'page' => (new Typo3Version())->getMajorVersion() < 13 ? $this->getTypoScriptFrontendController()->id : $this->finisherContext->getRequest()->getAttribute('frontend.page.information')->getId(),
            'language' => (int)$context->getPropertyFromAspect('language', 'id', 0),
            'identifier' => $formDefinition->getIdentifier(),
            'data' => $encodedFormValues,
            'finisher_variables' => $encodedFinisherVariables,
        ];

        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $connection = $connectionPool->getConnectionForTable('tx_formlog_entries');
        $connection->insert('tx_formlog_entries', $data);

        return null;
    }

    /**
     * Get normalized form values
     */
    protected function getFormValues(): array
    {
        $normalizedFormValues = [];
        $formDefinition = $this->finisherContext->getFormRuntime()->getFormDefinition();
        $skipHiddenElements = !$this->parseOption('includeHiddenElements');
        $skipElementTypes = GeneralUtility::trimExplode(',', $this->parseOption('skipElementsTypes'));

        foreach ($this->finisherContext->getFormValues() as $identifier => $formValue) {
            $element = $formDefinition->getElementByIdentifier($identifier);
            $elementType = null;
            if ($element !== null) {
                $renderingOptions = $element->getRenderingOptions();
                $elementType = $element->getType();
            }
            if (
                $skipHiddenElements &&
                (
                    // Mimik the logik of \TYPO3\CMS\Form\Domain\Finishers\EmailFinisher
                    // with conditions against {formValue.value} and {formValue.isSection} in
                    // EXT:form/Resources/Private/Frontend/Templates/Finishers/Email/Default.html
                    // where {formValue.isSection} is set in \TYPO3\CMS\Form\ViewHelpers\RenderFormValueViewHelper::renderStatic()
                    ($renderingOptions['_isCompositeFormElement'] ?? false)
                    || ($renderingOptions['_isSection'] ?? false)
                    // additionally skip configurable element types which don't actually have form values (e.g. StaticText)
                    || in_array($elementType, $skipElementTypes)
                )
            ) {
                continue;
            }

            if (is_object($formValue)) {
                if ($formValue instanceof ExtbaseFileReference) {
                    $formValue = $formValue->getOriginalResource();
                }

                if ($formValue instanceof CoreFileReference) {
                    $normalizedFormValues[$identifier] = [
                        'file' => [
                            'name' => $formValue->getName(),
                        ],
                    ];
                    continue;
                }

                if ($element instanceof StringableFormElementInterface) {
                    $normalizedFormValues[$identifier] = $element->valueToString($formValue);
                    continue;
                }
            } else {
                $normalizedFormValues[$identifier] = $formValue;
            }
        }

        return $normalizedFormValues;
    }

    /**
     * Get map of configured finisher variables
     *
     * @return array
     */
    protected function getFinisherVariables(): array
    {
        $finisherVariablesConfiguration = $this->parseOption('finisherVariables');
        $variableProvider = $this->finisherContext->getFinisherVariableProvider();
        $finisherVariables = [];

        foreach ($finisherVariablesConfiguration as $finisherIdentifier => $variableNames) {
            foreach ($variableNames as $variableName) {
                $finisherVariables[$finisherIdentifier][$variableName] = $variableProvider->get($finisherIdentifier, $variableName);
            }
        }

        return $finisherVariables;
    }
}
