<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged;

use DecodeLabs\Elementary\Markup\Proxy as RootProxy;

interface MarkupProxy extends RootProxy
{
    public function toMarkup(): Markup;
}
