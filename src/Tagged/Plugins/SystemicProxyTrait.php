<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Plugins;

use DateTimeZone;
use DecodeLabs\Systemic;
use Locale as SysLocale;

trait SystemicProxyTrait
{
    /**
     * Get system locale
     */
    protected function getLocale(): string
    {
        if (class_exists(Systemic::class)) {
            return (string)Systemic::$locale->get();
        }

        return (string)SysLocale::getDefault();
    }

    /**
     * Get system timezone
     */
    protected function getTimezone(): DateTimeZone
    {
        if (class_exists(Systemic::class)) {
            return Systemic::$timezone->get();
        }

        return new DateTimeZone('UTC');
    }
}
