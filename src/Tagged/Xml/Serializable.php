<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged\Xml;

use DecodeLabs\Tagged\Xml\Element;
use DecodeLabs\Tagged\Xml\Writer;
use DecodeLabs\Tagged\Xml\Consumer;
use DecodeLabs\Tagged\Xml\Provider;

interface Serializable extends Consumer, Provider
{
    public function xmlUnserialize(Element $element): void;
    public function xmlSerialize(Writer $writer): void;
}
