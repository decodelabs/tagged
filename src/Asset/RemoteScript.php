<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Asset;

use DecodeLabs\Coercion;
use DecodeLabs\Tagged\AssetTrait;
use DecodeLabs\Tagged\Element;

class RemoteScript implements Script
{
    use AssetTrait;

    /**
     * Init with URL
     *
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        int $priority,
        string $src,
        array $attributes = []
    ) {
        $this->priority = $priority;
        $attributes['src'] = $src;

        if (!isset($attributes['type'])) {
            $attributes['type'] = 'text/javascript';
        }

        $this->setAttributes($attributes);
    }

    /**
     * Set URL
     */
    public function setSrc(
        string $src
    ): void {
        $this->setAttribute('src', $src);
    }

    /**
     * Get URL
     */
    public function getSrc(): string
    {
        return Coercion::toString(
            $this->getAttribute('src')
        );
    }

    /**
     * Set async
     */
    public function setAsync(
        bool $async
    ): void {
        $this->setAttribute('async', $async);
    }

    /**
     * Is async
     */
    public function isAsync(): bool
    {
        return Coercion::toBool($this->getAttribute('async') ?? false);
    }

    /**
     * Set deferred
     */
    public function setDeferred(
        bool $deferred
    ): void {
        $this->setAttribute('defer', $deferred);
    }

    /**
     * Is deferred
     */
    public function isDeferred(): bool
    {
        return Coercion::toBool($this->getAttribute('defer') ?? false);
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
    public function getType(): string
    {
        return Coercion::toString(
            $this->getAttribute('type') ?? 'text/javascript'
        );
    }


    /**
     * Render
     */
    public function render(): Element
    {
        return new Element('script', null, $this->attributes);
    }
}
