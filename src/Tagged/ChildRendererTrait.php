<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged;

trait ChildRendererTrait
{
    /**
     * Convert child element to string
     */
    protected function renderChild($value): string
    {
        $output = '';

        if (is_callable($value) && is_object($value)) {
            return $this->renderChild($value($this));
        }

        if (is_iterable($value) && !$value instanceof Markup) {
            foreach ($value as $part) {
                $output .= $this->renderChild($part);
            }

            return $output;
        }

        $output = (string)$value;

        if (!$value instanceof Markup) {
            $output = $this->esc($output);
        }

        return $output;
    }
}
