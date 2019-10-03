<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged\Embed;

use DecodeLabs\Tagged\Markup;

interface Media extends Markup
{
    public static function parse(string $mbed): Media;
    public static function extractProviderName(string $url): ?string;
    public static function getClassForUrl(string $url): string;
    public static function defaultUrlFromId(string $id): string;

    public function getUrl(): ?string;
    public function getPreparedUrl(): ?string;
    public function getProvider(): ?string;

    public function setId(?string $id): Media;
    public function getId(): ?string;
    public function setOrigin(?string $origin): Media;
    public function getOrigin(): ?string;

    public function setWidth(?int $width): Media;
    public function scaleWidth(int $width): Media;
    public function getWidth(): ?int;
    public function setHeight(?int $height): Media;
    public function getHeight(): ?int;
    public function setDimensions(?int $width, ?int $height=null);

    public function setAllowFullScreen(bool $flag);
    public function shouldAllowFullScreen(): bool;

    public function setAutoPlay(bool $flag);
    public function shouldAutoPlay(): bool;

    public function setStartTime(?int $seconds): Media;
    public function getStartTime(): ?int;
    public function setEndTime(?int $seconds): Media;
    public function getEndTime(): ?int;
    public function setDuration(?int $seconds): Media;
    public function getDuration(): ?int;

    public function lookupThumbnail(): ?string;
    public function lookupMeta(): ?array;

    public function render(): ?Markup;
    public function __toString(): string;
}
