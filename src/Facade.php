<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged;

class Facade
{
    protected static $factory;

    /**
     * Temporary static facade
     * This will be replaced with a Veneer facade when ready
     */
    public static function __callStatic(string $name, array $args): Markup
    {
        if (!self::$factory) {
            self::$factory = new Factory();
        }

        return (self::$factory)->{$name}(...$args);
    }

    public static function getFactory(): Factory
    {
        if (!self::$factory) {
            self::$factory = new Factory();
        }

        return self::$factory;
    }
}
