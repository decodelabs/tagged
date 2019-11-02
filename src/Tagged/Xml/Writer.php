<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged\Xml;

use DecodeLabs\Tagged\Xml\Element;

use DecodeLabs\Collections\AttributeContainer;
use DecodeLabs\Collections\AttributeContainerTrait;
use DecodeLabs\Glitch;

use XMLWriter;
use ArrayAccess;

class Writer implements AttributeContainer, ArrayAccess
{
    const ELEMENT = 1;
    const CDATA = 2;
    const COMMENT = 3;
    const PI = 4;

    use AttributeContainerTrait;

    protected $document;
    protected $path;
    protected $headerWritten = false;
    protected $dtdWritten = false;
    protected $rootWritten = false;
    protected $finalized = false;

    protected $elementContent = null;
    protected $rawAttributeNames = [];
    protected $currentNode = null;

    /**
     * Init with optional file path
     */
    public function __construct(string $path=null)
    {
        $this->document = new XMLWriter();

        if ($path !== null) {
            $dir = dirname($path);

            if (!is_dir($dir)) {
                throw Glitch::EIo('Xml path is not writable');
            }

            $this->path = $path;
            $this->document->openURI($path);
        } else {
            $this->document->openMemory();
        }

        $this->document->setIndent(true);
        $this->document->setIndentString('    ');
    }


    /**
     * Write initial XML header
     */
    public function writeHeader(string $version='1.0', string $encoding='UTF-8', bool $standalone=false): Writer
    {
        if ($this->headerWritten) {
            throw Glitch::ELogic('XML header has already been written');
        }

        if ($this->dtdWritten || $this->rootWritten) {
            throw Glitch::ELogic('XML header cannot be written once the document is open');
        }

        try {
            $this->document->startDocument($version, $encoding, $standalone ? true : null);
        } catch (\ErrorException $e) {
            throw Glitch::EInvalidArguement($e->getMessage(), [
                'previous' => $e
            ]);
        }

        $this->headerWritten = true;
        return $this;
    }

    /**
     * Write full DTD
     */
    public function writeDtd(string $name, string $publicId=null, string $systemId=null, string $subset=null): Writer
    {
        if ($this->rootWritten) {
            throw Glitch::ELogic('XML DTD cannot be written once the document is open');
        }

        if (!$this->headerWritten) {
            $this->writeHeader();
        }

        try {
            $this->document->writeDtd($name, $publicId, $systemId, $subset);
        } catch (\ErrorException $e) {
            throw Glitch::EInvalidArguement($e->getMessage(), [
                'previous' => $e
            ]);
        }

        $this->dtdWritten = true;
        return $this;
    }

    /**
     * Write DTD attlist
     */
    public function writeDtdAttlist(string $name, string $content): Writer
    {
        if ($this->rootWritten) {
            throw Glitch::ELogic('XML DTD cannot be written once the document is open');
        }

        if (!$this->headerWritten) {
            $this->writeHeader();
        }

        try {
            $this->document->writeDtdAttlist($name, $content);
        } catch (\ErrorException $e) {
            throw Glitch::EInvalidArguement($e->getMessage(), [
                'previous' => $e
            ]);
        }

        $this->dtdWritten = true;
        return $this;
    }

    /**
     * Write DTD element
     */
    public function writeDtdElement(string $name, string $content): Writer
    {
        if ($this->rootWritten) {
            throw Glitch::ELogic('XML DTD cannot be written once the document is open');
        }

        if (!$this->headerWritten) {
            $this->writeHeader();
        }

        try {
            $this->document->writeDtdElement($name, $content);
        } catch (\ErrorException $e) {
            throw Glitch::EInvalidArguement($e->getMessage(), [
                'previous' => $e
            ]);
        }

        $this->dtdWritten = true;
        return $this;
    }

