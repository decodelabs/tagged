<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged;

use DecodeLabs\Collections\AttributeContainerTrait;

/**
 * @phpstan-require-implements Asset
 */
trait AssetTrait
{
    use AttributeContainerTrait;

    protected int $priority = 10;

    /**
     * Set priority
     */
    public function setPriority(
        int $priority
    ): void {
        $this->priority = $priority;
    }

    /**
     * Get priority
     */
    public function getPriority(): int
    {
        return $this->priority;
    }
}
