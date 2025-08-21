<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Component;

use Closure;
use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\Component;
use DecodeLabs\Tagged\ContentCollection;
use DecodeLabs\Tagged\RenderableTrait;
use DecodeLabs\Tagged\Tag;

class Each extends Tag implements Component
{
    use RenderableTrait;

    /**
     * @var iterable<mixed>|Closure():(iterable<mixed>)|null
     */
    public iterable|Closure|null $items;

    public ?Closure $renderer = null;

    /**
     * @param iterable<mixed>|Closure():(iterable<mixed>)|null $items
     */
    public function __construct(
        iterable|Closure|null $items,
        ?callable $renderer = null
    ) {
        parent::__construct(null);
        $this->items = $items;
        $this->renderer = $renderer ? Closure::fromCallable($renderer) : null;
    }

    public function render(
        bool $pretty = false
    ): ?Buffer {
        return ContentCollection::normalize(function () {
            $items = $this->items;

            if ($items instanceof Closure) {
                $items = $items();
            }

            if (!is_iterable($items)) {
                return;
            }

            $i = 0;

            foreach ($items as $key => $item) {
                $i++;

                if ($this->renderer) {
                    yield ($this->renderer)($item, null, $key, $i);
                } else {
                    yield $item;
                }
            }
        }, $pretty);
    }
}
