<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged\Builder;

use DecodeLabs\Tagged\Markup;

use DecodeLabs\Collections\AttributeContainer;
use DecodeLabs\Collections\Sequence;

interface Element extends Tag, Sequence
{
    public function setBody($body): Element;
    public function render(bool $pretty=false): string;
    public function renderContent(bool $pretty=false): Markup;
}
