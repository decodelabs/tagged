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
     * Parse embed string
     */
    public static function parse(string $embed): Media
    {
        $embed = trim($embed);
        $stripEmbed = strip_tags($embed, '<iframe><object><embed><script>');
        $parts = explode('<', $stripEmbed, 2);

        if (count($parts) == 2) {
            $embed = '<'.array_pop($parts);

            if (!preg_match('/^\<([a-zA-Z0-9\-]+) /i', $embed, $matches)) {
                throw Glitch::EUnexpectedValue(
                    'Don\'t know how to parse this video embed'
                );
            }

            $tag = strtolower($matches[1]);

            switch ($tag) {
                case 'iframe':
                case 'object':
                    if (!preg_match('/src\=(\"|\')([^\'"]+)(\"|\')/i', $embed, $matches)) {
                        throw Glitch::EUnexpectedValue(
                            'Could not extract source from flash embed'
                        );
                    }

                    $url = trim($matches[2]);
                    $output = new self($url, null, null, $embed);

                    if (preg_match('/width\=\"([^\"]+)\"/i', $embed, $matches)) {
                        $width = $matches[1];

                        if (preg_match('/height\=\"([^\"]+)\"/i', $embed, $matches)) {
                            $height = $matches[1];
                        } else {
                            $height = round(($width / $output->_width) * $output->_height);
                        }

                        if (false !== strpos($width, '%')) {
                            $width = 720 / 100 * (int)$width;
                        }

                        if (false !== strpos($height, '%')) {
                            $height = 450 / 100 * (int)$height;
                        }

                        $output->setWidth((int)$width);
                        $output->setHeight((int)$height);
                    }

                    break;

                case 'script':
                    $output = new self(null, null, null, $embed);
                    break;

                default:
                    throw Glitch::EUnexpectedValue(
                        'Don\'t know how to parse this video embed'
                    );
            }
        } else {
            // TODO: check is url
            $output = new self($embed);
        }

        return $output;
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

        if ($this->provider) {
            $func = 'render'.ucfirst($this->provider);
            $tag = $this->$func();
        } else {
            $tag = Element::create('iframe', null, [
                'src' => $this->url,
                'width' => $this->width,
                'height' => $this->height,
                'frameborder' => 0
            ]);
        }

        $tag->addClass('embed video');

        if ($tag->getName() == 'iframe' && $this->allowFullScreen) {
            $tag->setAttribute('allowfullscreen', true);
            $tag->setAttribute('webkitAllowFullScreen', true);
            $tag->setAttribute('mozallowfullscreen', true);
        }

        return $tag;
    }




    /**
     * Render youtube specific embed
     */
    protected function renderYoutube(): Markup
    {
        $urlParts = parse_url($this->url);
        parse_str($urlParts['query'] ?? '', $urlParts['query']);

        if (isset($urlParts['query']['v'])) {
            $id = $urlParts['query']['v'];
        } else {
            $parts = explode('/', $urlParts['path'] ?? '');
            $id = array_pop($parts);

            if ($id == 'watch') {
                throw Glitch::EUnexpectedValue('Malformed youtube URL', null, $this->url);
            }
        }

        static $vars = [
            'autohide', 'autoplay', 'cc_load_policy', 'color', 'controls',
            'disablekb', 'enablejsapi', 'end', 'fs', 'hl', 'iv_load_policy',
            'list', 'listType', 'loop', 'modestbranding', 'origin', 'playerapiid',
            'playlist', 'playsinline', 'rel', 'showinfo', 'start', 'theme'
        ];

        $url = '//www.youtube.com/embed/'.$id;
        $queryVars = [];

        foreach ((array)$urlParts['query'] as $key => $value) {
            if (in_array(strtolower($key), $vars)) {
                $queryVars[$key] = $value;
            }
        }

        if ($this->startTime !== null) {
            $queryVars['start'] = $this->startTime;
        }

        if ($this->endTime !== null) {
            $queryVars['end'] = $this->endTime;
        }

        if ($this->duration !== null) {
            $queryVars['end'] = $this->duration + $this->startTime;
        }

        if ($this->autoPlay) {
            $queryVars['autoplay'] = 1;
        }

        if ($this->useApi) {
            $queryVars['enablejsapi'] = 1;
        }

        if ($this->origin !== null) {
            $queryVars['origin'] = $this->origin;
        }

        if (!empty($queryVars)) {
            $url .= '?'.http_build_query($queryVars);
        }

        return Element::create('iframe', null, [
            'id' => $this->id,
            'src' => $url,
            'width' => $this->width,
            'height' => $this->height,
            'frameborder' => 0,
            'data-video' => $this->provider
        ]);
    }

    /**
     * Render vimeo specific video
     */
    protected function renderVimeo(): Markup
    {
        $url = $this->url;
        $urlParts = parse_url($this->url);
        parse_str($urlParts['query'] ?? '', $urlParts['query']);
        $parts = explode('/', $urlParts['path'] ?? '');
        $id = array_pop($parts);

        if (is_numeric($id)) {
            $url = '//player.vimeo.com/video/'.$id;
            $queryVars = [];

            if ($this->autoPlay) {
                $queryVals['autoplay'] = 1;
            }

            /*
            if($this->startTime !== null) {
                $queryVals['start'] = $this->startTime.'s';
            }

            if($this->endTime !== null) {
                $queryVals['end'] = $this->endTime.'s';
            }

            if($this->duration !== null) {
                $queryVals['end'] = $this->duration + $this->startTime;
            }
            */

            if (!empty($queryVars)) {
                $url .= '?'.http_build_query($queryVars);
            }
        }

        $tag = Element::create('iframe', null, [
            'id' => $this->id,
            'src' => $url,
            'width' => $this->width,
            'height' => $this->height,
            'frameborder' => 0,
            'data-video' => $this->provider
        ]);

        return $tag;
    }
}
