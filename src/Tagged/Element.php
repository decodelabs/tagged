<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged;

use DecodeLabs\Elementary\Element as ElementInterface;
use DecodeLabs\Elementary\ElementTrait as ElementTrait;
use IteratorAggregate;

/**
 * @phpstan-import-type TAttributeValue from Tag
 * @phpstan-import-type TAttributeInput from Tag
 * @implements IteratorAggregate<mixed>
 * @implements ElementInterface<TAttributeValue,TAttributeInput,Buffer>
 */
class Element extends Tag implements
    IteratorAggregate,
    ElementInterface
{
    /**
     * @use ElementTrait<Buffer>
     */
    use ElementTrait;
    use RenderableTrait;

    /**
     * Apply nested by string name
     *
     * @param array<string,mixed>|null $attributes
     */
    public static function create(
        string $name,
        mixed $content = null,
        ?array $attributes = null
    ): self {
        if (false !== strpos($name, '>')) {
            $parts = explode('>', $name);

            foreach (array_reverse($parts) as $name) {
                $content = new self(trim($name), $content, $attributes);
                $attributes = null;
            }

            return $content;
        }

        return new self($name, $content, $attributes);
    }
}
