<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Asset;

use DecodeLabs\Coercion;
use DecodeLabs\Tagged\Asset;
use DecodeLabs\Tagged\AssetTrait;
use DecodeLabs\Tagged\Element;

class Link implements Asset
{
    use AssetTrait;

    /**
     * Init with URL
     *
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        int $priority,
        string $href,
        array $attributes = []
    ) {
        $this->priority = $priority;
        $attributes['href'] = $href;

        if (!isset($attributes['rel'])) {
            $attributes['rel'] = 'stylesheet';
        }

        $this->setAttributes($attributes);
    }

    /**
     * Set URL
     */
    public function setHref(
        string $href
    ): void {
        $this->setAttribute('href', $href);
    }

    /**
     * Get URL
     */
    public function getHref(): string
    {
        return Coercion::asString(
            $this->getAttribute('href')
        );
    }

    /**
     * Set rel
     */
    public function setRel(
        string $rel
    ): void {
        $this->setAttribute('rel', $rel);
    }

    /**
     * Get rel
     */
    public function getRel(): string
    {
        return Coercion::asString(
            $this->getAttribute('rel') ?? 'stylesheet'
        );
    }

    /**
     * Set type
     */
    public function setType(
        string $type
    ): void {
        $this->setAttribute('type', $type);
    }

    /**
     * Get type
     */
    public function getType(): ?string
    {
        return Coercion::tryString(
            $this->getAttribute('type')
        );
    }

    /**
     * Render
     */
    public function render(): Element
    {
        return new Element('link', null, $this->attributes);
    }
}
