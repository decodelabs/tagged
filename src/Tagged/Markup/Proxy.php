<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Markup;

use DecodeLabs\Elementary\Markup\Proxy as RootProxy;
use DecodeLabs\Tagged\Markup;

interface Proxy extends RootProxy
{
    public function toMarkup(): Markup;
}
