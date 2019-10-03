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

use DecodeLabs\Collections\Tree\NativeMutable as Tree;

class Audioboom extends Audio
{
    protected $audioboomId;
    protected $type = 'embed';
    protected $options = [];

    /**
     * Extract parts from URL
     */
    protected function setUrl(string $url): Media
    {
        parent::setUrl($url);

        if (!$this->url) {
            return $this;
        }

        $urlParts = parse_url($this->url);
        parse_str($urlParts['query'] ?? '', $urlParts['query']);

        $parts = explode('/', ltrim($urlParts['path'] ?? '', '/'));
        $booId = $parts[1] ?? null;

        if ($booId === 'playlist' || $booId === 'playlist') {
            $this->type = 'playlist';
            $this->audioboomId = $urlParts['query']['data_for_content_type'];
        } else {
            $this->type = 'embed';
            $this->audioboomId = $booId;

            static $vars = [
                'eid', 'player_type'
            ];

            foreach ((array)$urlParts['query'] as $key => $value) {
                if (in_array(strtolower($key), $vars)) {
                    $this->options[$key] = $value;
                }
            }
        }

        return $this;
    }

    /**
     * Get Audioboom id
     */
    public function getAudioboomId(): string
    {
        return $this->audioboomId;
    }

    /**
     * Get audiobom type
     */
    public function getAudioboomType(): string
    {
        return $this->type;
    }

    /**
     * Render Audioboom specific embed
     */
    public function render(): Markup
    {
        if ($this->type === 'playlist') {
            $url = 'https://embeds.audioboom.com/publishing/playlist/v4?boo_content_type=playlist&data_for_content_type='.$this->audioboomId;
        } elseif ($this->type === 'embed') {
            $url = '//embeds.audioboom.com/boos/'.$this->audioboomId.'/embed/v4';

            if (!empty($this->options)) {
                $url .= '?'.http_build_query($this->options);
            }
        } else {
            throw Glitch::EUnexpectedValue('Unexpected Audioboom type', null, $this->type);
        }

        return $this->prepareIframeElement($url);
    }


    /**
     * Lookup thumbnail URL
     */
    public function lookupThumbnail(): ?string
    {
        switch ($this->type) {
            case 'embed':
                $url = 'https://audioboom.com/publishing/oembed.json?url=https://audioboom.com/posts/'.$this->audioboomId;
                break;

            case 'playlist':
                return null;
        }

        try {
            $json = file_get_contents($url);
            $json = json_decode($json, true);
        } catch (\ErrorException $e) {
            return null;
        }

        return $json['thumbnail_url'] ?? null;
    }

    /**
     * Lookup media meta information
     */
    public function lookupMeta(): ?array
    {
        switch ($this->type) {
            case 'embed':
                $url = 'https://audioboom.com/publishing/oembed.json?url=https://audioboom.com/posts/'.$this->audioboomId;
                break;

            case 'playlist':
                return null;
        }

        try {
            $json = file_get_contents($url);
            $json = json_decode($json, true);
            $json = new Tree($json);
        } catch (\ErrorException $e) {
            return null;
        }

        return [
            'title' => $json['title'],
            'url' => $this->url,
            'embed' => $json['html'],
            'width' => $json['width'],
            'height' => $json['height'],
            'duration' => $json['duration'],
            'uploadDate' => isset($json['upload_date']) ? (new \DateTime())->setTimestamp((int)$json['upload_date']) : null,
            'description' => $json['description'],
            'authorName' => $json['author_name'],
            'authorUrl' => $json['author_url'],
            'thumbnailUrl' => $json['thumbnail_url'],
            'thumbnailWidth' => $json['thumbnail_width'],
            'thumbnailHeight' => $json['thumbnail_height']
        ];
    }
}
