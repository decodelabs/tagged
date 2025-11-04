<?php

/**
 * Tagged
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged;

trait BufferProviderTrait
{
    protected function newBuffer(
        ?string $content
    ): Buffer {
        return new Buffer($content);
    }
}
