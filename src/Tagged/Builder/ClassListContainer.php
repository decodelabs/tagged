<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged\Builder;

use DecodeLabs\Collections\AttributeContainer;

interface ClassListContainer extends AttributeContainer
{
    public function setClasses(...$classes): ClassListContainer;
    public function addClasses(...$classes): ClassListContainer;
    public function getClasses(): ClassList;
    public function setClass(string ...$classes): ClassListContainer;
    public function addClass(string ...$classes): ClassListContainer;
    public function removeClass(string ...$classes): ClassListContainer;
    public function hasClass(string ...$classes): bool;
    public function hasClasses(string ...$classes): bool;
    public function clearClasses(): ClassListContainer;
    public function countClasses(): int;
}
