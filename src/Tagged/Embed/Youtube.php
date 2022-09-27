<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Embed;

use DateTime;

use DecodeLabs\Collections\Tree\NativeMutable as Tree;
use DecodeLabs\Exceptional;
use DecodeLabs\Tagged\Element;

use ErrorException;

class Youtube extends Video
{
    protected string $youtubeId;

    /**
     * @var array<string, mixed>
     */
    protected array $options = [];

    /**
     * Extract parts from URL
     */
    protected function setUrl(?string $url): static
    {
        parent::setUrl($url);

        if (!$this->url) {
            return $this;
        }

        $urlParts = parse_url($this->url);

        if ($urlParts === false || empty($urlParts)) {
            throw Exceptional::UnexpectedValue('Unable to parse URL', null, $this->url);
        }

        parse_str($urlParts['query'] ?? '', $query);

        if (isset($query['v'])) {
            $id = $query['v'];
        } else {
            $parts = explode('/', $urlParts['path'] ?? '');
            $id = array_pop($parts);

            if ($id == 'watch') {
                throw Exceptional::UnexpectedValue('Malformed youtube URL', null, $this->url);
            }
        }

        $this->youtubeId = $id;


        static $vars = [
            'autohide', 'autoplay', 'cc_load_policy', 'color', 'controls',
            'disablekb', 'enablejsapi', 'end', 'fs', 'hl', 'iv_load_policy',
            'list', 'listType', 'loop', 'modestbranding', 'origin', 'playerapiid',
            'playlist', 'playsinline', 'rel', 'showinfo', 'start', 'theme'
        ];

        foreach ((array)$query as $key => $value) {
            if (in_array(strtolower($key), $vars)) {
                $this->options[(string)$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Get finalized URL from renderer tag
     */
    public function getPreparedUrl(): ?string
    {
        $url = 'https://www.youtube.com/embed/' . $this->youtubeId;
        $queryVars = $this->options;

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
            $url .= '?' . http_build_query($queryVars);
        }

        return $url;
    }

    /**
     * Get Youtube id
     */
    public function getYoutubeId(): string
    {
        return $this->youtubeId;
    }

    /**
     * Render URL embed
     */
    public function render(): Element
    {
        return $this->prepareIframeElement((string)$this->getPreparedUrl());
    }


    /**
     * Lookup thumbnail URL
     */
    public function lookupThumbnail(?array $options = null): ?string
    {
        return 'https://img.youtube.com/vi/' . $this->youtubeId . '/hqdefault.jpg';
    }

    /**
     * Lookup media meta information
     */
    public function lookupMeta(?array $options = null): ?array
    {
        $url = 'https://www.youtube.com/oembed?url=' . urlencode('https://www.youtube.com/watch?v=' . $this->youtubeId) . '&format=json';
        $infoUrl = 'https://www.youtube.com/get_video_info?video_id=' . $this->youtubeId;

        try {
            if (false !== ($json = file_get_contents($url))) {
                /* @phpstan-ignore-next-line */
                $json = new Tree(json_decode($json, true));
            } else {
                $json = new Tree();
            }

            if (false !== ($info = file_get_contents($infoUrl))) {
                $info = Tree::fromDelimitedString($info);
            } else {
                $info = new Tree();
            }
        } catch (ErrorException $e) {
            return null;
        }

        return [
            'title' => $json['title'],
            'url' => $this->url,
            'embed' => $json['html'],
            'width' => $json['width'],
            'height' => $json['height'],
            'duration' => $info['length_seconds'],
            'uploadDate' => isset($info['timestamp']) ? (new DateTime())->setTimestamp((int)$info['timestamp']) : null,
            'description' => $json['description'],
            'authorName' => $json['author_name'],
            'authorUrl' => $json['author_url'],
            'thumbnailUrl' => $json['thumbnail_url'],
            'thumbnailWidth' => $json['thumbnail_width'],
            'thumbnailHeight' => $json['thumbnail_height']
        ];
    }
}
