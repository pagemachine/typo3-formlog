<?php

use Pagemachine\Formlog\Form\Element\JSONDataElement;
use Pagemachine\Formlog\Updates\FormLogEntryPageUpdate;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Scheduler\Task\TableGarbageCollectionTask;

defined('TYPO3') or die();

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1490193269] = [
    'nodeName' => 'jsonData',
    'priority' => 10,
    'class' => JSONDataElement::class,
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['EXT:form/Resources/Private/Language/Database.xlf'][1519643592] = 'EXT:formlog/Resources/Private/Language/Database.xlf';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['de']['EXT:form/Resources/Private/Language/Database.xlf'][1519643592] = 'EXT:formlog/Resources/Private/Language/de.Database.xlf';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update'][FormLogEntryPageUpdate::class] = FormLogEntryPageUpdate::class;

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][TableGarbageCollectionTask::class]['options']['tables']['tx_formlog_entries'] = [
    'dateField' => 'tstamp',
    'expirePeriod' => 180,
];

ExtensionManagementUtility::addTypoScriptSetup(<<<TYPOSCRIPT
module {
  tx_form {
    settings {
      yamlConfigurations {
        1520412939 = EXT:formlog/Configuration/Form/Setup.yaml
      }
    }
  }

  tx_formlog {
    settings {
      dateTimeFormat =

      # list {
      #   columns {
      #   }
      # }

      export {
        columns {
          10 {
            property = uid
            label = formlog.entry.uid
          }
          20 {
            property = page.title
            label = formlog.entry.page.title
          }
          30 {
            property = identifier
            label = formlog.entry.identifier
          }
          40 {
            property = language.title
            label = formlog.entry.language
          }
          50 {
            property = submissionDate
            label = formlog.entry.submissionDate
          }
        }
      }
    }
  }
}
TYPOSCRIPT);
