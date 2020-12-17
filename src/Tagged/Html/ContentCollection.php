<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Html;

use DecodeLabs\Collections\Native\SequenceTrait;
use DecodeLabs\Collections\Sequence;
use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\Builder\ChildRendererTrait;
use DecodeLabs\Tagged\Markup;

class ContentCollection implements Markup, \IteratorAggregate, Sequence
{
    public const MUTABLE = true;

    use SequenceTrait;
    use ChildRendererTrait;

    /**
     * Normalize abitrary content
     */
    public static function normalize($content, bool $pretty = false): Buffer
    {
        if (!is_array($content)) {
            $content = [$content];
        }

        return (new self($content))->render($pretty);
    }

    /**
     * Flatten to string
     */
    public function __toString(): string
    {
        return (string)$this->render();
    }

    /**
     * Render contents
     */
    public function render(bool $pretty = false): Buffer
    {
        $output = '';

        foreach ($this->items as $value) {
            if (empty($value) && $value != '0') {
                continue;
            }

            $output .= $this->renderChild($value, $pretty);
        }

        return new Buffer($output);
    }
}
