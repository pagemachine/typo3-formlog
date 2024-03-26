<?php

declare(strict_types=1);

namespace Pagemachine\Formlog\Tests\Unit\Export;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */
use Pagemachine\Formlog\Export\AbstractExport;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Testcase for Pagemachine\Formlog\Export\AbstractExport
 */
final class AbstractExportTest extends UnitTestCase
{
    #[Test]
    public function sortsColumns(): void
    {
        $export = new class () extends AbstractExport {
            public function exposeColumnPaths(): array
            {
                return $this->getColumnPaths();
            }

            public function dump(iterable $items): void {}
        };

        $configuration = [
            'columns' => [
                99 => [
                    'property' => 'custom',
                ],
                10 => [
                    'property' => 'uid',
                ],
                20 => [
                    'property' => 'page.title',
                ],
            ],
        ];
        $export->setConfiguration($configuration);

        $result = $export->exposeColumnPaths();
        $expected = [
            'uid',
            'page.title',
            'custom',
        ];

        self::assertEquals($expected, $result);
    }
}
