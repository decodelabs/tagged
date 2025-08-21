<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Component;

use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\Component;
use DecodeLabs\Tagged\Embed\Video;
use DecodeLabs\Tagged\RenderableTrait;
use DecodeLabs\Tagged\Tag;

class VideoEmbed extends Tag implements Component
{
    use RenderableTrait;

    public protected(set) ?Video $embed;

    /**
     * @param array<string,mixed>|null $attributes
     */
    public function __construct(
        ?string $embed,
        ?int $width = null,
        ?int $height = null,
        ?array $attributes = null
    ) {
        parent::__construct('div', $attributes);

        $this->embed = Video::parse($embed)
            ?->setDimensions($width, $height);
    }

    public function render(
        bool $pretty = false
    ): ?Buffer {
        $this->renderEmpty = false;

        if ($this->embed === null) {
            return null;
        }

        $el = $this->embed->render();

        foreach ($this->attributes as $key => $value) {
            if ($key === 'class') {
                $el->addClasses($value);
                continue;
            } elseif ($key === 'style') {
                $el->addStyles($value);
                continue;
            }

            $el->setAttribute($key, $value);
        }

        return $el->render($pretty);
    }
}
