<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged;

use DecodeLabs\Tagged\Markup;

/**
 * @template T of Markup
 */
class PriorityMarkup implements Markup
{
    public int $priority = 0;

    /**
     * @var T
     */
    public Markup $markup;

    /**
     * @param T $markup
     */
    public function __construct(
        Markup $markup,
        int $priority = 0
    ) {
        $this->markup = $markup;
        $this->priority = $priority;
    }

    public function __toString(): string
    {
        return (string)$this->markup;
    }

    public function jsonSerialize(): mixed
    {
        return $this->markup->jsonSerialize();
    }
}
