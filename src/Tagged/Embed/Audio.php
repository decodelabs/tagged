<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Embed;

use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\Element;

class Audio implements Media
{
    use MediaTrait;

    /**
     * @var array<string,string>
     */
    protected const array UrlMap = [
        'audioboom' => 'audioboom',
        'audioboo' => 'audioboom'
    ];



    public static function defaultUrlFromId(
        string $id
    ): string {
        return '//embeds.audioboom.com/boos/' . $id . '/embed/v4';
    }




    public function render(): Element
    {
        if (
            (
                $this->url === null ||
                !$this->provider
            ) &&
            $this->source !== null
        ) {
            return Element::create('div.embed.audio', new Buffer($this->source));
        }

        return $this->prepareIframeElement($this->url);
    }


    protected function prepareIframeElement(
        ?string $url
    ): Element {
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
