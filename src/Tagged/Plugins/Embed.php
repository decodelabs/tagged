<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Plugins;

use DecodeLabs\Tagged\Embed\Audio;
use DecodeLabs\Tagged\Embed\Video;
use DecodeLabs\Tagged\Factory;

use DecodeLabs\Tagged\Markup;
use DecodeLabs\Veneer\Plugin;

class Embed implements Plugin
{
    protected Factory $html;

    /**
     * Init with parent factory
     */
    public function __construct(Factory $html)
    {
        $this->html = $html;
    }


    /**
     * Embed shared video code
     */
    public function video(
        ?string $embed,
        int $width = null,
        int $height = null
    ): ?Markup {
        if ($embed === null) {
            return null;
        }

        $embed = trim($embed);

        if (empty($embed)) {
            return null;
        }

        return Video::parse($embed)
            ->setDimensions($width, $height);
    }

    /**
     * Embed shared audio code
     */
    public function audio(
        ?string $embed,
        int $width = null,
        int $height = null
    ): ?Markup {
        if ($embed === null) {
            return null;
        }

        $embed = trim($embed);

        if (empty($embed)) {
            return null;
        }

        return Audio::parse($embed)
            ->setDimensions($width, $height);
    }
}
