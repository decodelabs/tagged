<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged;

use DecodeLabs\Elementary\Markup as RootMarkup;

interface Markup extends RootMarkup
{
    public function __toString(): string;
}
