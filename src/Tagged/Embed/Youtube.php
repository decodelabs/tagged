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

class Youtube extends Video
{
    protected $youtubeId;
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

        if (isset($urlParts['query']['v'])) {
            $id = $urlParts['query']['v'];
        } else {
            $parts = explode('/', $urlParts['path'] ?? '');
            $id = array_pop($parts);

            if ($id == 'watch') {
                throw Glitch::EUnexpectedValue('Malformed youtube URL', null, $this->url);
            }
        }

        $this->youtubeId = $id;


        static $vars = [
            'autohide', 'autoplay', 'cc_load_policy', 'color', 'controls',
            'disablekb', 'enablejsapi', 'end', 'fs', 'hl', 'iv_load_policy',
            'list', 'listType', 'loop', 'modestbranding', 'origin', 'playerapiid',
            'playlist', 'playsinline', 'rel', 'showinfo', 'start', 'theme'
        ];

        foreach ((array)$urlParts['query'] as $key => $value) {
            if (in_array(strtolower($key), $vars)) {
                $this->options[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Get Youtube id
     */
    public function getYoutubeId(): string
    {
        return $this->youtubeId;
    }

    /**
     * Render youtube specific embed
     */
    public function render(): Markup
    {
        $url = '//www.youtube.com/embed/'.$this->youtubeId;
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
            $url .= '?'.http_build_query($queryVars);
        }

        return $this->prepareIframeElement($url);
    }


    /**
     * Lookup thumbnail URL
     */
    public function lookupThumbnail(): ?string
    {
        return 'http://img.youtube.com/vi/'.$this->youtubeId.'/hqdefault.jpg';
    }

    /**
     * Lookup media meta information
     */
    public function lookupMeta(): ?array
    {
        $url = 'http://www.youtube.com/oembed?url='.urlencode('http://www.youtube.com/watch?v='.$this->youtubeId).'&format=json';
        $infoUrl = 'https://www.youtube.com/get_video_info?video_id='.$this->youtubeId;

        try {
            $json = file_get_contents($url);
            $json = json_decode($json, true);
            $json = new Tree($json);

            $info = file_get_contents($infoUrl);
            $info = Tree::fromDelimitedString($info);
        } catch (\ErrorException $e) {
            return null;
        }

        return [
            'title' => $json['title'],
            'url' => $this->url,
            'embed' => $json['html'],
            'width' => $json['width'],
            'height' => $json['height'],
            'duration' => $info['length_seconds'],
            'uploadDate' => isset($info['timestamp']) ? (new \DateTime())->setTimestamp((int)$info['timestamp']) : null,
            'description' => $json['description'],
            'authorName' => $json['author_name'],
            'authorUrl' => $json['author_url'],
            'thumbnailUrl' => $json['thumbnail_url'],
            'thumbnailWidth' => $json['thumbnail_width'],
            'thumbnailHeight' => $json['thumbnail_height']
        ];
    }
}
