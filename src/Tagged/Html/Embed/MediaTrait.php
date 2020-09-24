<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged\Html\Embed;

use DecodeLabs\Tagged\Html\Embed\Media;

use DecodeLabs\Glitch;

trait MediaTrait
{
    //const URL_MAP = [];

    protected $url;
    protected $provider;

    protected $id;
    protected $origin;

    protected $width = 640;
    protected $height = 360;

    protected $allowFullScreen = true;
    protected $autoPlay = false;

    protected $startTime;
    protected $endTime;
    protected $duration;

    protected $source;


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
                    'Don\'t know how to parse this embed'
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
                    $class = self::getClassForUrl($url);
                    $output = new $class($url, null, null, $embed);

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
                        'Don\'t know how to parse this video embed'
                    );
            }
        } else {
            $url = $embed;

            if (preg_match('/^[0-9a-zA-Z]+$/', $url)) {
                $url = self::defaultUrlFromId($url);
            }

            $class = self::getClassForUrl($url);
            $output = new $class($url);
        }

        return $output;
    }


    /**
     * Extract provider name from URL
     */
    public static function extractProviderName(string $url): ?string
    {
        foreach (self::URL_MAP as $search => $key) {
            if (false !== stripos($url, $search)) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Get instance class for entry URL
     */
    public static function getClassForUrl(string $url): string
    {
        $class = get_called_class();

        if ($provider = self::extractProviderName($url)) {
            $customClass = '\\DecodeLabs\\Tagged\\Html\\Embed\\'.ucfirst($provider);

            if (class_exists($customClass)) {
                $class = $customClass;
            }
        }

        return $class;
    }

    /**
     * Init with main iframe details
     */
    public function __construct(?string $url, int $width=null, int $height=null, string $mediaSource=null)
    {
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
     */
    protected function setUrl(?string $url): Media
    {
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
     */
    public function setId(?string $id): Media
    {
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
     */
    public function setOrigin(?string $origin): Media
    {
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
     */
    public function setWidth(?int $width): Media
    {
        $this->width = $width;
        return $this;
    }

    /**
     * Scale dimensions from original width
     */
    public function scaleWidth(int $width): Media
    {
        $this->height = round(($width / $this->width) * $this->height);
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
     */
    public function setHeight(?int $height): Media
    {
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
     */
    public function setDimensions(?int $width, ?int $height=null)
    {
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
            $width = round(($height / $this->height) * $this->width);
        }

        $this->width = $width;
        $this->height = $height;

        return $this;
    }


    /**
     * Set whether Media element can go full screen
     */
    public function setAllowFullScreen(bool $flag)
    {
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
     */
    public function setAutoPlay(bool $autoplay)
    {
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
     */
    public function setStartTime(?int $seconds): Media
    {
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
     */
    public function setEndTime(?int $seconds): Media
    {
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
     */
    public function setDuration(?int $seconds): Media
    {
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
     * Lookup thumbnail URL
     */
    public function lookupThumbnail(?array $options=null): ?string
    {
        return null;
    }

    /**
     * Lookup media meta information
     */
    public function lookupMeta(?array $options=null): ?array
    {
        return null;
    }

    /**
     * Render to string
     */
    public function __toString(): string
    {
        return (string)$this->render();
    }
}
