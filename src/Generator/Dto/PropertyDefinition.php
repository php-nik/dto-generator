<?php

declare(strict_types=1);

namespace PhpNik\DtoGenerator\Generator\Dto;

final readonly class PropertyDefinition
{
    /**
     * @param array<string> $types
     */
    public function __construct(
        public string $name,
        public array $types,
        public bool $nullable,
    ) {}
}
