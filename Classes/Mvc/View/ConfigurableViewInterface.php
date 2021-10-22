<?php

declare(strict_types = 1);

namespace Pagemachine\Formlog\Mvc\View;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use TYPO3\CMS\Extbase\Mvc\View\ViewInterface as ExtbaseViewInterface;

/**
 * Interface for configurable views
 */
interface ConfigurableViewInterface extends ExtbaseViewInterface
{
    /**
     * Set configuration for this view
     */
    public function setConfiguration(array $configuration): void;
}
