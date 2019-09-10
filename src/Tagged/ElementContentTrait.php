<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged;

use DecodeLabs\Collections\Native\SequenceTrait;

trait ElementContentTrait
{
    use SequenceTrait;

    /**
     * Render inner content
     */
    public function renderContent(): Markup
    {
        $output = '';

        foreach ($this->items as $value) {
            if (empty($value) && $value != '0') {
                continue;
            }

            $output .= $this->renderChild($value);
        }

        return new Buffer($output);
    }

    abstract protected function renderChild($value): string;
}
