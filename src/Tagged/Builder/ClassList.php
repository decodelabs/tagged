<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged\Builder;

use DecodeLabs\Glitch\Inspectable;
use DecodeLabs\Glitch\Dumper\Entity;
use DecodeLabs\Glitch\Dumper\Inspector;

class ClassList implements \Countable, Inspectable
{
    protected $classes = [];

    /**
     * Init with list
     */
    public function __construct(string ...$classes)
    {
        $this->add(...$classes);
    }

    /**
     * Add class list
     */
    public function add(string ...$classes): ClassList
    {
        foreach ($classes as $value) {
            foreach (explode(' ', $value) as $class) {
                $this->classes[$class] = true;
            }
        }

        return $this;
    }

    /**
     * Has class(es) in list
     */
    public function has(string ...$classes): bool
    {
        foreach ($classes as $class) {
            if (isset($this->classes[$class])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Has all classes in list
     */
    public function hasAll(string ...$classes): bool
    {
        foreach ($classes as $class) {
            if (!isset($this->classes[$class])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Remove all classes in list
     */
    public function remove(string ...$classes): ClassList
    {
        foreach ($classes as $class) {
            unset($this->classes[$class]);
        }

        return $this;
    }

    /**
     * Clear class list
     */
    public function clear(): ClassList
    {
        $this->classes = [];
        return $this;
    }

    /**
     * How many classes in list?
     */
    public function count(): int
    {
        return count($this->classes);
    }

    /**
     * Export to array
     */
    public function toArray(): array
    {
        return array_keys($this->classes);
    }

    /**
     * Render to string
     */
    public function __toString(): string
    {
        return implode(' ', array_keys($this->classes));
    }

    /**
     * Inspect for Glitch
     */
    public function glitchInspect(Entity $entity, Inspector $inspector): void
    {
        $entity
            ->setText($this->__toString());
    }
}
