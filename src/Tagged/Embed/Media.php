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
    public static function parse(string $embed): static;

    public static function extractProviderName(string $url): ?string;
    public static function getClassForUrl(string $url): string;
    public static function defaultUrlFromId(string $id): string;

    public function getUrl(): ?string;
    public function getPreparedUrl(): ?string;
    public function getProvider(): ?string;

    /**
     * @return static
     */
    public function setId(?string $id): static;

    public function getId(): ?string;

    /**
     * @return static
     */
    public function setOrigin(?string $origin): static;
    public function getOrigin(): ?string;

    /**
     * @return static
     */
    public function setWidth(?int $width): static;

    /**
     * @return static
     */
    public function scaleWidth(int $width): static;

    public function getWidth(): ?int;

    /**
     * @return static
     */
    public function setHeight(?int $height): static;

    public function getHeight(): ?int;
    public function setDimensions(?int $width, ?int $height = null): static;

    /**
     * @return static
     */
    public function setAllowFullScreen(bool $flag): static;
    public function shouldAllowFullScreen(): bool;

    /**
     * @return static
     */
    public function setAutoPlay(bool $flag): static;

    public function shouldAutoPlay(): bool;

    /**
     * @return static
     */
    public function setStartTime(?int $seconds): static;

    public function getStartTime(): ?int;

    /**
     * @return static
     */
    public function setEndTime(?int $seconds): static;

    public function getEndTime(): ?int;

    /**
     * @return static
     */
    public function setDuration(?int $seconds): static;

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
