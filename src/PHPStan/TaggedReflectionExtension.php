<?php

/**
 * @package PHPStanDecodeLabs
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\PHPStan;

use DecodeLabs\PHPStan\MethodReflection;
use DecodeLabs\Tagged\Factory as HtmlFactory;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodReflection as MethodReflectionInterface;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\ReflectionProvider;

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
        return $classReflection->getName() === HtmlFactory::class;
    }

    public function getMethod(
        ClassReflection $classReflection,
        string $methodName
    ): MethodReflectionInterface {
        $method = $this->reflectionProvider->getClass(HtmlFactory::class)->getNativeMethod('el');

        /** @var FunctionVariant $variant */
        $variant = $method->getVariants()[0];
        $params = array_slice($variant->getParameters(), 1);

        $newVariant = MethodReflection::alterVariant($variant, $params);
        return new MethodReflection($classReflection, $methodName, [$newVariant]);
    }
}
