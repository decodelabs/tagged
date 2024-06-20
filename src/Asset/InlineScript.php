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

class InlineScript implements Script
{
    use AssetTrait;

    protected string $source;

    /**
     * Init with script
     *
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        int $priority,
        string $source,
        array $attributes = []
    ) {
        $this->priority = $priority;
        $this->source = $source;

        if (!isset($attributes['type'])) {
            $attributes['type'] = 'text/javascript';
        }

        $this->setAttributes($attributes);
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
        unset($this->attributes['src']);
        return new Element('script', $this->source, $this->attributes);
    }
}
