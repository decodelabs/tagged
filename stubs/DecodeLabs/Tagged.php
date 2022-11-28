<?php
/**
 * This is a stub file for IDE compatibility only.
 * It should not be included in your projects.
 */
namespace DecodeLabs;

use DecodeLabs\Veneer\Proxy as Proxy;
use DecodeLabs\Veneer\ProxyTrait as ProxyTrait;
use DecodeLabs\Tagged\Factory as Inst;
use DecodeLabs\Tagged\Plugins\Embed as EmbedPlugin;
use DecodeLabs\Tagged\Plugins\Icon as IconPlugin;
use DecodeLabs\Tagged\Plugins\Time as TimePlugin;
use DecodeLabs\Tagged\Plugins\Number as NumberPlugin;
use DecodeLabs\Veneer\Plugin\Wrapper as PluginWrapper;
use DecodeLabs\Tagged\Tag as Ref0;
use DecodeLabs\Tagged\Element as Ref1;
use DecodeLabs\Tagged\Buffer as Ref2;
use DecodeLabs\Tagged\ContentCollection as Ref3;
use Stringable as Ref4;

class Tagged implements Proxy
{
    use ProxyTrait;

    const VENEER = 'DecodeLabs\Tagged';
    const VENEER_TARGET = Inst::class;

    public static Inst $instance;
    /** @var EmbedPlugin|PluginWrapper<EmbedPlugin> $embed */
    public static EmbedPlugin|PluginWrapper $embed;
    /** @var IconPlugin|PluginWrapper<IconPlugin> $icon */
    public static IconPlugin|PluginWrapper $icon;
    /** @var TimePlugin|PluginWrapper<TimePlugin> $time */
    public static TimePlugin|PluginWrapper $time;
    /** @var NumberPlugin|PluginWrapper<NumberPlugin> $number */
    public static NumberPlugin|PluginWrapper $number;

    public static function tag(string $name, ?array $attributes = NULL): Ref0 {
        return static::$instance->tag(...func_get_args());
    }
    public static function el(string $name, mixed $content = NULL, ?array $attributes = NULL): Ref1 {
        return static::$instance->el(...func_get_args());
    }
    public static function raw(mixed $html): Ref2 {
        return static::$instance->raw(...func_get_args());
    }
    public static function wrap(mixed ...$content): Ref2 {
        return static::$instance->wrap(...func_get_args());
    }
    public static function content(mixed ...$content): Ref3 {
        return static::$instance->content(...func_get_args());
    }
    public static function list(?iterable $list, string $container, ?string $name, ?callable $callback = NULL, ?array $attributes = NULL): Ref1 {
        return static::$instance->list(...func_get_args());
    }
    public static function elements(?iterable $list, ?string $name, ?callable $callback = NULL, ?array $attributes = NULL): Ref2 {
        return static::$instance->elements(...func_get_args());
    }
    public static function loop(?iterable $list, ?callable $callback = NULL): Ref2 {
        return static::$instance->loop(...func_get_args());
    }
    public static function uList(?iterable $list, ?callable $renderer = NULL, ?array $attributes = NULL): Ref1 {
        return static::$instance->uList(...func_get_args());
    }
    public static function oList(?iterable $list, ?callable $renderer = NULL, ?array $attributes = NULL): Ref1 {
        return static::$instance->oList(...func_get_args());
    }
    public static function dList(?iterable $list, ?callable $renderer = NULL, ?array $attributes = NULL): Ref1 {
        return static::$instance->dList(...func_get_args());
    }
    public static function iList(?iterable $list, ?callable $renderer = NULL, ?string $delimiter = NULL, ?string $finalDelimiter = NULL, ?int $limit = NULL): Ref1 {
        return static::$instance->iList(...func_get_args());
    }
    public static function image(Ref4|string|null $url, ?string $alt = NULL, string|int|null $width = NULL, string|int|null $height = NULL): Ref1 {
        return static::$instance->image(...func_get_args());
    }
    public static function esc(mixed $value): ?string {
        return static::$instance->esc(...func_get_args());
    }
};
