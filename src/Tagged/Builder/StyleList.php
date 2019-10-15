<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged\Builder;

use DecodeLabs\Collections\HashMap;
use DecodeLabs\Collections\ArrayProvider;
use DecodeLabs\Collections\Native\HashMapTrait;

class StyleList implements \IteratorAggregate, HashMap
{
    use HashMapTrait;

    const MUTABLE = true;

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
    public function import(...$input): StyleList
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
                throw Glitch::EInvalidArgument('Invalid style data', null, $data);
            }

            $this->merge($data);
        }

        return $this;
    }

    /**
     * Parse string styles
     */
    protected function parse(string $style): array
    {
        $parts = explode(';', $style);
        $output = [];

        foreach ($parts as $part) {
            $part = trim($part);

            if (empty($part)) {
                continue;
            }

            $exp = explode(':', $part);

            if (count($exp) == 2) {
                $output[trim((string)array_shift($exp))] = trim((string)array_shift($exp));
            }
        }

        return $output;
    }

    /**
     * Render to string
     */
    public function __toString(): string
    {
        $output = [];

        foreach ($this->items as $key => $value) {
            $output[] = $key.': '.$value.';';
        }

        return implode(' ', $output);
    }
}
