<?php

use Pagemachine\Formlog\Controller\Backend\FormLogController;

return [
    'web_Formlog' => [
        'parent' => 'web',
        'position' => [
            'after' => 'web_FormFormbuilder',
        ],
        'access' => 'user',
        'labels' => 'LLL:EXT:formlog/Resources/Private/Language/locallang_mod_formlog.xlf',
        'icon' => 'EXT:formlog/Resources/Public/Icons/module-list.svg',
        'inheritNavigationComponentFromMainModule' => false,
        'extensionName' => 'Formlog',
        'controllerActions' => [
            FormLogController::class => [
                'index',
                'export',
            ],
        ],
    ],
];
