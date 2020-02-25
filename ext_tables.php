<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_formlog_entries');

(function () {
    $extensionName = 'Formlog';
    $controllerName = \Pagemachine\Formlog\Controller\Backend\FormLogController::class;

    if (version_compare(\TYPO3\CMS\Core\Utility\VersionNumberUtility::getNumericTypo3Version(), '10.0.0') === -1) {
        $extensionName = 'Pagemachine.Formlog';
        $controllerName = 'Backend\\FormLog';
    }

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        $extensionName,
        'web',
        'list',
        'after:FormFormbuilder',
        [
            $controllerName => 'index, export',
        ],
        [
            'access' => 'user,group',
            'icon' => 'EXT:formlog/Resources/Public/Icons/module-list.svg',
            'labels' => 'LLL:EXT:formlog/Resources/Private/Language/locallang_mod_formlog.xlf',
            'navigationComponentId' => '',
            'inheritNavigationComponentFromMainModule' => false,
        ]
    );
})();
