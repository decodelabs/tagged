<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Component;

use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\Component;
use DecodeLabs\Tagged\RenderableTrait;
use DecodeLabs\Tagged\Tag;
use Stringable;

class Img extends Tag implements Component
{
    use RenderableTrait;

    /**
     * @param array<string,mixed>|null $attributes
     */
    public function __construct(
        string|Stringable|null $src,
        ?string $alt = null,
        string|int|null $width = null,
        string|int|null $height = null,
        ?array $attributes = null
    ) {
        parent::__construct('img', $attributes);

        $this->setAttributes([
            'src' => $src,
            'alt' => $alt
        ]);

        if ($width !== null) {
            $this->setAttribute('width', $width);
        }

        if ($height !== null) {
            $this->setAttribute('height', $height);
        }
    }

    public function render(
        bool $pretty = false
    ): ?Buffer {
        return $this->renderWith(null, $pretty);
    }
}
