<?php

declare(strict_types = 1);

namespace Pagemachine\Formlog\Mvc\View;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\View\ViewResolverInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\TemplateView;

final class FormatViewResolver implements ViewResolverInterface
{
    /**
     * @var string[]
     */
    private array $formatMap = [];

    private ObjectManager $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function map(string $format, string $viewClassName): void
    {
        $this->formatMap[$format] = $viewClassName;
    }

    public function resolve(string $controllerObjectName, string $actionName, string $format): ViewInterface
    {
        $viewClassName = $this->formatMap[$format] ?? TemplateView::class;

        return $this->objectManager->get($viewClassName);
    }
}
