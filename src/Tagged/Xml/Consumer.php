<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged\Xml;

use DecodeLabs\Tagged\Xml\Element;

interface Consumer
{
    public static function fromXmlFile(string $path);
    public static function fromXmlString(string $xml);
    public static function fromXmlElement(Element $element);
}
