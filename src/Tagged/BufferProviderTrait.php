<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged;

use DecodeLabs\Elementary\Buffer as BufferInterface;

trait BufferProviderTrait
{
    /**
     * Create new buffer
     */
    protected function newBuffer(?string $content): BufferInterface
    {
        return new Buffer($content);
    }
}
