<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Html;

use DecodeLabs\Collections\AttributeContainer;
use DecodeLabs\Elementary\Attribute\ClassList\Container as ClassListContainer;
use DecodeLabs\Elementary\Attribute\ClassList\ContainerTrait as ClassListContainerTrait;
use DecodeLabs\Elementary\Buffer as BufferInterface;
use DecodeLabs\Elementary\Style\Container as StyleContainer;
use DecodeLabs\Elementary\Style\ContainerTrait as StyleContainerTrait;
use DecodeLabs\Elementary\Tag as TagInterface;
use DecodeLabs\Elementary\TagTrait;
use DecodeLabs\Glitch\Dumpable;
use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\Markup;

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
                   'var', 'ruby',
                   'wbr', 'span',
                          'time',
    ];

    public const BOOLEAN_ATTRIBUTES = [
        'spellcheck'
    ];

    /**
     * Can tag be closed with full </tag>
     */
    public static function isClosableTagName(string $name): bool
    {
        return !in_array(strtolower($name), self::CLOSED_TAGS);
    }

    /**
     * Should tag be single inline entity
     */
    public static function isInlineTagName(string $name): bool
    {
        return in_array(strtolower($name), self::INLINE_TAGS);
    }




    /**
     * Set attribute value
     */
    public function setAttribute(string $key, $value): AttributeContainer
    {
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

        if (!is_bool($value)) {
            $value = (string)$value;
        }

        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Get attribute value
     */
    public function getAttribute(string $key)
    {
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
     */
    public function setDataAttributes(array $attributes): AttributeContainer
    {
        foreach ($attributes as $key => $value) {
            $this->setDataAttribute($key, $value);
        }

        return $this;
    }

    /**
     * Replace all data attributes with new map
     */
    public function replaceDataAttributes(array $attributes): AttributeContainer
    {
        $this->clearDataAttributes();
        return $this->setDataAttributes($attributes);
    }

    /**
     * Get map of current data attributes
     */
    public function getDataAttributes(): array
    {
        $output = [];

        foreach ($this->attributes as $key => $value) {
            if (preg_match('/^data\-/i', $key)) {
                $output[$key] = $value;
            }
        }

        return $output;
    }

    /**
     * Replace single data value
     */
    public function setDataAttribute(string $key, $value): AttributeContainer
    {
        return $this->setAttribute('data-' . $key, $value);
    }

    /**
     * Retrieve data attribute value if set
     */
    public function getDataAttribute(string $key)
    {
        return $this->getAttribute('data-' . $key);
    }

    /**
     * Remove single data attribute
     */
    public function removeDataAttribute(string ...$keys): AttributeContainer
    {
        $keys = array_map(function ($key) {
            return 'data-' . $key;
        }, $keys);

        return $this->removeAttribute(...$keys);
    }

    /**
     *  Have any of these data attributes been set?
     */
    public function hasDataAttribute(string ...$keys): bool
    {
        $keys = array_map(function ($key) {
            return 'data-' . $key;
        }, $keys);

        return $this->hasAttribute(...$keys);
    }

    /**
     *  Have all of these data attributes been set?
     */
    public function hasDataAttributes(string ...$keys): bool
    {
        $keys = array_map(function ($key) {
            return 'data-' . $key;
        }, $keys);

        return $this->hasAttributes(...$keys);
    }

    /**
     * Remove all data attributes
     */
    public function clearDataAttributes(): AttributeContainer
    {
        foreach (array_keys($this->attributes) as $key) {
            if (preg_match('/^data\-/i', $key)) {
                $this->removeAttribute($key);
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
            if (preg_match('/^data\-/i', $key)) {
                $output++;
            }
        }

        return $output;
    }



    /**
     * Toggle hidden attribute on/off
     */
    public function setHidden(bool $hidden): TagInterface
    {
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
     */
    public function hide(): TagInterface
    {
        $this->setAttribute('hidden', true);
        return $this;
    }

    /**
     * Remove hidden attribute
     */
    public function show(): TagInterface
    {
        $this->removeAttribute('hidden');
        return $this;
    }


    /**
     * Set title attribute
     */
    public function setTitle(?string $title): TagInterface
    {
        $this->setAttribute('title', $title);
        return $this;
    }

    /**
     * Get title attribute
     */
    public function getTitle(): ?string
    {
        return $this->getAttribute('title');
    }


    /**
     * Create new buffer
     */
    protected function newBuffer(?string $content): BufferInterface
    {
        return new Buffer($content);
    }
}
