<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Component;

use Closure;

class Ol extends ContainedList
{
    /**
     * @param iterable<mixed>|Closure():(iterable<mixed>)|null $items
     * @param array<string,mixed>|null $attributes
     */
    public function __construct(
        iterable|Closure|null $items,
        ?callable $renderer = null,
        ?array $attributes = null
    ) {
        parent::__construct($items, 'ol', '?li', $renderer, $attributes);
    }
}
