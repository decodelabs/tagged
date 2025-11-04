<?php

/**
 * Tagged
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged;

use DecodeLabs\Elementary\MarkupProvider as RootProvider;

interface MarkupProvider extends RootProvider
{
    public function toMarkup(): Markup;
}
