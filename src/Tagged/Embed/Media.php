<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Embed;

use DecodeLabs\Tagged\Markup;

interface Media extends Markup
{
    /**
     * @return static
     */
    public static function parse(string $embed): Media;

    public static function extractProviderName(string $url): ?string;
    public static function getClassForUrl(string $url): string;
    public static function defaultUrlFromId(string $id): string;

    public function getUrl(): ?string;
    public function getPreparedUrl(): ?string;
    public function getProvider(): ?string;

    /**
     * @return static
     */
    public function setId(?string $id): Media;

    public function getId(): ?string;

    /**
     * @return static
     */
    public function setOrigin(?string $origin): Media;
    public function getOrigin(): ?string;

    /**
     * @return static
     */
    public function setWidth(?int $width): Media;

    /**
     * @return static
     */
    public function scaleWidth(int $width): Media;

    public function getWidth(): ?int;

    /**
     * @return static
     */
    public function setHeight(?int $height): Media;

    public function getHeight(): ?int;
    public function setDimensions(?int $width, ?int $height = null): Media;

    /**
     * @return static
     */
    public function setAllowFullScreen(bool $flag): Media;
    public function shouldAllowFullScreen(): bool;

    /**
     * @return static
     */
    public function setAutoPlay(bool $flag): Media;

    public function shouldAutoPlay(): bool;

    /**
     * @return static
     */
    public function setStartTime(?int $seconds): Media;

    public function getStartTime(): ?int;

    /**
     * @return static
     */
    public function setEndTime(?int $seconds): Media;

    public function getEndTime(): ?int;

    /**
     * @return static
     */
    public function setDuration(?int $seconds): Media;

    public function getDuration(): ?int;

    /**
     * @param array<string, mixed> $options
     */
    public function lookupThumbnail(?array $options = null): ?string;

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>|null
     */
    public function lookupMeta(?array $options = null): ?array;

    public function render(): ?Markup;
    public function __toString(): string;
}
