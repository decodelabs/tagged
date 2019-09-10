<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged\Builder;

interface StyleListContainer
{
    public function setStyles(...$styles): StyleListContainer;
    public function addStyles(...$styles): StyleListContainer;
    public function getStyles(): StyleList;
    public function setStyle(string $key, ?string $value): StyleListContainer;
    public function getStyle(string $key): ?string;
    public function removeStyle(string ...$keys): StyleListContainer;
    public function hasStyle(string ...$keys): bool;
    public function hasStyles(string ...$keys): bool;
    public function clearStyles(): StyleListContainer;
}
