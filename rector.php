<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Ssch\TYPO3Rector\Set\Typo3LevelSetList;
use Ssch\TYPO3Rector\TYPO313\v0\MigrateTypoScriptFrontendControllerReadOnlyPropertiesRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/Classes',
        __DIR__ . '/Configuration/TCA',
        __DIR__ . '/Tests',
    ])
    ->withRootFiles()
    ->withImportNames(
        importShortClasses: false,
        removeUnusedImports: true,
    )
    ->withPhpSets()
    ->withComposerBased(
        phpunit: true,
    )
    ->withSets([
        Typo3LevelSetList::UP_TO_TYPO3_13,
    ])
    ->withSkip([
        ...(
            class_exists(MigrateTypoScriptFrontendControllerReadOnlyPropertiesRector::class) ? [
                MigrateTypoScriptFrontendControllerReadOnlyPropertiesRector::class => [
                    __DIR__ . '/Classes/Domain/Form/Finishers/LoggerFinisher.php',
                ],
            ] : []
        ),
    ])
;
