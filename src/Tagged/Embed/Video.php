<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Embed;

use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\Element;

class Video implements Media
{
    use MediaTrait;

    protected const UrlMap = [
        'youtube' => 'youtube',
        'youtu.be' => 'youtube',
        'youtube-nocookie.com' => 'youtube',
        'vimeo' => 'vimeo'
    ];

    protected bool $useApi = false;


    public static function defaultUrlFromId(
        string $id
    ): string {
        return '//www.youtube.com/embed/' . $id;
    }

    public function setUseApi(
        bool $flag
    ): static {
        $this->useApi = $flag;
        return $this;
    }

    public function shouldUseApi(): bool
    {
        return $this->useApi;
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
            return Element::create('div.embed.video', new Buffer($this->source));
        }

        return $this->prepareIframeElement($this->url);
    }

    protected function prepareIframeElement(
        ?string $url
    ): Element {
        $tag = Element::create('iframe.embed.video', null, [
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
            $tag->setAttribute('data-video', $this->provider);
        }

        return $tag;
    }
}
