<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged;

use DecodeLabs\Tagged\Markup;

interface MarkupProxy
{
    public function toMarkup(): Markup;
}
