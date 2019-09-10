<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged\Html;

use DecodeLabs\Glitch\Inspectable;
use DecodeLabs\Glitch\Dumper\Entity;
use DecodeLabs\Glitch\Dumper\Inspector;

use DecodeLabs\Tagged\ElementContentTrait;
use DecodeLabs\Collections\Sequence;

class Element extends Tag implements \IteratorAggregate, Sequence
{
    use ElementContentTrait;

    const MUTABLE = true;

    /**
     * Init with name, content and attributes
     */
    public function __construct(string $name, $content, array $attributes=null)
    {
        parent::__construct($name, $attributes);

        if (!is_iterable($content)) {
            $content = [$content];
        }

        $this->merge($content);
    }

    /**
     * Render to string
     */
    public function __toString(): string
    {
        return (string)$this->renderWith($this->renderContent());
    }

    /**
     * Replace all content with new body
     */
    public function setBody($body): Element
    {
        $this->clear()->push($body);
        return $this;
    }


    /**
     * Inspect for Glitch
     */
    public function glitchInspect(Entity $entity, Inspector $inspector): void
    {
        parent::glitchInspect($entity, $inspector);

        $entity
            ->setValues($inspector->inspectList($this->items))
            ->hideSection('values');
    }
}
