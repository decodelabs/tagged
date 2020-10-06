<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Xml;

use DecodeLabs\Atlas\File;

interface Provider
{
    public function toXmlString(bool $embedded = false): string;
    public function toXmlFile(string $path): File;
    public function toXmlElement(): Element;
}
