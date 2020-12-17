<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Xml;

interface Consumer
{
    public static function fromXml($xml);
    public static function fromXmlFile(string $path);
    public static function fromXmlString(string $xml);
    public static function fromXmlElement(Element $element);
}
