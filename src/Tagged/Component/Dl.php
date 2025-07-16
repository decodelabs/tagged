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
use DecodeLabs\Tagged\Element;
use DecodeLabs\Tagged\RenderableTrait;
use DecodeLabs\Tagged\Tag;

class Dl extends Tag implements Component
{
    use RenderableTrait;

    /**
     * @var iterable<mixed>|Closure():(iterable<mixed>)|null
     */
    public iterable|Closure|null $items;

    public ?Closure $renderer = null;

    /**
     * Generate nested list
     *
     * @param iterable<mixed>|Closure():(iterable<mixed>)|null $items
     * @param array<string,mixed>|null $attributes
     */
    public function __construct(
        iterable|Closure|null $items,
        ?callable $renderer = null,
        ?array $attributes = null
    ) {
        parent::__construct('dl', $attributes);
        $this->items = $items;
        $this->renderer = $renderer ? Closure::fromCallable($renderer) : null;
    }

    public function render(
        bool $pretty = false
    ): ?Buffer {
        $this->renderEmpty = false;

        return $this->renderWith(function () use ($pretty) {
            $items = $this->items;

            if ($items instanceof Closure) {
                $items = $items();
            }

            if (!is_iterable($items)) {
                return;
            }

            $renderer = $this->renderer ?? fn ($item) => $item;

            foreach ($items as $key => $item) {
                $dt = Element::create('dt', null);

                // Render dd tag before dt so that renderer can add contents to dt first
                $dd = Element::create('dd', function ($dd) use ($key, $item, $renderer, &$i, $dt) {
                    return $renderer($item, $dt, $dd, $key, ++$i);
                })->render($pretty);

                if ($dt->isEmpty()) {
                    $dt->append($key);
                }

                yield $dt;
                yield $dd;
            }
        }, $pretty);
    }
}
