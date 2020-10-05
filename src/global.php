<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);

/**
 * global helpers
 */
namespace DecodeLabs\Tagged
{
    use DecodeLabs\Tagged\Html;
    use DecodeLabs\Tagged\Html\Factory as HtmlFactory;
    use DecodeLabs\Veneer;

    // Register the Veneer proxy
    Veneer::register(HtmlFactory::class, Html::class);
}
