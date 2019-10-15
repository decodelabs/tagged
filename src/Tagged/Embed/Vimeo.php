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

class Vimeo extends Video
{
    protected $vimeoId;
    protected $options = [];

    /**
     * Extract parts from URL
     */
    protected function setUrl(?string $url): Media
    {
        parent::setUrl($url);

        if (!$this->url) {
            return $this;
        }

        $urlParts = parse_url($this->url);

        if ($urlParts === false || empty($urlParts)) {
            throw Glitch::EUnexpectedValue('Unable to parse URL', null, $this->url);
        }

        parse_str($urlParts['query'] ?? '', $query);
        $parts = explode('/', $urlParts['path'] ?? '');
        $id = array_pop($parts);

        if (!is_numeric($id)) {
            throw Glitch::EUnexpectedValue('Malformed vimeo URL', null, $this->url);
        }

        $this->vimeoId = $id;
        $this->options = (array)$query;

        return $this;
    }

    /**
     * Get finalized URL from renderer tag
     */
    public function getPreparedUrl(): ?string
    {
        $url = 'https://player.vimeo.com/video/'.$this->vimeoId;
        $queryVars = $this->options;

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

        return $url;
    }

    /**
     * Get Vimeo id
     */
    public function getVimeoId(): string
    {
        return $this->vimeoId;
    }

    /**
     * Render URL embed
     */
    public function render(): Markup
    {
        return $this->prepareIframeElement((string)$this->getPreparedUrl());
    }


    /**
     * Lookup thumbnail URL
     */
    public function lookupThumbnail(): ?string
    {
        $url = 'https://vimeo.com/api/oembed.json?url='.$this->url;

        try {
            if (false === ($json = file_get_contents($url))) {
                return null;
            }

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
        $url = 'https://vimeo.com/api/oembed.json?url='.urlencode($this->url);

        try {
            if (false !== ($json = file_get_contents($url))) {
                $json = new Tree(json_decode($json, true));
            } else {
                $json = new Tree();
            }
        } catch (\ErrorException $e) {
            return null;
        }

        return [
            'title' => $json['title'],
            'url' => $this->url,
            'embed' => $json['html'],
            'width' => $json['width'],
            'height' => $json['height'],
            'duration' => $json['duration'] ?? $json['length_seconds'],
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
