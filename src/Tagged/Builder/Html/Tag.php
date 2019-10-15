<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged\Builder\Html;

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
    public static function isClosableTagName($name): bool
    {
        return !in_array(strtolower($name), self::CLOSED_TAGS);
    }

    /**
     * Should tag be single inline entity
     */
    public static function isInlineTagName($name): bool
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
