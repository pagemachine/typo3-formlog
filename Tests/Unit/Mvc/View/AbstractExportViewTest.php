<?php
declare(strict_types = 1);

namespace Pagemachine\Formlog\Tests\Unit\Mvc\View;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Pagemachine\Formlog\Mvc\View\Export\AbstractExportView;

/**
 * Testcase for Pagemachine\Formlog\Mvc\View\Export\AbstractExportView
 */
final class AbstractExportViewTest extends UnitTestCase
{
    /**
     * @test
     */
    public function sortsColumns(): void
    {
        $view = new class extends AbstractExportView {
            public function exposeColumnPaths(): array
            {
                return $this->getColumnPaths();
            }

            public function render()
            {
            }
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
        $view->setConfiguration($configuration);

        $result = $view->exposeColumnPaths();
        $expected = [
            'uid',
            'page.title',
            'custom',
        ];

        $this->assertEquals($expected, $result);
    }
}
