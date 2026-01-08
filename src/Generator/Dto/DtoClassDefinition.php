<?php

declare(strict_types=1);

namespace PhpNik\DtoGenerator\Generator\Dto;

final readonly class DtoClassDefinition
{
    /**
     * @param array<PropertyDefinition> $properties
     */
    public function __construct(
        public string $namespace,
        public string $className,
        public array $properties,
    ) {}
}
