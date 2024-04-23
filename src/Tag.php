<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged;

use Closure;
use DecodeLabs\Coercion;
use DecodeLabs\Elementary\Attribute\ClassList\Container as ClassListContainer;
use DecodeLabs\Elementary\Attribute\ClassList\ContainerTrait as ClassListContainerTrait;
use DecodeLabs\Elementary\Style\Container as StyleContainer;
use DecodeLabs\Elementary\Style\ContainerTrait as StyleContainerTrait;
use DecodeLabs\Elementary\Tag as TagInterface;
use DecodeLabs\Elementary\TagTrait;
use DecodeLabs\Exceptional;
use DecodeLabs\Glitch\Dumpable;
use Generator;

class Tag implements
    Markup,
    TagInterface,
    ClassListContainer,
    StyleContainer,
    Dumpable
{
    use TagTrait;
    use ClassListContainerTrait;
    use StyleContainerTrait;
    use BufferProviderTrait;

    public const CLOSED_TAGS = [
        'area', 'base', 'br', 'col', 'command', 'embed',
        'hr', 'img', 'input', 'keygen', 'link', 'meta',
        'param', 'source', 'wbr'
    ];

    public const INLINE_TAGS = [
        'a', 'br', 'bdo', 'abbr', 'blink', 'nextid', 'acronym', 'basefont',
        'b', 'em', 'big', 'cite', 'input', 'spacer', 'listing',
        'i', 'rp', 'del', 'code', 'label', 'strike', 'marquee',
        'q', 'rt', 'ins', 'font', 'small', 'strong',
        's', 'tt', 'sub', 'mark',
        'u', 'xm', 'sup', 'nobr',
        'var', 'ruby', 'wbr', 'span', 'time',
    ];

    public const BOOLEAN_ATTRIBUTES = [
        'spellcheck'
    ];

    /**
     * Can tag be closed with full </tag>
     */
    public static function isClosableTagName(
        string $name
    ): bool {
        return !in_array(strtolower($name), self::CLOSED_TAGS);
    }

    /**
     * Should tag be single inline entity
     */
    public static function isInlineTagName(
        string $name
    ): bool {
        return in_array(strtolower($name), self::INLINE_TAGS);
    }




    /**
     * Set attribute value
     *
     * @return $this
     */
    public function setAttribute(
        string $key,
        mixed $value
    ): static {
        $key = strtolower($key);

        if ($key == 'class') {
            $this->setClasses($value);
            return $this;
        } elseif ($key == 'style') {
            $this->setStyles($value);
            return $this;
        }

        if ($value === null) {
            $this->removeAttribute($key);
            return $this;
        }

        if ($value instanceof Closure) {
            $value = $value($this);
        }

        if ($value instanceof Generator) {
            $value = iterator_to_array($value);
        }

        if (
            is_array($value) &&
            substr($key, 0, 1) == ':'
        ) {
            if (!$value = json_encode($value)) {
                throw Exceptional::UnexpectedValue(
                    'Unable to encode attribute value to JSON'
                );
            }
        } elseif (
            !is_bool($value) &&
            !$value instanceof Buffer
        ) {
            $value = Coercion::forceString($value);
        }

        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Get attribute value
     *
     * @return mixed
     */
    public function getAttribute(
        string $key
    ): mixed {
        $key = strtolower($key);

        if ($key == 'class') {
            return $this->getClasses();
        } elseif ($key == 'style') {
            return $this->getStyles();
        }

        return $this->attributes[$key] ?? null;
    }


    /**
     * Add data attributes with map
     *
     * @param array<string, mixed> $attributes
     * @return $this
     */
    public function setDataAttributes(
        array $attributes
    ): static {
        foreach ($attributes as $key => $value) {
            $this->setDataAttribute($key, $value);
        }

        return $this;
    }

    /**
     * Replace all data attributes with new map
     *
     * @param array<string, mixed> $attributes
     * @return $this
     */
    public function replaceDataAttributes(
        array $attributes
    ): static {
        $this->clearDataAttributes();
        $this->setDataAttributes($attributes);
        return $this;
    }

    /**
     * Get map of current data attributes
     *
     * @return array<string, mixed>
     */
    public function getDataAttributes(): array
    {
        $output = [];

        foreach ($this->attributes as $key => $value) {
            if (preg_match('/^data\-/i', $key)) {
                $output[(string)$key] = $value;
            }
        }

        return $output;
    }

    /**
     * Replace single data value
     *
     * @param mixed $value
     * @return $this
     */
    public function setDataAttribute(
        string $key,
        mixed $value
    ): static {
        $this->setAttribute('data-' . $key, $value);
        return $this;
    }

    /**
     * Retrieve data attribute value if set
     *
     * @return mixed
     */
    public function getDataAttribute(
        string $key
    ) {
        return $this->getAttribute('data-' . $key);
    }

    /**
     * Remove single data attribute
     * @return $this
     */
    public function removeDataAttribute(
        string ...$keys
    ): static {
        $keys = array_map(function ($key) {
            return 'data-' . $key;
        }, $keys);

        $this->removeAttribute(...$keys);
        return $this;
    }

    /**
     *  Have any of these data attributes been set?
     */
    public function hasDataAttribute(
        string ...$keys
    ): bool {
        $keys = array_map(function ($key) {
            return 'data-' . $key;
        }, $keys);

        return $this->hasAttribute(...$keys);
    }

    /**
     *  Have all of these data attributes been set?
     */
    public function hasDataAttributes(
        string ...$keys
    ): bool {
        $keys = array_map(function ($key) {
            return 'data-' . $key;
        }, $keys);

        return $this->hasAttributes(...$keys);
    }

    /**
     * Remove all data attributes
     * @return $this
     */
    public function clearDataAttributes(): static
    {
        foreach (array_keys($this->attributes) as $key) {
            if (preg_match('/^data\-/i', (string)$key)) {
                $this->removeAttribute((string)$key);
            }
        }

        return $this;
    }

    /**
     * How many data attributes have been set?
     */
    public function countDataAttributes(): int
    {
        $output = 0;

        foreach (array_keys($this->attributes) as $key) {
            if (preg_match('/^data\-/i', (string)$key)) {
                $output++;
            }
        }

        return $output;
    }



    /**
     * Toggle hidden attribute on/off
     *
     * @return $this
     */
    public function setHidden(
        bool $hidden
    ): Tag {
        $this->setAttribute('hidden', $hidden);
        return $this;
    }

    /**
     * Does this tag have hidden attr?
     */
    public function isHidden(): bool
    {
        return $this->hasAttribute('hidden');
    }

    /**
     * Set hidden attribute
     *
     * @return $this
     */
    public function hide(): Tag
    {
        $this->setAttribute('hidden', true);
        return $this;
    }

    /**
     * Remove hidden attribute
     *
     * @return $this
     */
    public function show(): Tag
    {
        $this->removeAttribute('hidden');
        return $this;
    }


    /**
     * Set title attribute
     *
     * @return $this
     */
    public function setTitle(
        ?string $title
    ): Tag {
        $this->setAttribute('title', $title);
        return $this;
    }

    /**
     * Get title attribute
     */
    public function getTitle(): ?string
    {
        return Coercion::toStringOrNull($this->getAttribute('title'));
    }
}
