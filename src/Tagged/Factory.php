<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged;

use Closure;
use DecodeLabs\Archetype;
use DecodeLabs\Coercion;
use DecodeLabs\Exceptional;
use DecodeLabs\Glitch\Proxy as Glitch;
use DecodeLabs\Tagged;
use DecodeLabs\Tagged\Plugins\Number as NumberPlugin;
use DecodeLabs\Tagged\Plugins\Time as TimePlugin;
use DecodeLabs\Veneer;
use DecodeLabs\Veneer\Plugin;
use Stringable;
use Throwable;

class Factory implements Markup
{
    #[Plugin(lazy: true)]
    public TimePlugin $time;

    #[Plugin(lazy: true)]
    public NumberPlugin $number;


    /**
     * Instance shortcut to el
     *
     * @param array<string,mixed>|null $attributes
     */
    public function __invoke(
        string $name,
        mixed $content,
        ?array $attributes = null
    ): Element {
        return Element::create($name, $content, $attributes);
    }

    /**
     * Call named widget from instance
     *
     * @param array<mixed> $args
     */
    public function __call(
        string $name,
        array $args
    ): Element|Component {
        if(str_starts_with($name, '@')) {
            return $this->component(substr($name, 1), ...$args);
        }

        return Element::create($name, ...$args);
    }

    /**
     * Dummy string generator to satisfy Markup dep
     */
    public function __toString(): string
    {
        return '';
    }




    /**
     * Create a standalone tag
     *
     * @param array<string,mixed>|null $attributes
     */
    public function tag(
        string $name,
        ?array $attributes = null
    ): Tag {
        return new Tag($name, $attributes);
    }

    /**
     * Create a standalone element
     *
     * @param array<string, mixed>|null $attributes
     */
    public function el(
        string $name,
        mixed $content = null,
        ?array $attributes = null
    ): Element {
        return Element::create($name, $content, $attributes);
    }

    /**
     * Create a standalone component
     */
    public function component(
        string $name,
        mixed ...$args
    ): Component {
        if(!preg_match('/^([a-zA-Z0-9_-]+)([^a-zA-Z0-9_].*)?$/', $name, $matches)) {
            throw Exceptional::InvalidArgument(
                message: 'Invalid component name: '.$name
            );
        }

        $name = $matches[1];
        $def = $matches[2] ?? null;

        $name = str_replace('-', ' ', $name);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);

        if($name === 'List') {
            $name = 'ContainedList';
        }

        $class = Archetype::resolve(Component::class, ucfirst($name));
        $output = new $class(...$args);

        if($def) {
            // Re-add tag definition and parse
            $tagName = $output->tagName.$def;
            $output->tagName = $tagName;
        }

        return $output;
    }

    /**
     * Wrap raw html string
     */
    public function raw(
        mixed $html
    ): Buffer {
        return new Buffer(Coercion::toString($html));
    }

    /**
     * Normalize arbitrary content
     */
    public function wrap(
        mixed ...$content
    ): Buffer {
        return ContentCollection::normalize($content);
    }

    /**
     * Wrap arbitrary content as collection
     */
    public function content(
        mixed ...$content
    ): ContentCollection {
        return new ContentCollection($content);
    }



    /**
     * Escape HTML
     */
    public function esc(
        mixed $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        try {
            return htmlspecialchars(Coercion::toString($value), ENT_QUOTES, 'UTF-8');
        } catch (Throwable $e) {
            Glitch::logException($e);
            return Coercion::toString($value);
        }
    }

    /**
     * Serialize to json
     */
    public function jsonSerialize(): mixed
    {
        return (string)$this;
    }
}


// Register the Veneer proxy
Veneer\Manager::getGlobalManager()->register(
    Factory::class,
    Tagged::class
);
