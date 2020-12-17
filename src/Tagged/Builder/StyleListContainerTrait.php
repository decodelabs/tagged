<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Builder;

trait StyleListContainerTrait
{
    /**
     * Replace style list
     */
    public function setStyles(...$styles): StyleListContainer
    {
        $styles = $this->getStyles();
        $styles->clear();
        $styles->import(...$styles);
        return $this;
    }

    /**
     * Merge style list
     */
    public function addStyles(...$styles): StyleListContainer
    {
        $this->getStyles()->import(...$styles);
        return $this;
    }

    /**
     * Get style object
     */
    public function getStyles(): StyleList
    {
        if (!isset($this->attributes['style'])) {
            $this->attributes['style'] = new StyleList();
        }

        return $this->attributes['style'];
    }

    /**
     * Set a single style value
     */
    public function setStyle(string $key, ?string $value): StyleListContainer
    {
        $styles = $this->getStyles();

        if ($value === null) {
            $styles->remove($key);
        } else {
            $styles->set($key, $value);
        }

        return $this;
    }

    /**
     * Get a single style value
     */
    public function getStyle(string $key): ?string
    {
        return $this->getStyles()->get($key);
    }

    /**
     * Remove set of styles
     */
    public function removeStyle(string ...$keys): StyleListContainer
    {
        $this->getStyles()->remove(...$keys);
        return $this;
    }

    /**
     * List has any of these styles?
     */
    public function hasStyle(string ...$keys): bool
    {
        return $this->getStyles()->has(...$keys);
    }

    /**
     * List has ALL of these styles?
     */
    public function hasStyles(string ...$keys): bool
    {
        return $this->getStyles()->hasAll(...$keys);
    }

    /**
     * Reset all styles
     */
    public function clearStyles(): StyleListContainer
    {
        $this->getStyles()->clear();
        return $this;
    }
}
