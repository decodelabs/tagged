<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Html\Embed;

use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\Html\Element;
use DecodeLabs\Tagged\Markup;

class Audio implements Media
{
    use MediaTrait;

    public const URL_MAP = [
        'audioboom' => 'audioboom',
        'audioboo' => 'audioboom'
    ];



    /**
     * Convert an anonymous id to a URL
     */
    public static function defaultUrlFromId(string $id): string
    {
        return '//embeds.audioboom.com/boos/' . $id . '/embed/v4';
    }



    /**
     * Render embed to markup
     */
    public function render(): Markup
    {
        if (($this->url === null || !$this->provider) && $this->source !== null) {
            return Element::create('div.embed.audio', new Buffer($this->source));
        }

        return $this->prepareIframeElement($this->url);
    }

    /**
     * Prepare iframe element
     */
    protected function prepareIframeElement(string $url): Element
    {
        $tag = Element::create('iframe.embed.audio', null, [
            'id' => $this->id,
            'src' => $url,
            'width' => $this->width,
            'height' => $this->height,
            'frameborder' => 0,
            'allowtransparency' => 'allowtransparency',
            'scrolling' => 'no'
        ]);

        if ($this->allowFullScreen) {
            $tag->setAttributes([
                'allowfullscreen' => true,
                'webkitAllowFullScreen' => true,
                'mozallowfullscreen' => true
            ]);
        }

        if ($this->provider) {
            $tag->setAttribute('data-audio', $this->provider);
        }

        return $tag;
    }
}
