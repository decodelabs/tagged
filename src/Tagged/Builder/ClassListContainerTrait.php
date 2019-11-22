<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged\Builder;

use DecodeLabs\Collections\ArrayUtils;

trait ClassListContainerTrait
{
    /**
     * Replace class list
     */
    public function setClasses(...$classes): ClassListContainer
    {
        $classes = ArrayUtils::collapse($classes, false, true, true);
        $this->getClasses()->clear()->add(...$classes);
        return $this;
    }

    /**
     * Add class set to list
     */
    public function addClasses(...$classes): ClassListContainer
    {
        $classes = ArrayUtils::collapse($classes, false, true, true);
        $this->getClasses()->add(...$classes);
        return $this;
    }

    /**
     * Get class list from attribute set
     */
    public function getClasses(): ClassList
    {
        if (!isset($this->attributes['class'])) {
            $this->attributes['class'] = new ClassList();
        }

        return $this->attributes['class'];
    }

    /**
     * Add class set to list
     */
    public function setClass(?string ...$classes): ClassListContainer
    {
        return $this->getClasses()->clear()->add(...$classes);
    }

    /**
     * Get class list from attribute set
     */
    public function addClass(?string ...$classes): ClassListContainer
    {
        return $this->getClasses()->add(...$classes);
    }

    /**
     * Remove set of classes from list
     */
    public function removeClass(?string ...$classes): ClassListContainer
    {
        $this->getClasses()->remove(...$classes);
        return $this;
    }

    /**
     * Does class list have any of these?
     */
    public function hasClass(string ...$classes): bool
    {
        return $this->getClasses()->has(...$classes);
    }

    /**
     * Does class list have ALL of these?
     */
    public function hasClasses(string ...$classes): bool
    {
        return $this->getClasses()->hasAll(...$classes);
    }

    /**
     * Reset class list
     */
    public function clearClasses(): ClassListContainer
    {
        $this->getClasses()->clear();
        return $this;
    }

    /**
     * How many classes do we have?
     */
    public function countClasses(): int
    {
        return $this->getClasses()->count();
    }
}