    /**
     * Write DTD entity
     */
    public function writeDtdEntity(string $name, string $content, string $pe, string $publicId, string $systemId, string $nDataId): Writer
    {
        if ($this->rootWritten) {
            throw Glitch::ELogic('XML DTD cannot be written once the document is open');
        }

        if (!$this->headerWritten) {
            $this->writeHeader();
        }

        try {
            $this->document->writeDtdEntity($name, $content, $pe, $publicId, $systemId, $nDataId);
        } catch (\ErrorException $e) {
            throw Glitch::EInvalidArguement($e->getMessage(), [
                'previous' => $e
            ]);
        }

        $this->dtdWritten = true;
        return $this;
    }



    /**
     * Write full element in one go
     */
    public function writeElement(string $name, string $content=null, array $attributes=null): Writer
    {
        $this->startElement($name, $attributes);

        if ($content !== null) {
            $this->setElementContent($content);
        }

        return $this->endElement();
    }

    /**
     * Open element to write into
     */
    public function startElement(string $name, array $attributes=null): Writer
    {
        $this->completeCurrentNode();
        $this->document->startElement($name);

        if ($attributes !== null) {
            $this->setAttributes($attributes);
        }

        $this->currentNode = self::ELEMENT;
        $this->rootWritten = true;

        return $this;
    }

    /**
     * Complete writing current element
     */
    public function endElement(): Writer
    {
        if ($this->currentNode !== self::ELEMENT) {
            throw Glitch::ELogic('XML writer is not currently writing an element');
        }

        $this->completeCurrentNode();
        $this->document->endElement();
        $this->currentNode = self::ELEMENT;

        return $this;
    }

    /**
     * Store element content ready for writing
     */
    public function setElementContent(string $content): Writer
    {
        $this->elementContent = $content;
        return $this;
    }

    /**
     * Get current buffered element content
     */
    public function getElementContent(): ?string
    {
        return $this->elementContent;
    }



    /**
     * Write a full CDATA section
     */
    public function writeCData(string $content): Writer
    {
        $this->startCData();
        $this->writeCDataContent($content);
        return $this->endCData();
    }

    /**
     * Write new element with CDATA section
     */
    public function writeCDataElement(string $name, string $content, array $attributes=null): Writer
    {
        $this->startElement($name, $attributes);
        $this->writeCData($content);
        return $this->endElement();
    }

    /**
     * Start new CDATA section
     */
    public function startCData(): Writer
    {
        $this->completeCurrentNode();
        $this->document->startCData();
        $this->currentNode = self::CDATA;
        return $this;
    }

    /**
     * Write content for CDATA section
     */
    public function writeCDataContent(string $content): Writer
    {
        if ($this->currentNode !== self::CDATA) {
            throw Glitch::ELogic('XML writer is not current writing CDATA');
        }

        $content = self::normalizeString($content);
        $this->document->text($content);
        return $this;
    }

    /**
     * Finalize CDATA section
     */
    public function endCData(): Writer
    {
        if ($this->currentNode !== self::CDATA) {
            throw Glitch::ELogic('XML writer is not current writing CDATA');
        }

        $this->document->endCData();
        $this->currentNode = self::ELEMENT;
        return $this;
    }


    /**
     * Write comment in one go
     */
    public function writeComment(string $comment): Writer
    {
        $this->startComment();
        $this->writeCommentContent($comment);
        return $this->endComment();
    }

    /**
     * Begin comment node
     */
    public function startComment(): Writer
    {
        $this->completeCurrentNode();
        $this->document->startComment();
        $this->currentNode = self::COMMENT;
        return $this;
    }

    /**
     * Write comment body
     */
    public function writeCommentContent(string $comment): Writer
    {
        if ($this->currentNode !== self::COMMENT) {
            throw Glitch::ELogic('XML writer is not currently writing a comment');
        }

        $comment = self::normalizeString($comment);
        $this->document->text($comment);
        return $this;
    }

    /**
     * Finalize comment node
     */
    public function endComment(): Writer
    {
        if ($this->currentNode !== self::COMMENT) {
            throw Glitch::ELogic('XML writer is not currently writing a comment');
        }

        $this->document->endComment();
        $this->currentNode = self::ELEMENT;
        return $this;
    }


