<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Builder;

use DecodeLabs\Collections\Sequence;
use DecodeLabs\Tagged\Buffer;

interface Element extends Tag, Sequence
{
    public function setBody($body): Element;
    public function render(bool $pretty = false): ?Buffer;
    public function renderContent(bool $pretty = false): ?Buffer;
}
