<?php

use Pagemachine\Formlog\Form\Element\JSONDataElement;
use TYPO3\CMS\Scheduler\Task\TableGarbageCollectionTask;

defined('TYPO3') or die();

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1490193269] = [
    'nodeName' => 'jsonData',
    'priority' => 10,
    'class' => JSONDataElement::class,
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['EXT:form/Resources/Private/Language/Database.xlf'][1519643592] = 'EXT:formlog/Resources/Private/Language/Database.xlf';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['de']['EXT:form/Resources/Private/Language/Database.xlf'][1519643592] = 'EXT:formlog/Resources/Private/Language/de.Database.xlf';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][TableGarbageCollectionTask::class]['options']['tables']['tx_formlog_entries'] = [
    'dateField' => 'tstamp',
    'expirePeriod' => 180,
];
