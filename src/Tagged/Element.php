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
     * @param iterable<string,TAttributeInput> $attributes
     * @param TAttributeInput ...$attributeList
     */
    public static function create(
        string $tagName,
        mixed $content = null,
        iterable $attributes = [],
        mixed ...$attributeList
    ): self {
        /** @var array<string,TAttributeInput> $attributes */
        $attributes = array_merge(
            iterator_to_array($attributes),
            $attributeList
        );

        if (false !== strpos($tagName, '>')) {
            $parts = explode('>', $tagName);

            foreach (array_reverse($parts) as $tagName) {
                $content = new self(trim($tagName), $content, $attributes);
                $attributes = null;
            }

            return $content;
        }

        return new self($tagName, $content, $attributes);
    }
}
