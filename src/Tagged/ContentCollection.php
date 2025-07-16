<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged;

use ArrayAccess;
use DecodeLabs\Collections\SequenceInterface;
use DecodeLabs\Collections\SequenceTrait;
use DecodeLabs\Elementary\ChildRendererTrait;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<mixed>
 * @implements SequenceInterface<mixed>
 * @implements ArrayAccess<int,mixed>
 */
class ContentCollection implements
    Markup,
    IteratorAggregate,
    SequenceInterface,
    ArrayAccess
{
    /**
     * @use SequenceTrait<mixed>
     */
    use SequenceTrait;

    /**
     * @use ChildRendererTrait<Buffer>
     */
    use ChildRendererTrait;
    use BufferProviderTrait;

    protected const bool Mutable = true;

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
            if (
                empty($value) &&
                $value != '0'
            ) {
                continue;
            }

            $output .= $this->renderChild($value, $pretty);
        }

        return new Buffer($output);
    }
}
