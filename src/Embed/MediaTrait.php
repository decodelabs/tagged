<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Embed;

use DecodeLabs\Coercion;
use DecodeLabs\Exceptional;

/**
 * @phpstan-require-implements Media
 */
trait MediaTrait
{
    //protected const UrlMap = [];

    protected ?string $url = null;
    protected ?string $provider = null;
    protected ?string $id = null;
    protected ?string $origin = null;

    protected ?int $width = 640;
    protected ?int $height = 360;

    protected bool $allowFullScreen = true;
    protected bool $autoPlay = false;

    protected ?int $startTime = null;
    protected ?int $endTime = null;
    protected ?int $duration = null;

    protected ?string $source = null;
    protected bool $consent = false;


    /**
     * Parse embed string
     */
    public static function parse(
        string $embed
    ): static {
        $embed = trim($embed);
        $stripEmbed = strip_tags($embed, '<iframe><object><embed><script>');
        $parts = explode('<', $stripEmbed, 2);

        if (count($parts) == 2) {
            $embed = '<' . array_pop($parts);

            if (!preg_match('/^\<([a-zA-Z0-9\-]+) /i', $embed, $matches)) {
                throw Exceptional::UnexpectedValue(
                    'Don\'t know how to parse this embed'
                );
            }

            $tag = strtolower($matches[1]);

            switch ($tag) {
                case 'iframe':
                case 'object':
                    if (!preg_match('/src\=(\"|\')([^\'"]+)(\"|\')/i', $embed, $matches)) {
                        throw Exceptional::UnexpectedValue(
                            'Could not extract source from flash embed'
                        );
                    }

                    $url = trim($matches[2]);

                    /** @var class-string<static> */
                    $class = self::getClassForUrl($url);
                    $output = new $class($url, null, null, $embed);

                    if (preg_match('/width\=\"([^\"]+)\"/i', $embed, $matches)) {
                        $width = $matches[1];

                        if (false !== strpos($width, '%')) {
                            $width = 720 / 100 * (int)$width;
                        }

                        $width = Coercion::toInt($width);

                        if (preg_match('/height\=\"([^\"]+)\"/i', $embed, $matches)) {
                            $height = $matches[1];

                            if (false !== strpos($height, '%')) {
                                $height = 450 / 100 * (int)$height;
                            }

                            $height = Coercion::toInt($height);
                        } else {
                            $height = Coercion::toInt(
                                round($width / $output->width * $output->height)
                            );
                        }


                        $output->setWidth($width);
                        $output->setHeight($height);
                    }

                    break;

                case 'script':
                    $output = new self(null, null, null, $embed);
                    break;

                default:
                    throw Exceptional::UnexpectedValue(
                        'Don\'t know how to parse this media embed'
                    );
            }
        } else {
            $url = $embed;

            if (preg_match('/^[0-9a-zA-Z]+$/', $url)) {
                $url = self::defaultUrlFromId($url);
            } elseif (
                preg_match('|^(http(s?)\:)?//|', $url) &&
                !preg_match('/\s/', $url)
            ) {
                // Url direct
            } else {
                throw Exceptional::UnexpectedValue(
                    'Don\'t know how to parse this media embed'
                );
            }

            /** @var class-string<static> */
            $class = self::getClassForUrl($url);
            $output = new $class($url);
        }

        /* @phpstan-ignore-next-line */
        return $output;
    }


