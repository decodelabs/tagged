<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Embed;

use DateTime;

use DecodeLabs\Coercion;
use DecodeLabs\Collections\Tree\NativeMutable as Tree;
use DecodeLabs\Exceptional;

use DecodeLabs\Tagged\Element;

use ErrorException;

class Audioboom extends Audio
{
    protected string $audioboomId;
    protected string $type = 'embed';

    /**
     * @var array<string, string>
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

        $parts = explode('/', ltrim($urlParts['path'] ?? '', '/'));
        $booId = $parts[1] ?? null;

        if ($booId === 'playlist' || $booId === 'playlist') {
            $this->type = 'playlist';
            $this->audioboomId = Coercion::toString($query['data_for_content_type']);
        } else {
            $this->type = 'embed';
            $this->audioboomId = (string)$booId;

            static $vars = [
                'eid', 'player_type'
            ];

            foreach ((array)$query as $key => $value) {
                if (in_array(strtolower((string)$key), $vars)) {
                    $this->options[(string)$key] = Coercion::forceString($value);
                }
            }
        }

        return $this;
    }

    /**
     * Get finalized URL from renderer tag
     */
    public function getPreparedUrl(): ?string
    {
        if ($this->type === 'playlist') {
            $url = 'https://embeds.audioboom.com/publishing/playlist/v4?boo_content_type=playlist&data_for_content_type=' . $this->audioboomId;
        } elseif ($this->type === 'embed') {
            $url = '//embeds.audioboom.com/boos/' . $this->audioboomId . '/embed/v4';

            if (!empty($this->options)) {
                $url .= '?' . http_build_query($this->options);
            }
        } else {
            throw Exceptional::UnexpectedValue('Unexpected Audioboom type', null, $this->type);
        }

        return $url;
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
     * Render URL embed
     */
    public function render(): Element
    {
        return $this->prepareIframeElement((string)$this->getPreparedUrl());
    }


    /**
     * Lookup thumbnail URL
     *
     * @param array<string, mixed> $options
     */
    public function lookupThumbnail(?array $options = null): ?string
    {
        switch ($this->type) {
            case 'embed':
                $url = 'https://audioboom.com/publishing/oembed.json?url=https://audioboom.com/posts/' . $this->audioboomId;
                break;

            case 'playlist':
            default:
                return null;
        }

        try {
            if (false === ($json = file_get_contents($url))) {
                return null;
            }

            $json = json_decode($json, true);
        } catch (ErrorException $e) {
            return null;
        }

        /** @var array<string, mixed> $json */
        return Coercion::toStringOrNull($json['thumbnail_url'] ?? null);
    }

    /**
     * Lookup media meta information
     *
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function lookupMeta(?array $options = null): ?array
    {
        switch ($this->type) {
            case 'embed':
                return $this->lookupEmbedMeta($options);

            case 'playlist':
                return $this->lookupPlaylistMeta($options);

            default:
                throw Exceptional::UnexpectedValue('Unsupported Audioboom type: ' . $this->type);
        }
    }

    /**
     * Lookup meta for embed
     *
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    protected function lookupEmbedMeta(?array $options = null): ?array
    {
        $url = 'https://audioboom.com/publishing/oembed.json?url=https://audioboom.com/posts/' . $this->audioboomId;

        try {
            if (false !== ($json = file_get_contents($url))) {
                /* @phpstan-ignore-next-line */
                $json = new Tree(json_decode($json, true));
            } else {
                $json = new Tree();
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
            'duration' => $json['duration'],
            'uploadDate' => isset($json['upload_date']) ? (new DateTime())->setTimestamp((int)$json['upload_date']) : null,
            'description' => $json['description'],
            'authorName' => $json['author_name'],
            'authorUrl' => $json['author_url'],
            'thumbnailUrl' => $json['thumbnail_url'],
            'thumbnailWidth' => $json['thumbnail_width'],
            'thumbnailHeight' => $json['thumbnail_height']
        ];
    }

    /**
     * Lookup meta for embed
     *
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    protected function lookupPlaylistMeta(?array $options = null): ?array
    {
        $url = 'https://api.audioboom.com/playlists/' . $this->audioboomId;

        try {
            if (false !== ($json = file_get_contents($url))) {
                /* @phpstan-ignore-next-line */
                $json = new Tree(json_decode($json, true));
            } else {
                $json = new Tree();
            }
        } catch (ErrorException $e) {
            return null;
        }

        $playlist = $json->body->playlist;
        $duration = 0;
        $uploadTs = $uploadDate = $user = $profileUrl = null;

        foreach ($playlist->memberships as $item) {
            $duration += $item->audio_clip['duration'];
            $currentDate = new DateTime(Coercion::toString($item->audio_clip['uploaded_at']));
            $ts = $currentDate->getTimestamp();

            if ($ts > $uploadTs) {
                $uploadDate = $currentDate;
            }

            $user = $item->audio_clip->user['username'];
            $profileUrl = $item->audio_clip->user->urls['profile'];
        }

        return [
            'title' => $playlist['title'],
            'url' => $this->url,
            'embed' => $this->source ?? (string)$this->render(),
            'width' => null,
            'height' => null,
            'duration' => $duration,
            'uploadDate' => $uploadDate,
            'description' => empty($playlist['description']) ? null : $playlist['description'],
            'authorName' => $user,
            'authorUrl' => $profileUrl,
            'thumbnailUrl' => $playlist->mosaic_image['original'],
            'thumbnailWidth' => null,
            'thumbnailHeight' => null
        ];
    }
}
