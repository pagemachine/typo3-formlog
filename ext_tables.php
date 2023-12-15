<?php

defined('TYPO3') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_formlog_entries');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    'Formlog',
    'web',
    'list',
    'after:FormFormbuilder',
    [
        \Pagemachine\Formlog\Controller\Backend\FormLogController::class => 'index, export',
    ],
    [
        'access' => 'user,group',
        'icon' => 'EXT:formlog/Resources/Public/Icons/module-list.svg',
        'labels' => 'LLL:EXT:formlog/Resources/Private/Language/locallang_mod_formlog.xlf',
        'navigationComponentId' => '',
        'inheritNavigationComponentFromMainModule' => false,
    ]
);
