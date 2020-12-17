<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Xml;

use DecodeLabs\Atlas\File;
use DecodeLabs\Exceptional;

trait SerializableTrait
{
    /**
     * Create from any xml type
     */
    public static function fromXml($xml)
    {
        if ($xml instanceof self) {
            return $xml;
        } elseif ($xml instanceof Provider) {
            return static::fromXmlElement($xml->toXmlElement());
        } elseif ($xml instanceof DOMDocument) {
            return static::fromXmlElement(Element::fromDomDocument($xml));
        } elseif ($xml instanceof DOMElement) {
            return static::fromXmlElement(Element::fromDomElement($xml));
        } elseif ($xml instanceof File) {
            return static::fromXmlFile($xml->getPath());
        } elseif (is_string($xml) || (is_object($xml) && method_exists($xml, '__toString'))) {
            return static::fromXmlString((string)$xml);
        } else {
            throw Exceptional::UnexpectedValue(
                'Unable to convert item to XML Element',
                null,
                $xml
            );
        }
    }

    /**
     * Load object from xml file
     */
    public static function fromXmlFile(string $path)
    {
        return static::fromXmlElement(Element::fromXmlFile($path));
    }

    /**
     * Load object from xml string
     */
    public static function fromXmlString(string $xml)
    {
        return static::fromXmlElement(Element::fromXmlString($xml));
    }

    /**
     * Load object using xmlUnserialize as constructor
     */
    public static function fromXmlElement(Element $element)
    {
        $class = get_called_class();
        $ref = new \ReflectionClass($class);

        if (!$ref->isInstantiable()) {
            throw Exceptional::Logic(
                'XML consumer cannot be instantiated',
                null,
                $class
            );
        }

        if (!$ref->implementsInterface(Serializable::class)) {
            throw Exceptional::Logic(
                'XML consumer does not implement DecodeLabs\\Tagged\\Xml\\Serializable',
                null,
                $class
            );
        }

        $output = $ref->newInstanceWithoutConstructor();
        $output->xmlUnserialize($element);

        return $output;
    }


    /**
     * Convert object to xml string
     */
    public function toXmlString(bool $embedded = false): string
    {
        $writer = Writer::create();

        if (!$embedded) {
            $writer->writeHeader();
        }

        $this->xmlSerialize($writer);
        return $writer->toXmlString($embedded);
    }

    /**
     * Convert object to xml file
     */
    public function toXmlFile(string $path): File
    {
        $writer = Writer::createFile($path);
        $this->xmlSerialize($writer);
        return $writer->toXmlFile($path);
    }

    /**
     * Convert object to XML Element
     */
    public function toXmlElement(): Element
    {
        return Element::fromXmlString($this->toXmlString());
    }
}