    /**
     * Extract provider name from URL
     */
    public static function extractProviderName(
        string $url
    ): ?string {
        foreach (self::UrlMap as $search => $key) {
            if (false !== stripos($url, $search)) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Get instance class for entry URL
     */
    public static function getClassForUrl(
        string $url
    ): string {
        $class = get_called_class();

        if ($provider = self::extractProviderName($url)) {
            $customClass = '\\DecodeLabs\\Tagged\\Embed\\' . ucfirst($provider);

            if (class_exists($customClass)) {
                $class = $customClass;
            }
        }

        return $class;
    }

    /**
     * Init with main iframe details
     */
    public function __construct(
        ?string $url,
        ?int $width = null,
        ?int $height = null,
        ?string $mediaSource = null
    ) {
        $this->setUrl($url);

        if ($width !== null) {
            $this->setWidth($width);
        }

        if ($height !== null) {
            $this->setHeight($height);
        }

        $this->source = $mediaSource;
        $this->id = uniqid('media-');
    }

    /**
     * Set media source URL
     *
     * @return $this
     */
    protected function setUrl(
        ?string $url
    ): static {
        if (empty($url)) {
            $this->url = null;
            return $this;
        }

        $url = str_replace('&amp;', '&', $url);

        if (false !== strpos($url, '&') && false === strpos($url, '?')) {
            $parts = explode('&', $url, 2);
            $url = implode('?', $parts);
        }

        $this->url = $url;
        $this->provider = self::extractProviderName($this->url);

        return $this;
    }

    /**
     * Get media source URL
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * Get finalized URL from renderer tag
     */
    public function getPreparedUrl(): ?string
    {
        return $this->url;
    }


    /**
     * Get media provider
     */
    public function getProvider(): ?string
    {
        return $this->provider;
    }


    /**
     * Set Media element id
     *
     * @return $this
     */
    public function setId(
        ?string $id
    ): static {
        $this->id = $id;
        return $this;
    }

    /**
     * Get Media element id
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Set media origin
     *
     * @return $this
     */
    public function setOrigin(
        ?string $origin
    ): static {
        $this->origin = $origin;
        return $this;
    }

    /**
     * Get media origin
     */
    public function getOrigin(): ?string
    {
        return $this->origin;
    }


    /**
     * Set Media element width
     *
     * @return $this
     */
    public function setWidth(
        ?int $width
    ): static {
        $this->width = $width;
        return $this;
    }

    /**
     * Scale dimensions from original width
     *
     * @return $this
     */
    public function scaleWidth(
        int $width
    ): static {
        $this->height = (int)round($width / $this->width * $this->height);
        $this->width = $width;

        return $this;
    }

    /**
     * Get Media element width
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * Set Media element height
     *
     * @return $this
     */
    public function setHeight(
        ?int $height
    ): static {
        $this->height = $height;
        return $this;
    }

    /**
     * Get Media element height
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * Set width and height and scale accordingly
     *
     * @return $this
     */
    public function setDimensions(
        ?int $width,
        ?int $height = null
    ): static {
        $width = $width;
        $height = $height;

        if (!$height) {
            if ($width) {
                return $this->scaleWidth($width);
            } else {
                return $this;
            }
        }

        if (!$width) {
            $width = (int)round($height / $this->height * $this->width);
        }

        $this->width = $width;
        $this->height = $height;

        return $this;
    }


    /**
     * Set whether Media element can go full screen
     *
     * @return $this
     */
    public function setAllowFullScreen(
        bool $flag
    ): static {
        $this->allowFullScreen = $flag;
        return $this;
    }

    /**
     *  Can element go full screen?
     */
    public function shouldAllowFullScreen(): bool
    {
        return $this->allowFullScreen;
    }


    /**
     * Set whether media can autoplay
     *
     * @return $this
     */
    public function setAutoPlay(
        bool $autoplay
    ): static {
        $this->autoPlay = $autoplay;
        return $this;
    }

    /**
     * Get whether media can autoplay
     */
    public function shouldAutoPlay(): bool
    {
        return $this->autoPlay;
    }


    /**
     * Set start time of media
     *
     * @return $this
     */
    public function setStartTime(
        ?int $seconds
    ): static {
        $this->startTime = $seconds;
        return $this;
    }

    /**
     * Get start time of media
     */
    public function getStartTime(): ?int
    {
        return $this->startTime;
    }

    /**
     * Set end time of media
     *
     * @return $this
     */
    public function setEndTime(
        ?int $seconds
    ): static {
        $this->endTime = $seconds;

        if ($this->endTime) {
            $this->duration = null;
        }

        return $this;
    }

    /**
     * Get end time of media
     */
    public function getEndTime(): ?int
    {
        return $this->endTime;
    }

    /**
     * Set media duration
     *
     * @return $this
     */
    public function setDuration(
        ?int $seconds
    ): static {
        $this->duration = $seconds;

        if ($this->duration) {
            $this->endTime = null;
        }

        return $this;
    }

    /**
     * Get media duration
     */
    public function getDuration(): ?int
    {
        return $this->duration;
    }


    /**
     * Set consent status
     *
     * @return $this
     */
    public function setConsent(
        bool $consent
    ): static {
        $this->consent = $consent;
        return $this;
    }

    /**
     * Has consent
     */
    public function hasConsent(): bool
    {
        return $this->consent;
    }


    /**
     * Lookup thumbnail URL
     */
    public function lookupThumbnail(
        ?array $options = null
    ): ?string {
        return null;
    }

    /**
     * Lookup media meta information
     */
    public function lookupMeta(
        ?array $options = null
    ): ?array {
        return null;
    }

    /**
     * Render to string
     */
    public function __toString(): string
    {
        return (string)$this->render();
    }

    /**
     * Serialize to json
     */
    public function jsonSerialize(): mixed
    {
        return (string)$this;
    }
}
