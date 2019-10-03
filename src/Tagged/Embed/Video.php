<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged\Embed;

use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\Builder\Html\ContentCollection;
use DecodeLabs\Tagged\Builder\Html\Tag;
use DecodeLabs\Tagged\Builder\Html\Element;

use DecodeLabs\Tagged\Embed\Media;
use DecodeLabs\Tagged\Markup;

class Video implements Media
{
    use MediaTrait;

    const URL_MAP = [
        'youtube' => 'youtube',
        'youtu.be' => 'youtube',
        'vimeo' => 'vimeo'
    ];

    protected $useApi = false;


    /**
     * Convert an anonymous id to a URL
     */
    public static function defaultUrlFromId(string $id): string
    {
        return '//www.youtube.com/embed/'.$id;
    }

    /**
     * Set to use API (youtube)
     */
    public function setUseApi(bool $flag): Media
    {
        $this->useApi = $flag;
        return $this;
    }

    /**
     * Should use API?
     */
    public function shouldUseApi(): bool
    {
        return $this->useApi;
    }



    /**
     * Render embed to markup
     */
    public function render(): Markup
    {
        if (($this->url === null || !$this->provider) && $this->source !== null) {
            return Element::create('div.embed.video', new Buffer($this->source));
        }

        return $this->prepareIframeElement($this->url);
    }

    /**
     * Prepare iframe element
     */
    protected function prepareIframeElement(string $url): Element
    {
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
