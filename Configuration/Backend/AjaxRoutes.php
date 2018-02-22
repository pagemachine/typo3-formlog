<?php

return [
    'formlog_suggest' => [
        'path' => '/formlog/suggest/search',
        'target' => \Pagemachine\Formlog\Controller\Backend\FormLogSuggestController::class . '::searchAction',
    ],
];
