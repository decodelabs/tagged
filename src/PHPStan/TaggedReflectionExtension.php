<?php

/**
 * @package PHPStanDecodeLabs
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\PHPStan;

use DecodeLabs\Archetype;
use DecodeLabs\PHPStan\MethodReflection;
use DecodeLabs\Tagged\Component;
use DecodeLabs\Tagged\Factory as HtmlFactory;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodReflection as MethodReflectionInterface;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;

class TaggedReflectionExtension implements MethodsClassReflectionExtension
{
    protected ReflectionProvider $reflectionProvider;

    public function __construct(
        ReflectionProvider $reflectionProvider
    ) {
        $this->reflectionProvider = $reflectionProvider;
    }

    public function hasMethod(
        ClassReflection $classReflection,
        string $methodName
    ): bool {
        if($classReflection->getName() !== HtmlFactory::class) {
            return false;
        }

        if(!str_starts_with($methodName, '@')) {
            return true;
        }

        if(null === ($name = $this->normalizeName($methodName))) {
            return false;
        }

        return (bool)Archetype::tryResolve(Component::class, $name);
    }

    public function getMethod(
        ClassReflection $classReflection,
        string $methodName
    ): MethodReflectionInterface {
        if(!str_starts_with($methodName, '@')) {
            // Element
            $method = $this->reflectionProvider->getClass(HtmlFactory::class)->getNativeMethod('el');

            /** @var FunctionVariant $variant */
            $variant = clone $method->getVariants()[0];
            $params = array_slice($variant->getParameters(), 1);

            $newVariant = MethodReflection::alterVariant($variant, $params);
            return new MethodReflection($classReflection, $methodName, [$newVariant]);
        }

        // Component
        $name = $this->normalizeName($methodName);
        $class = Archetype::resolve(Component::class, $name);
        $method = $this->reflectionProvider->getClass($class)->getNativeMethod('__construct');
        /** @var FunctionVariant $variant */
        $variant = $method->getVariants()[0];

        $newVariant = MethodReflection::alterVariant(
            $variant,
            $variant->getParameters(),
            new ObjectType($class)
        );

        return new MethodReflection($classReflection, $methodName, [$newVariant]);
    }

    protected function normalizeName(
        string $name
    ): ?string {
        if(!preg_match('/^@([a-zA-Z0-9_-]+)([^a-zA-Z0-9_].*)?$/', $name, $matches)) {
            return null;
        }

        $name = $matches[1];
        $name = str_replace('-', ' ', $name);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);

        if($name === 'List') {
            $name = 'ContainedList';
        }

        // DELETE next version
        if($name === 'Image') {
            $name = 'Img';
        }

        return $name;
    }
}
