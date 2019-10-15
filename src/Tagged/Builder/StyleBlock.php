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

use DecodeLabs\Glitch;
use DecodeLabs\Glitch\Inspectable;
use DecodeLabs\Glitch\Dumper\Entity;
use DecodeLabs\Glitch\Dumper\Inspector;

class StyleBlock implements \IteratorAggregate, HashMap, Inspectable
{
    use HashMapTrait {
        HashMapTrait::set as private parentSet;
    }

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
    public function set(string $key, $value): HashMap
    {
        if (!$value instanceof StyleList) {
            $value = new StyleList($value);
        }

        return $this->parentSet($key, $value);
    }

    /**
     * Render to string
     */
    public function render(): ?string
    {
        if (empty($this->items)) {
            return '';
        }

        $output = [];

        foreach ($this->items as $selector => $styles) {
            $output[] = $selector.' { '.$styles.' }';
        }

        return '<style type="text/css">'."\n    ".implode("\n".'    ', $output)."\n".'</style>';
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
     * Inspect for Glitch
     */
    public function glitchInspect(Entity $entity, Inspector $inspector): void
    {
        $entity
            ->setText($this->render())
            ->setSectionVisible('text', false)
            ->setValues($inspector->inspectList($this->items));
    }
}
