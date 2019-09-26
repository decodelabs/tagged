<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged\FactoryPlugins;

use DecodeLabs\Veneer\FacadePlugin;

use DecodeLabs\Tagged\Markup;
use DecodeLabs\Tagged\HtmlFactory;
use DecodeLabs\Tagged\Buffer;

use DecodeLabs\Tagged\Embed\Media;
use DecodeLabs\Tagged\Embed\Video;
use DecodeLabs\Tagged\Embed\Audio;

class Embed implements FacadePlugin
{
    protected $html;

    /**
     * Init with parent factory
     */
    public function __construct(HtmlFactory $html)
    {
        $this->html = $html;
    }


    /**
     * Embed shared video code
     */
    public function video(string $embed, int $width=null, int $height=null): ?Markup
    {
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
    public function audio(string $embed, int $width=null, int $height=null): ?Markup
    {
        $embed = trim($embed);

        if (empty($embed)) {
            return null;
        }

        return Audio::parse($embed)
            ->setDimensions($width, $height);
    }
}
