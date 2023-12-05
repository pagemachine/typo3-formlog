<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/Classes',
        __DIR__ . '/Tests',
    ]);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_80,
        PHPUnitSetList::PHPUNIT_91,
    ]);

    $rectorConfig->skip([
        \Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector::class,
    ]);
};
