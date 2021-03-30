<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged;

use DecodeLabs\Elementary\Buffer as BufferInterface;
use DecodeLabs\Elementary\BufferTrait;
use DecodeLabs\Glitch\Dumpable;

class Buffer implements BufferInterface, Markup, Dumpable
{
    use BufferTrait;
}
