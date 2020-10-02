<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged\Builder;

use DecodeLabs\Collections\ArrayProvider;

use DecodeLabs\Exceptional;
use DecodeLabs\Glitch\Dumpable;

use ArrayIterator;

class StyleBlock implements \IteratorAggregate, Dumpable
{
    const MUTABLE = true;

    protected $styles = [];

    /**
     * Init with styles
     */
    public function __construct(...$input)
    {
        $this->import(...$input);
    }

    /**
     * Import style data
     */
    public function import(...$input): StyleBlock
    {
        foreach ($input as $data) {
            if (is_string($data)) {
                $data = $this->parse($data);
            } elseif ($data instanceof ArrayProvider) {
                $data = $data->toArray();
            } elseif (is_iterable($data) && !is_array($data)) {
                $data = iterator_to_array($data);
            } elseif ($data === null) {
                continue;
            } elseif (!is_array($data)) {
                throw Exceptional::InvalidArgument('Invalid style data', null, $data);
            }

            foreach ($data as $key => $value) {
                $this->set($key, $value);
            }
        }

        return $this;
    }

    /**
     * Parse string styles
     */
    protected function parse(string $style): array
    {
        $parts = explode('{', $style);
        $output = [];

        while (!empty($parts)) {
            $selector = trim((string)array_shift($parts));
            $body = explode('}', (string)array_shift($parts), 2);
            $nextSelector = trim((string)array_pop($body));
            $body = trim((string)array_shift($body));

            if (!empty($nextSelector)) {
                array_unshift($parts, $nextSelector);
            }

            $output[$selector] = new StyleList($body);
        }

        return $output;
    }

    /**
     * Direct set a value
     */
    public function set(string $key, $value): StyleBlock
    {
        if (!$value instanceof StyleList) {
            $value = new StyleList($value);
        }

        $this->styles[$key] = $value;
        return $this;
    }

    /**
     * Get a style list
     */
    public function get(string $key): ?StyleList
    {
        return $this->styles[$key] ?? null;
    }

    /**
     * Has style list set?
     */
    public function has(string $key): bool
    {
        return isset($this->styles[$key]);
    }

    /**
     * Remove style list
     */
    public function remove(string $key): StyleBlock
    {
        unset($this->styles[$key]);
        return $this;
    }

    /**
     * Render to string
     */
    public function render(): ?string
    {
        if (null === ($styles = $this->renderStyles())) {
            return null;
        }

        return '<style type="text/css">'."\n    ".$styles."\n".'</style>';
    }

    /**
     * Render styles blocks
     */
    public function renderStyles(): ?string
    {
        if (empty($this->styles)) {
            return null;
        }

        $output = [];

        foreach ($this->styles as $selector => $styles) {
            $output[] = $selector.' { '.$styles.' }';
        }

        return implode("\n".'    ', $output);
    }

    /**
     * Convert to string
     */
    public function __toString(): string
    {
        try {
            return (string)$this->render();
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Get iterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->styles);
    }

    /**
     * Export for dump inspection
     */
    public function glitchDump(): iterable
    {
        yield 'definition' => $this->render();
        yield 'section:definition' => false;
        yield 'values' => $this->styles;
    }
}
