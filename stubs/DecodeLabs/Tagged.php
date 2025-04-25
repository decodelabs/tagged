<?php
/**
 * This is a stub file for IDE compatibility only.
 * It should not be included in your projects.
 */
namespace DecodeLabs;

use DecodeLabs\Veneer\Proxy as Proxy;
use DecodeLabs\Veneer\ProxyTrait as ProxyTrait;
use DecodeLabs\Tagged\Factory as Inst;
use DecodeLabs\Tagged\Plugins\Time as TimePlugin;
use DecodeLabs\Tagged\Plugins\Number as NumberPlugin;
use DecodeLabs\Tagged\Tag as Ref0;
use DecodeLabs\Tagged\Element as Ref1;
use DecodeLabs\Tagged\Component as Ref2;
use DecodeLabs\Tagged\Buffer as Ref3;
use DecodeLabs\Tagged\ContentCollection as Ref4;

class Tagged implements Proxy
{
    use ProxyTrait;

    public const Veneer = 'DecodeLabs\\Tagged';
    public const VeneerTarget = Inst::class;

    protected static Inst $_veneerInstance;
    public static TimePlugin $time;
    public static NumberPlugin $number;

    public static function tag(string $tagName, iterable $attributes = [], mixed ...$attributeList): Ref0 {
        return static::$_veneerInstance->tag(...func_get_args());
    }
    public static function el(string $tagName, mixed $content = NULL, iterable $attributes = [], mixed ...$attributeList): Ref1 {
        return static::$_veneerInstance->el(...func_get_args());
    }
    public static function component(string $tagName, mixed ...$args): Ref2 {
        return static::$_veneerInstance->component(...func_get_args());
    }
    public static function raw(mixed $html, bool $escaped = false): Ref3 {
        return static::$_veneerInstance->raw(...func_get_args());
    }
    public static function wrap(mixed ...$content): Ref3 {
        return static::$_veneerInstance->wrap(...func_get_args());
    }
    public static function render(mixed ...$content): string {
        return static::$_veneerInstance->render(...func_get_args());
    }
    public static function content(mixed ...$content): Ref4 {
        return static::$_veneerInstance->content(...func_get_args());
    }
    public static function esc(mixed $value): ?string {
        return static::$_veneerInstance->esc(...func_get_args());
    }
    public static function jsonSerialize(): mixed {
        return static::$_veneerInstance->jsonSerialize();
    }
};
