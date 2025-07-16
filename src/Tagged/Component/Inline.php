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

class Inline extends Tag implements Component
{
    use RenderableTrait;

    /**
     * @var iterable<mixed>|Closure():(iterable<mixed>)|null
     */
    public iterable|Closure|null $items;

    public ?Closure $renderer = null;
    public ?string $delimiter = null;
    public ?string $finalDelimiter = null;
    public ?int $limit = null;

    /**
     * Generate inline list
     *
     * @param iterable<mixed>|Closure():(iterable<mixed>)|null $items
     * @param array<string,mixed>|null $attributes
     */
    public function __construct(
        iterable|Closure|null $items,
        ?callable $renderer = null,
        ?string $delimiter = null,
        ?string $finalDelimiter = null,
        ?int $limit = null,
        ?array $attributes = null
    ) {
        parent::__construct('span.list', $attributes);
        $this->items = $items;
        $this->renderer = $renderer ? Closure::fromCallable($renderer) : null;
        $this->delimiter = $delimiter;
        $this->finalDelimiter = $finalDelimiter;
        $this->limit = $limit;
    }

    public function render(
        bool $pretty = false
    ): ?Buffer {
        $this->renderEmpty = false;

        return $this->renderWith(function () {
            $items = $this->items;

            if ($items instanceof Closure) {
                $items = $items();
            }

            if (!is_iterable($items)) {
                return;
            }

            $first = true;
            $i = $more = 0;

            $delimiter = $this->delimiter ?? ', ';
            $finalDelimiter = $this->finalDelimiter ?? $delimiter;

            $itemSet = [];

            foreach ($items as $key => $item) {
                if ($item === null) {
                    continue;
                }

                $i++;

                $cellTag = Element::create('?span', function (Element $el) use ($key, $item, &$i) {
                    if ($this->renderer) {
                        return ($this->renderer)($item, $el, $key, $i);
                    } else {
                        return $item;
                    }
                });

                if (empty($tagString = (string)$cellTag)) {
                    $i--;
                    continue;
                }

                if (
                    $this->limit !== null &&
                    $i > $this->limit
                ) {
                    $more++;
                    continue;
                }


                $itemSet[] = new Buffer($tagString);
            }

            $total = count($itemSet);

            foreach ($itemSet as $i => $item) {
                if (!$first) {
                    if ($i + 1 == $total) {
                        yield $finalDelimiter;
                    } else {
                        yield $delimiter;
                    }
                }

                yield $item;

                $first = false;
            }

            if ($more) {
                yield Element::create('em.more', '… +' . $more); // @ignore-non-ascii
            }
        }, $pretty);
    }
}
