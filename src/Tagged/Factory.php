<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged;

use Closure;
use DecodeLabs\Coercion;
use DecodeLabs\Glitch\Proxy as Glitch;
use DecodeLabs\Tagged;
use DecodeLabs\Tagged\Plugins\Embed as EmbedPlugin;
use DecodeLabs\Tagged\Plugins\Icon as IconPlugin;
use DecodeLabs\Tagged\Plugins\Number as NumberPlugin;
use DecodeLabs\Tagged\Plugins\Time as TimePlugin;
use DecodeLabs\Veneer;
use DecodeLabs\Veneer\Plugin;
use Stringable;
use Throwable;

class Factory implements Markup
{
    #[Plugin(lazy: true)]
    public EmbedPlugin $embed;

    #[Plugin(lazy: true)]
    public IconPlugin $icon;

    #[Plugin(lazy: true)]
    public TimePlugin $time;

    #[Plugin(lazy: true)]
    public NumberPlugin $number;


    /**
     * Instance shortcut to el
     *
     * @param array<string, mixed>|null $attributes
     */
    public function __invoke(
        string $name,
        mixed $content,
        ?array $attributes = null
    ): Element {
        return $this->el($name, $content, $attributes);
    }

    /**
     * Call named widget from instance
     *
     * @param array<mixed> $args
     */
    public function __call(
        string $name,
        array $args
    ): Element {
        return Element::create($name, ...$args);
    }

    /**
     * Dummy string generator to satisfy Markup dep
     */
    public function __toString(): string
    {
        return '';
    }






    /**
     * Create a standalone tag
     *
     * @param array<string, mixed>|null $attributes
     */
    public function tag(
        string $name,
        ?array $attributes = null
    ): Tag {
        return new Tag($name, $attributes);
    }

    /**
     * Create a standalone element
     *
     * @param array<string, mixed>|null $attributes
     */
    public function el(
        string $name,
        mixed $content = null,
        ?array $attributes = null
    ): Element {
        return Element::create($name, $content, $attributes);
    }

    /**
     * Wrap raw html string
     */
    public function raw(
        mixed $html
    ): Buffer {
        return new Buffer(Coercion::toString($html));
    }

    /**
     * Normalize arbitrary content
     */
    public function wrap(
        mixed ...$content
    ): Buffer {
        return ContentCollection::normalize($content);
    }

    /**
     * Wrap arbitrary content as collection
     */
    public function content(
        mixed ...$content
    ): ContentCollection {
        return new ContentCollection($content);
    }




    /**
     * Generate nested list
     *
     * @param iterable<mixed>|Closure():(iterable<mixed>)|null $list
     * @param array<string, mixed>|null $attributes
     */
    public function list(
        iterable|Closure|null $list,
        string $container,
        ?string $name,
        ?callable $callback = null,
        ?array $attributes = null
    ): Element {
        $output = Element::create($container, function () use ($list, $name, $callback) {
            if($list instanceof Closure) {
                $list = $list();
            }

            if (!is_iterable($list)) {
                return;
            }

            $i = 0;

            foreach ($list as $key => $item) {
                $i++;

                if ($name === null) {
                    // Unwrapped
                    if ($callback) {
                        yield $callback($item, null, $key, $i);
                    } else {
                        yield $item;
                    }
                } else {
                    // Wrapped
                    yield Element::create($name, function ($el) use ($key, $item, $callback, $i) {
                        if ($callback) {
                            return $callback($item, $el, $key, $i);
                        } else {
                            return $item;
                        }
                    });
                }
            }
        }, $attributes);

        $output->setRenderEmpty(false);
        return $output;
    }


    /**
     * Generate naked list
     *
     * @param iterable<mixed>|Closure():(iterable<mixed>)|null $list
     * @param array<string, mixed>|null $attributes
     */
    public function elements(
        iterable|Closure|null $list,
        ?string $name,
        ?callable $callback = null,
        ?array $attributes = null
    ): Buffer {
        return ContentCollection::normalize(function () use ($list, $name, $callback, $attributes) {
            if($list instanceof Closure) {
                $list = $list();
            }

            if (!is_iterable($list)) {
                return;
            }

            $i = 0;

            foreach ($list as $key => $item) {
                $i++;

                if ($name === null) {
                    // Unwrapped
                    if ($callback) {
                        yield $callback($item, null, $key, $i);
                    } else {
                        yield $item;
                    }
                } else {
                    // Wrapped
                    yield Element::create($name, function ($el) use ($key, $item, $callback, $i) {
                        if ($callback) {
                            return $callback($item, $el, $key, $i);
                        } else {
                            return $item;
                        }
                    }, $attributes);
                }
            }
        });
    }


