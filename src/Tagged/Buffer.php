<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged;

use DecodeLabs\Glitch\Inspectable;
use DecodeLabs\Glitch\Dumper\Entity;
use DecodeLabs\Glitch\Dumper\Inspector;

class Buffer implements Markup, Inspectable
{
    protected $content;

    /**
     * Init with content
     */
    public function __construct(?string $content)
    {
        $this->content = (string)$content;
    }

    /**
     * Render content to string
     */
    public function __toString(): string
    {
        return $this->content;
    }

    /**
     * Add content to start
     */
    public function prepend(?string $content): Buffer
    {
        $this->content = $content.$this->content;
        return $this;
    }

    /**
     * Add content to end
     */
    public function append(?string $content): Buffer
    {
        $this->content .= $content;
        return $this;
    }

    /**
     * Replace content
     */
    public function replace(?string $content): Buffer
    {
        $this->content = (string)$content;
        return $this;
    }

    /**
     * Is there any content here?
     */
    public function isEmpty(): bool
    {
        return $this->content === '';
    }


    /**
     * Inspect for Glitch
     */
    public function glitchInspect(Entity $entity, Inspector $inspector): void
    {
        $entity->setText($this->content);
    }
}