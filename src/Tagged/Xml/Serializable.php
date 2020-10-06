<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Xml;

interface Serializable extends Consumer, Provider
{
    public function xmlUnserialize(Element $element): void;
    public function xmlSerialize(Writer $writer): void;
}
