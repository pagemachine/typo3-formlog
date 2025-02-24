<?php

use Pagemachine\Formlog\Controller\Backend\FormLogController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

ExtensionUtility::registerModule(
    'Formlog',
    'web',
    'list',
    'after:FormFormbuilder',
    [
        FormLogController::class => 'index, export',
    ],
    [
        'access' => 'user,group',
        'icon' => 'EXT:formlog/Resources/Public/Icons/module-list.svg',
        'labels' => 'LLL:EXT:formlog/Resources/Private/Language/locallang_mod_formlog.xlf',
        'navigationComponentId' => '',
        'inheritNavigationComponentFromMainModule' => false,
    ]
);
