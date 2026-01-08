<?php

declare(strict_types=1);

namespace PhpNik\DtoGenerator\Tests\Unit;

use PhpNik\DtoGenerator\Generator\Dto\DtoClassDefinition;
use PhpNik\DtoGenerator\Generator\Dto\DtoClassGenerator;
use PhpNik\DtoGenerator\Generator\Dto\DtoClassGeneratorInterface;
use PhpNik\DtoGenerator\Generator\Dto\PropertyDefinition;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DtoClassGeneratorTest extends TestCase
{
    private ?DtoClassGeneratorInterface $generator = null;

    protected function setUp(): void
    {
        $this->generator = new DtoClassGenerator();
    }

    #[Test]
    #[DataProvider('getData')]
    public function generate(DtoClassDefinition $definition): void
    {

        $code = $this->generator->generate($definition);

        $this->assertNotEmpty($code);

        eval(str_replace('<?php', '', $code));

        $fqcn = $definition->namespace.'\\'.$definition->className;

        $this->assertTrue(class_exists($fqcn));

        $classReflection = new \ReflectionClass($fqcn);

        $this->assertSame($definition->namespace, $classReflection->getNamespaceName());
        $this->assertSame($definition->className, $classReflection->getShortName());
        $this->assertSame($fqcn, $classReflection->getName());
        $this->assertTrue($classReflection->isFinal());
        $this->assertTrue($classReflection->isReadOnly());
        $this->assertCount(count($definition->properties), $classReflection->getProperties());

        foreach ($definition->properties as $propertyDefinition) {
            $propertyReflection = new \ReflectionProperty($fqcn, $propertyDefinition->name);

            $this->assertTrue($propertyReflection->isPromoted());
            $this->assertTrue($propertyReflection->isPublic());
            $this->assertTrue($propertyReflection->hasType());
            $this->assertNotNull($propertyReflection->getType());
            $this->assertSame($propertyDefinition->nullable, $propertyReflection->getType()?->allowsNull());
            if (count($propertyDefinition->types) > 1) {
                $this->assertSame(\ReflectionUnionType::class, get_class($propertyReflection->getType()));
                foreach ($propertyReflection->getType()->getTypes() as $key => $type) {
                    $this->assertSame(\ReflectionNamedType::class, get_class($type));
                    $this->assertSame($propertyDefinition->types[$key], $type->getName());
                }
            } else {
                $this->assertSame(\ReflectionNamedType::class, get_class($propertyReflection->getType()));
                $this->assertSame($propertyDefinition->types[0], $propertyReflection->getType()->getName());
            }
        }
    }

    /**
     * @return iterable<array<DtoClassDefinition>>
     */
    public static function getData(): iterable
    {
        yield [new DtoClassDefinition('abc', 'eee', [])];

        yield [new DtoClassDefinition('qwe', 'Zxc', [
            new PropertyDefinition('one', ['array'], true),
            new PropertyDefinition('two', ['string'], false),
            new PropertyDefinition('three', ['string', 'int'], false),
            new PropertyDefinition('four', [PropertyDefinition::class], true),
        ])];
    }
}
