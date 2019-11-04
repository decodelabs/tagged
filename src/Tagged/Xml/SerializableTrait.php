<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged\Xml;

use DecodeLabs\Tagged\Xml\Element;
use DecodeLabs\Tagged\Xml\Consumer;
use DecodeLabs\Tagged\Xml\Provider;
use DecodeLabs\Tagged\Xml\Serializable;

use DecodeLabs\Atlas\File;

trait SerializableTrait
{
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
        return static::fromXmlElement(Element::fromXmlString($string));
    }

    /**
     * Load object using xmlUnserialize as constructor
     */
    public static function fromXmlElement(Element $element)
    {
        $class = get_called_class();
        $ref = new \ReflectionClass($class);

        if ($ref->isInstantable()) {
            throw Glitch::ELogic('XML consumer cannot be instantiated', null, $class);
        }

        if (!$ref->implementsInterface(Serializable::class)) {
            throw Glitch::ELogic('XML consumer does not implement DecodeLabs\\Tagged\\Xml\\Serializable', null, $class);
        }

        $output = $ref->newInstanceWithoutConstructor();
        $output->xmlUnserialize($element);

        return $output;
    }


    /**
     * Convert object to xml string
     */
    public function toXmlString(bool $embedded=false): string
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