    /**
     * Generate unwrapped naked list
     *
     * @param iterable<mixed>|Closure():(iterable<mixed>)|null $list
     */
    public function loop(
        iterable|Closure|null $list,
        ?callable $callback = null
    ): Buffer {
        return ContentCollection::normalize(function () use ($list, $callback) {
            if($list instanceof Closure) {
                $list = $list();
            }

            if (!is_iterable($list)) {
                return;
            }

            $i = 0;

            foreach ($list as $key => $item) {
                $i++;

                if ($callback) {
                    yield $callback($item, null, $key, $i);
                } else {
                    yield $item;
                }
            }
        });
    }


    /**
     * Create a standard ul > li structure
     *
     * @param iterable<mixed>|Closure():(iterable<mixed>)|null $list
     * @param array<string, mixed>|null $attributes
     */
    public function uList(
        iterable|Closure|null $list,
        ?callable $renderer = null,
        ?array $attributes = null
    ): Element {
        return $this->list($list, 'ul', '?li', $renderer ?? function ($value) {
            return $value;
        }, $attributes);
    }

    /**
     * Create a standard ol > li structure
     *
     * @param iterable<mixed>|Closure():(iterable<mixed>)|null $list
     * @param array<string, mixed>|null $attributes
     */
    public function oList(
        iterable|Closure|null $list,
        ?callable $renderer = null,
        ?array $attributes = null
    ): Element {
        return $this->list($list, 'ol', '?li', $renderer ?? function ($value) {
            return $value;
        }, $attributes);
    }

    /**
     * Create a standard dl > dt + dd structure
     *
     * @param iterable<mixed>|Closure():(iterable<mixed>)|null $list
     * @param array<string,mixed>|null $attributes
     */
    public function dList(
        iterable|Closure|null $list,
        ?callable $renderer = null,
        ?array $attributes = null
    ): Element {
        $renderer = $renderer ?? function ($value) {
            return $value;
        };

        $output = Element::create('dl', function () use ($list, $renderer) {
            if($list instanceof Closure) {
                $list = $list();
            }

            if (!is_iterable($list)) {
                return;
            }

            foreach ($list as $key => $item) {
                $dt = Element::create('dt', null);

                // Render dd tag before dt so that renderer can add contents to dt first
                $dd = (string)Element::create('dd', function ($dd) use ($key, $item, $renderer, &$i, $dt) {
                    return $renderer($item, $dt, $dd, $key, ++$i);
                });

                if ($dt->isEmpty()) {
                    $dt->append($key);
                }

                yield $dt;
                yield new Buffer($dd);
            }
        }, $attributes);

        $output->setRenderEmpty(false);
        return $output;
    }

    /**
     * Create an inline comma separated list with optional item limit
     *
     * @param iterable<mixed>|Closure():(iterable<mixed>)|null $list
     */
    public function iList(
        iterable|Closure|null $list,
        ?callable $renderer = null,
        ?string $delimiter = null,
        ?string $finalDelimiter = null,
        ?int $limit = null
    ): Element {
        if ($delimiter === null) {
            $delimiter = ', ';
        }

        return Element::create('span.list', function (
            Element $el
        ) use ($list, $renderer, $delimiter, $finalDelimiter, $limit) {
            $el->setRenderEmpty(false);

            if($list instanceof Closure) {
                $list = $list();
            }

            if (!is_iterable($list)) {
                return;
            }

            $first = true;
            $i = $more = 0;

            if ($finalDelimiter === null) {
                $finalDelimiter = $delimiter;
            }

            $items = [];

            foreach ($list as $key => $item) {
                if ($item === null) {
                    continue;
                }

                $i++;

                $cellTag = Element::create('?span', function ($el) use ($key, $item, $renderer, &$i) {
                    if ($renderer) {
                        return $renderer($item, $el, $key, $i);
                    } else {
                        return $item;
                    }
                });

                if (empty($tagString = (string)$cellTag)) {
                    $i--;
                    continue;
                }

                if (
                    $limit !== null &&
                    $i > $limit
                ) {
                    $more++;
                    continue;
                }


                $items[] = new Buffer($tagString);
            }

            $total = count($items);

            foreach ($items as $i => $item) {
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
        });
    }




    /**
     * Create image tag
     */
    public function image(
        string|Stringable|null $url,
        ?string $alt = null,
        string|int|null $width = null,
        string|int|null $height = null
    ): Element {
        $output = $this->el('img', null, [
            'src' => (string)$url,
            'alt' => $alt
        ]);

        if ($width !== null) {
            $output->setAttribute('width', $width);
        }

        if ($height !== null) {
            $output->setAttribute('height', $height);
        }

        return $output;
    }



    /**
     * Escape HTML
     */
    public function esc(
        mixed $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        try {
            return htmlspecialchars(Coercion::toString($value), ENT_QUOTES, 'UTF-8');
        } catch (Throwable $e) {
            Glitch::logException($e);
            return Coercion::toString($value);
        }
    }

    /**
     * Serialize to json
     */
    public function jsonSerialize(): mixed
    {
        return (string)$this;
    }
}


// Register the Veneer proxy
Veneer\Manager::getGlobalManager()->register(
    Factory::class,
    Tagged::class
);
