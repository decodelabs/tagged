<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged\Html;

use DecodeLabs\Tagged\Markup;
use DecodeLabs\Tagged\Builder\Tag as TagInterface;
use DecodeLabs\Tagged\Builder\TagTrait;
use DecodeLabs\Tagged\Builder\ChildRendererTrait;
use DecodeLabs\Tagged\Builder\ClassListContainer;
use DecodeLabs\Tagged\Builder\ClassListContainerTrait;
use DecodeLabs\Tagged\Builder\StyleListContainer;
use DecodeLabs\Tagged\Builder\StyleListContainerTrait;

use DecodeLabs\Collections\AttributeContainer;

use DecodeLabs\Glitch\Inspectable;
use DecodeLabs\Glitch\Dumper\Entity;
use DecodeLabs\Glitch\Dumper\Inspector;

class Tag implements TagInterface, ClassListContainer, StyleListContainer, Inspectable
{
    use TagTrait;
    use ClassListContainerTrait;
    use StyleListContainerTrait;

    const CLOSED_TAGS = [
        'area', 'base', 'br', 'col', 'command', 'embed',
        'hr', 'img', 'input', 'keygen', 'link', 'meta',
        'param', 'source', 'wbr'
    ];

    const INLINE_TAGS = [
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

    const BOOLEAN_ATTRIBUTES = [
        'spellcheck'
    ];

    /**
     * Can tag be closed with full </tag>
     */
    public static function isClosableTagName(?string $name): bool
    {
        return !in_array(strtolower($name), self::CLOSED_TAGS);
    }

    /**
     * Should tag be single inline entity
     */
    public static function isInlineTagName(?string $name): bool
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
        return $this->setAttribute('data-'.$key, $value);
    }

    /**
     * Retrieve data attribute value if set
     */
    public function getDataAttribute(string $key)
    {
        return $this->getAttribute('data-'.$key);
    }

    /**
     * Remove single data attribute
     */
    public function removeDataAttribute(string ...$keys): AttributeContainer
    {
        $keys = array_map(function ($key) {
            return 'data-'.$key;
        }, $keys);

        return $this->removeAttribute(...$keys);
    }

    /**
     *  Have any of these data attributes been set?
     */
    public function hasDataAttribute(string ...$keys): bool
    {
        $keys = array_map(function ($key) {
            return 'data-'.$key;
        }, $keys);

        return $this->hasAttribute(...$keys);
    }

    /**
     *  Have all of these data attributes been set?
     */
    public function hasDataAttributes(string ...$keys): bool
    {
        $keys = array_map(function ($key) {
            return 'data-'.$key;
        }, $keys);

        return $this->hasAttributes(...$keys);
    }

    /**
     * Remove all data attributes
     */
    public function clearDataAttributes(): AttributeContainer
    {
        foreach ($this->attributes as $key => $value) {
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

        foreach ($this->attributes as $key => $value) {
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
     * Shortcut to set attribute
     */
    public function offsetSet($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Shortcut to get attribute
     */
    public function offsetGet($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Shortcut to test for attribute
     */
    public function offsetExists($key)
    {
        return $this->hasAttribute($key);
    }

    /**
     * Shortcut to remove attribute
     */
    public function offsetUnset($key)
    {
        $this->removeAttribute($key);
    }
}
