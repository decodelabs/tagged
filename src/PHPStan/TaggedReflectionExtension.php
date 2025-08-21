<?php

/**
 * @package PHPStanDecodeLabs
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\PHPStan;

use DecodeLabs\Archetype;
use DecodeLabs\Monarch;
use DecodeLabs\Tagged;
use DecodeLabs\Tagged\Component;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodReflection as MethodReflectionInterface;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;

class TaggedReflectionExtension implements MethodsClassReflectionExtension
{
    protected ReflectionProvider $reflectionProvider;
    protected Archetype $archetype;

    public function __construct(
        ReflectionProvider $reflectionProvider
    ) {
        $this->reflectionProvider = $reflectionProvider;
        $this->archetype = Monarch::getService(Archetype::class);
    }

    public function hasMethod(
        ClassReflection $classReflection,
        string $methodName
    ): bool {
        if ($classReflection->getName() !== Tagged::class) {
            return false;
        }

        if (!str_starts_with($methodName, '@')) {
            return true;
        }

        if (null === ($name = $this->normalizeName($methodName))) {
            return false;
        }

        return (bool)$this->archetype->tryResolve(Component::class, $name);
    }

    public function getMethod(
        ClassReflection $classReflection,
        string $methodName
    ): MethodReflectionInterface {
        if (!str_starts_with($methodName, '@')) {
            // Element
            $method = $this->reflectionProvider->getClass(Tagged::class)->getNativeMethod('el');

            /** @var FunctionVariant $variant */
            $variant = clone $method->getVariants()[0];
            $params = array_slice($variant->getParameters(), 1);

            $newVariant = MethodReflection::alterVariant($variant, $params);
            $output = new MethodReflection($classReflection, $methodName, [$newVariant]);
            $output->setStatic(true);
            return $output;
        }

        // Component
        $name = $this->normalizeName($methodName);
        $class = $this->archetype->resolve(Component::class, $name);

        $method = $this->reflectionProvider->getClass($class)->getNativeMethod('__construct');
        /** @var FunctionVariant $variant */
        $variant = $method->getVariants()[0];

        $newVariant = MethodReflection::alterVariant(
            $variant,
            $variant->getParameters(),
            new ObjectType($class)
        );

        $output = new MethodReflection($classReflection, $methodName, [$newVariant]);
        $output->setStatic(true);
        return $output;
    }

    protected function normalizeName(
        string $name
    ): ?string {
        if (!preg_match('/^@([a-zA-Z0-9_-]+)([^a-zA-Z0-9_].*)?$/', $name, $matches)) {
            return null;
        }

        $name = $matches[1];
        $name = str_replace('-', ' ', $name);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);

        if ($name === 'List') {
            $name = 'ContainedList';
        }

        return $name;
    }
}
