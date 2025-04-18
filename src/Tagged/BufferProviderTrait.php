<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged;

trait BufferProviderTrait
{
    /**
     * Create new buffer
     */
    protected function newBuffer(
        ?string $content
    ): Buffer {
        return new Buffer($content);
    }
}
