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

class Audio implements Media
{
    use MediaTrait;

    const URL_MAP = [
        'audioboom' => 'audioboom',
        'audioboo' => 'audioboom'
    ];


    /**
     * Parse embed string
     */
    public static function parse(string $embed): Media
    {
        $embed = trim($embed);
        $parts = explode('<', $embed, 2);

        if (count($parts) == 2) {
            $embed = '<'.array_pop($parts);

            if (!preg_match('/^\<([a-zA-Z0-9\-]+) /i', $embed, $matches)) {
                throw Glitch::EUnexpectedValue(
                    'Don\'t know how to parse this audio embed'
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
                            $height = round(($width / $output->width) * $output->height);
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
                        'Don\'t know how to parse this audio embed'
                    );
            }
        } elseif (preg_match('/^[0-9]+$/', $embed)) {
            // Assume audioboom
            $output = new self('//embeds.audioboom.com/boos/'.$embed.'/embed/v4');
        } else {
            // check is url
            $output = new self($embed);
        }

        return $output;
    }




    /**
     * Render embed to markup
     */
    public function render(): Markup
    {
        if (($this->url === null || !$this->provider) && $this->source !== null) {
            return Element::create('div.embed.audio', new Buffer($this->source));
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

        $tag->addClass('embed audio');

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
    protected function renderAudioboom(): Markup
    {
        $urlParts = parse_url($this->url);
        parse_str($urlParts['query'] ?? '', $urlParts['query']);

        $parts = explode('/', ltrim($urlParts['path'] ?? '', '/'));
        $booId = $parts[1] ?? null;

        if ($booId === 'playlist' || $booId === 'playlist') {
            $url = $this->url;
        } else {
            $url = '//embeds.audioboom.com/boos/'.$booId.'/embed/v4';

            if (isset($urlParts['query']['eid'])) {
                $url .= '?eid='.$urlParts['query']['eid'];
            }
        }

        return Element::create('iframe', null, [
            'id' => $this->id,
            'src' => $url,
            'width' => $this->width,
            'height' => $this->height,
            'frameborder' => 0,
            'data-audio' => $this->provider,
            'allowtransparency' => 'allowtransparency',
            'scrolling' => 'no'
        ]);
    }
}
