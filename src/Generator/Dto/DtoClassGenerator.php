<?php

declare(strict_types=1);

namespace PhpNik\DtoGenerator\Generator\Dto;

use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\DeclareItem;
use PhpParser\Node\Scalar\Int_;
use PhpParser\PrettyPrinter\Standard;

final readonly class DtoClassGenerator implements DtoClassGeneratorInterface
{
    private const NATIVE_TYPES = [
        // scalars
        'bool', 'boolean',
        'int', 'integer',
        'float', 'double',
        'string',

        // pseudo / builtin
        'array',
        'object',
        'callable',
        'iterable',
        'resource',
        'mixed',
        'null',
        'false',
        'true',
        'void',
        'never',
    ];
    public function generate(DtoClassDefinition $definition): string
    {
        $factory = new BuilderFactory();

        // namespace
        $namespace = $factory->namespace($definition->namespace);

        // constructor params
        $params = [];

        foreach ($definition->properties as $property) {
            $type = $this->mapType($property);

            $param = $factory->param($property->name)
                ->setType($type)
                ->makePublic();

            $params[] = $param;
        }

        // constructor
        $constructor = $factory->method('__construct')
            ->makePublic()
            ->addParams($params);

        // class
        $class = $factory->class($definition->className)
            ->makeFinal()
            ->makeReadonly()
            ->addStmt($constructor);

        $namespace->addStmt($class);

        $ast = [
            new Node\Stmt\Declare_([
                new DeclareItem('strict_types', new Int_(1)),
            ]),
            $namespace->getNode(),
        ];

        return (new Standard())->prettyPrintFile($ast);
    }

    private function mapType(PropertyDefinition $property): Node\UnionType|Node\NullableType|Node\Identifier|Node\Name
    {
        if (count($property->types) === 0) {
            throw new \InvalidArgumentException('Property types must not be empty');
        }

        $types = [];
        foreach ($property->types as $type) {
            $types[] = $this->mapSingleType($type);
        }

        if (count($types) > 1) {
            return new Node\UnionType($types);
        }

        $type = $types[0];

        return $property->nullable ? new Node\NullableType($type) : $type;
    }

    private function mapSingleType(string $type): Node\Identifier|Node\Name
    {
        if ($this->isNativeType($type)) {
            return new Node\Identifier($type);
        }

        if (!str_starts_with($type, '\\')) {
            $type = '\\' . $type;
        }

        return new Node\Name($type);
    }

    private function isNativeType(string $type): bool
    {
        return in_array(strtolower($type), self::NATIVE_TYPES, true);
    }
}
