<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Component;

use Closure;
use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\ContentCollection;
use DecodeLabs\Tagged\Component;
use DecodeLabs\Tagged\Element;
use DecodeLabs\Tagged\RenderableTrait;
use DecodeLabs\Tagged\Tag;

class Elements extends Tag implements Component
{
    use RenderableTrait;

    public ?string $elementName;

    /**
     * @var iterable<mixed>|Closure():(iterable<mixed>)|null
     */
    public iterable|Closure|null $items;

    public ?Closure $renderer = null;

    /**
     * @var array<string,mixed>|null
     */
    public ?array $elementAttributes = null;

    /**
     * Generate list of elements
     *
     * @param iterable<mixed>|Closure():(iterable<mixed>)|null $items
     * @param array<string,mixed>|null $attributes
     */
    public function __construct(
        iterable|Closure|null $items,
        ?string $elementName,
        ?callable $renderer = null,
        ?array $attributes = null
    ) {
        parent::__construct(null, $attributes);
        $this->items = $items;
        $this->renderer = $renderer ? Closure::fromCallable($renderer) : null;
        $this->elementName = $elementName;
        $this->elementAttributes = $attributes;
    }

    public function render(
        bool $pretty = false
    ): ?Buffer {
        return ContentCollection::normalize(function () {
            $items = $this->items;

            if($items instanceof Closure) {
                $items = $items();
            }

            if (!is_iterable($items)) {
                return;
            }

            $i = 0;

            foreach ($items as $key => $item) {
                $i++;

                if ($this->elementName === null) {
                    // Unwrapped
                    if ($this->renderer) {
                        yield ($this->renderer)($item, null, $key, $i);
                    } else {
                        yield $item;
                    }
                } else {
                    // Wrapped
                    yield Element::create($this->elementName, function ($el) use ($key, $item, $i) {
                        if ($this->renderer) {
                            return ($this->renderer)($item, $el, $key, $i);
                        } else {
                            return $item;
                        }
                    }, $this->elementAttributes);
                }
            }
        }, $pretty);
    }
}
