<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged\Html;

use DecodeLabs\Tagged\Markup;
use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\Builder\ChildRendererTrait;

use DecodeLabs\Collections\Sequence;
use DecodeLabs\Collections\Native\SequenceTrait;

class ContentCollection implements Markup, \IteratorAggregate, Sequence
{
    use SequenceTrait;
    use ChildRendererTrait;

    /**
     * Normalize abitrary content
     */
    public static function normalize($content, bool $pretty=false): Markup
    {
        if (!is_array($content)) {
            $content = [$content];
        }

        return (new static($content))->render($pretty);
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
    public function render(bool $pretty=false): Markup
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
