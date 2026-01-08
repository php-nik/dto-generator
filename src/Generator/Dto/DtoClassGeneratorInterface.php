<?php

declare(strict_types=1);

namespace PhpNik\DtoGenerator\Generator\Dto;

interface DtoClassGeneratorInterface
{
    public function generate(DtoClassDefinition $definition): string;
}
