<?php

declare(strict_types = 1);

namespace Pagemachine\Formlog\Domain\Form\Finishers;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\FileReference as CoreFileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference as ExtbaseFileReference;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;

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
    ];

    /**
     * @return string|null
     */
    protected function executeInternal()
    {
        $formRuntime = $this->finisherContext->getFormRuntime();
        $formDefinition = $formRuntime->getFormDefinition();
        $context = GeneralUtility::makeInstance(Context::class);
        $now = $context->getPropertyFromAspect('date', 'timestamp');

        $data = [
            'pid' => $this->getTypoScriptFrontendController()->id,
            'crdate' => $now,
            'tstamp' => $now,
            'language' => (int)$context->getPropertyFromAspect('language', 'id', 0),
            'identifier' => $formDefinition->getIdentifier(),
            'data' => json_encode($this->getFormValues()),
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
     * Get normalized form values
     */
    protected function getFormValues(): array
    {
        $normalizedFormValues = [];

        foreach ($this->finisherContext->getFormValues() as $identifier => $formValue) {
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
