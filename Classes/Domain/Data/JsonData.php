<?php

declare(strict_types = 1);

namespace Pagemachine\Formlog\Domain\Data;

use TYPO3\CMS\Core\Type\TypeInterface;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

final class JsonData extends \ArrayObject implements TypeInterface
{
    public function __construct(string $jsonString)
    {
        $data = json_decode($jsonString, true, 512, \JSON_THROW_ON_ERROR);

        parent::__construct($data);
    }

    public function __toString(): string
    {
        return json_encode($this);
    }
}
