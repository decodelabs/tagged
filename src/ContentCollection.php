<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged;

use DecodeLabs\Collections\Native\SequenceTrait;
use DecodeLabs\Collections\Sequence;
use DecodeLabs\Elementary\Markup\ChildRendererTrait;

use IteratorAggregate;

/**
 * @implements IteratorAggregate<mixed>
 * @implements Sequence<mixed>
 */
class ContentCollection implements
    Markup,
    IteratorAggregate,
    Sequence
{
    /**
     * @use SequenceTrait<mixed>
     */
    use SequenceTrait;
    use ChildRendererTrait;
    use BufferProviderTrait;

    protected const Mutable = true;

    /**
     * Normalize abitrary content
     */
    public static function normalize(
        mixed $content,
        bool $pretty = false
    ): Buffer {
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
    public function render(
        bool $pretty = false
    ): Buffer {
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
