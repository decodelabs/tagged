<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs;

use DecodeLabs\Kingdom\Service;
use DecodeLabs\Kingdom\ServiceTrait;
use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\Component;
use DecodeLabs\Tagged\ContentCollection;
use DecodeLabs\Tagged\Element;
use DecodeLabs\Tagged\Tag;
use Throwable;

/**
 * @phpstan-import-type TAttributeValue from Tag
 * @phpstan-import-type TAttributeInput from Tag
 */
class Tagged implements Service
{
    use ServiceTrait;

    /**
     * @param iterable<string,TAttributeInput> $attributes
     * @param TAttributeInput ...$attributeList
     */
    public function __invoke(
        string $tagName,
        mixed $content,
        iterable $attributes = [],
        mixed ...$attributeList
    ): Element {
        return Element::create($tagName, $content, $attributes, ...$attributeList);
    }

    /**
     * @param array<mixed> $args
     */
    public static function __callStatic(
        string $tagName,
        array $args
    ): Element|Component {
        if (str_starts_with($tagName, '@')) {
            return static::component(substr($tagName, 1), ...$args);
        }

        return Element::create($tagName, ...$args);
    }




    /**
     * @param iterable<string,TAttributeInput> $attributes
     * @param TAttributeInput ...$attributeList
     */
    public static function tag(
        string $tagName,
        iterable $attributes = [],
        mixed ...$attributeList
    ): Tag {
        /** @var array<string,TAttributeInput> $attributes */
        $attributes = array_merge(
            iterator_to_array($attributes),
            $attributeList
        );

        return new Tag($tagName, $attributes);
    }

    /**
     * @param iterable<string,TAttributeInput> $attributes
     * @param TAttributeInput ...$attributeList
     */
    public static function el(
        string $tagName,
        mixed $content = null,
        iterable $attributes = [],
        mixed ...$attributeList
    ): Element {
        return Element::create($tagName, $content, $attributes, ...$attributeList);
    }

    public static function component(
        string $tagName,
        mixed ...$args
    ): Component {
        if (!preg_match('/^([a-zA-Z0-9_-]+)([^a-zA-Z0-9_].*)?$/', $tagName, $matches)) {
            throw Exceptional::InvalidArgument(
                message: 'Invalid component name: ' . $tagName
            );
        }

        $name = $matches[1];
        $def = $matches[2] ?? null;

        $name = str_replace('-', ' ', $name);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);

        if ($name === 'List') {
            $name = 'ContainedList';
        }

        /** @var array<string,mixed> $args */
        $output = new Slingshot()->resolveNamedInstance(Component::class, ucfirst($name), $args);

        if ($def) {
            // Re-add tag definition and parse
            $tagName = $output->tagName . $def;
            $output->tagName = $tagName;
        }

        return $output;
    }

    public static function raw(
        mixed $html,
        bool $escaped = false
    ): Buffer {
        return new Buffer(
            Coercion::toString($html),
            $escaped
        );
    }

    public static function wrap(
        mixed ...$content
    ): Buffer {
        return ContentCollection::normalize($content);
    }

    public static function render(
        mixed ...$content
    ): string {
        return (string)ContentCollection::normalize($content);
    }

    public static function content(
        mixed ...$content
    ): ContentCollection {
        return new ContentCollection($content);
    }



    public static function esc(
        mixed $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        try {
            return htmlspecialchars(Coercion::toString($value), ENT_QUOTES, 'UTF-8');
        } catch (Throwable $e) {
            Monarch::logException($e);
            return Coercion::toString($value);
        }
    }
}