    /**
     * Write PI in one go
     */
    public function writePi(string $target, string $content): Writer
    {
        $this->startPi($target);
        $this->writePiContent($content);
        return $this->endPi();
    }

    /**
     * Begin PI node
     */
    public function startPi(string $target): Writer
    {
        $this->completeCurrentNode();
        $this->document->startPI($target);
        $this->currentNode = self::PI;
        return $this;
    }

    /**
     * Write PI content
     */
    public function writePiContent(string $content): Writer
    {
        if ($this->currentNode !== self::PI) {
            throw Glitch::ELogic('XML writer is not currently writing a processing instruction');
        }

        $this->document->text($content);
        return $this;
    }

    /**
     * Finalize PI
     */
    public function endPi(): Writer
    {
        if ($this->currentNode !== self::PI) {
            throw Glitch::ELogic('XML writer is not currently writing a processing instruction');
        }

        $this->document->endPI();
        $this->currentNode = self::ELEMENT;
        return $this;
    }



    /**
     * Set list of attribute names to be written raw
     */
    public function setRawAttributeNames(string ...$names): Writer
    {
        $this->rawAttributeNames = $names;
        return $this;
    }

    /**
     * Get list of attributes to be written raw
     */
    public function getRawAttributeNames(): array
    {
        return $this->rawAttributeNames;
    }



    /**
     * Write directly to XML buffer
     */
    public function writeRaw(string $content): Writer
    {
        $this->document->writeRaw($content);
        return $this;
    }


    /**
     * Write stored info to doc
     */
    protected function completeCurrentNode(): void
    {
        switch ($this->currentNode) {
            case self::ELEMENT:
                foreach ($this->attributes as $key => $value) {
                    if (is_bool($value)) {
                        $value = $value ? 'true' : 'false';
                    }

                    if (in_array($key, $this->rawAttributeNames)) {
                        $this->document->startAttribute($key);
                        $this->document->writeRaw($value);
                        $this->document->endAttribute();
                    } else {
                        $this->document->writeAttribute($key, $value);
                    }
                }

                $this->attributes = [];
                $this->rawAttributeNames = [];

                if ($this->elementContent !== null) {
                    $content = self::normalizeString($this->elementContent);
                    $this->document->text($content);
                    $this->elementContent = null;
                }

                break;

            case self::CDATA:
                $this->endCData();
                break;

            case self::COMMENT:
                $this->endComment();
                break;

            case self::PI:
                $this->endPi();
                break;
        }
    }


    /**
     * Ensure everything is written to buffer
     */
    public function finalize(): Writer
    {
        if ($this->finalized) {
            return $this;
        }

        $this->completeCurrentNode();

        if ($this->headerWritten) {
            $this->document->endDocument();
        }

        if ($this->path) {
            $this->document->flush();
        }

        $this->finalized = true;
        return $this;
    }

    /**
     * Convert to
     */
    public function toReader(): Element
    {
        return Element::fromString($this->__toString());
    }

    /**
     * Import XML string from reader node
     */
    public function importReader(Element $reader)
    {
        $this->completeCurrentNode();
        $this->document->writeRaw($reader->__toString());
        return $this;
    }

    /**
     * Normalize string for writing
     */
    protected static function normalizeString(string $string): string
    {
        return (string)preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $string);
    }

    /**
     * Convert to string
     */
    public function __toString(): string
    {
        if ($this->path) {
            if (false === ($output = file_get_contents($this->path))) {
                throw Glitch::EUnexpectedValue('Unable to read contents of file', null, $this->path);
            }

            return $output;
        } else {
            return $this->document->outputMemory();
        }
    }


    /**
     * Shortcut to set attribute
     */
    public function offsetSet($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Shortcut to get attribute
     */
    public function offsetGet($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Shortcut to test for attribute
     */
    public function offsetExists($key)
    {
        return $this->hasAttribute($key);
    }

    /**
     * Shortcut to remove attribute
     */
    public function offsetUnset($key)
    {
        $this->removeAttribute($key);
    }

    /**
     * Dump string
     */
    public function __debugInfo(): array
    {
        return [
            'xml' => $this->__toString()
        ];
    }
}
