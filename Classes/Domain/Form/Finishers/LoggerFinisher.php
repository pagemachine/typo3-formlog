<?php
declare(strict_types = 1);

namespace Pagemachine\Formlog\Domain\Form\Finishers;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Finisher which logs all form values into the database
 */
class LoggerFinisher extends AbstractFinisher
{
    /**
     * @var array
     */
    protected $defaultOptions = [
        'finisherVariables' => [],
        'saveHiddenElements' => true,
    ];

    /**
     * @var TypoScriptFrontendController
     */
    protected $frontendController;

    /**
     * @param string $finisherIdentifier
     * @param TypoScriptFrontendController|null $frontendController
     */
    public function __construct(string $finisherIdentifier = '', TypoScriptFrontendController $frontendController = null)
    {
        parent::__construct($finisherIdentifier);
        $this->frontendController = $frontendController ?: $GLOBALS['TSFE'];
    }

    /**
     * @return string|null
     */
    protected function executeInternal()
    {
        $formValues = $this->getFormValues();
        $formDefinition = $this->finisherContext->getFormRuntime()->getFormDefinition();
        $data = [
            'pid' => $this->frontendController->id,
            'crdate' => $GLOBALS['EXEC_TIME'],
            'tstamp' => $GLOBALS['EXEC_TIME'],
            'language' => $this->getLanguageUid(),
            'identifier' => $formDefinition->getIdentifier(),
            'data' => json_encode($formValues),
            'finisher_variables' => json_encode($this->getFinisherVariables()),
        ];

        /** @var ConnectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        /** @var \TYPO3\CMS\Core\Database\Connection */
        $connection = $connectionPool->getConnectionForTable('tx_formlog_entries');
        $connection->insert('tx_formlog_entries', $data);

        return null;
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

    /**
     * Returns all form values
     * Hidden fields will be rejected, if selected in finisher.
     *
     * @return array
     */
    protected function getFormValues(): array
    {
        $formValues = $this->finisherContext->getFormValues();

        if ($this->parseOption('saveHiddenElements')) {
            return $formValues;
        }

        $formDefinition = $this->finisherContext->getFormRuntime()->getFormDefinition();

        foreach ($formValues as $formIdentifier => $value) {
            $element = $formDefinition->getElementByIdentifier($formIdentifier);
            $renderingOptions = $element->getRenderingOptions();

            if (!$element instanceof FormElementInterface
                || (
                    isset($renderingOptions['_isCompositeFormElement'])
                    && $renderingOptions['_isCompositeFormElement'] === true
                )
                || (
                    isset($renderingOptions['_isHiddenFormElement'])
                    && $renderingOptions['_isHiddenFormElement'] === true
                )
                || (
                    isset($renderingOptions['_isReadOnlyFormElement'])
                    && $renderingOptions['_isReadOnlyFormElement'] === true
                )
            ) {
                unset($formValues[$formIdentifier]);
            }
        }

        return $formValues;
    }

    /**
     * Gets the current language UID
     *
     * @return int
     */
    private function getLanguageUid(): int
    {
        if (class_exists(Context::class)) {
            /** @var Context */
            $context = GeneralUtility::makeInstance(Context::class); // @phpstan-ignore-line

            return (int)$context->getPropertyFromAspect('language', 'id', 0);
        }

        return $this->frontendController->sys_language_uid; // @phpstan-ignore-line
    }
}
