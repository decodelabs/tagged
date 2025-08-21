<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged;

use DateInterval;
use DateTimeInterface;
use DateTimeZone;
use DecodeLabs\Cosmos\Extension\Time as TimeExtension;
use DecodeLabs\Cosmos\Extension\TimeTrait as TimeExtensionTrait;
use DecodeLabs\Cosmos\Locale;
use Stringable;

/**
 * @implements TimeExtension<Element>
 */
class Time implements TimeExtension
{
    /**
     * @use TimeExtensionTrait<Element>
     */
    use TimeExtensionTrait;

    public static function format(
        DateTimeInterface|DateInterval|string|Stringable|int|null $date,
        string $format,
        DateTimeZone|string|Stringable|bool|null $timezone = true
    ): ?Element {
        if (!$date = static::prepare($date, $timezone, true)) {
            return null;
        }

        return static::wrap(
            $date->format($timezone === false ? 'Y-m-d' : DateTimeInterface::W3C),
            $date->format($format)
        );
    }

    public static function formatDate(
        DateTimeInterface|DateInterval|string|Stringable|int|null $date,
        string $format
    ): ?Element {
        if (!$date = static::prepare($date, false, true)) {
            return null;
        }

        return static::wrap(
            $date->format('Y-m-d'),
            $date->format($format)
        );
    }

    public static function pattern(
        DateTimeInterface|DateInterval|string|Stringable|int|null $date,
        string $pattern,
        DateTimeZone|string|Stringable|bool|null $timezone = true,
        string|Locale|null $locale = null
    ): ?Element {
        $output = static::formatRawIcuDate($date, $pattern, $timezone, $locale);

        if ($output === null) {
            return null;
        }

        return static::wrap(
            (string)$date?->format($timezone === false ? 'Y-m-d' : DateTimeInterface::W3C),
            $output
        );
    }

    public static function locale(
        DateTimeInterface|DateInterval|string|Stringable|int|null $date,
        string|int|bool|null $dateSize = true,
        string|int|bool|null $timeSize = true,
        DateTimeZone|string|Stringable|bool|null $timezone = true,
        string|Locale|null $locale = null
    ): ?Element {
        $output = static::formatRawLocaleDate($date, $dateSize, $timeSize, $timezone, $locale, $wrapFormat);

        if ($output === null) {
            return null;
        }

        return static::wrap(
            (string)$date?->format((string)$wrapFormat),
            $output
        );
    }



    protected static function formatNowInterval(
        DateTimeInterface|DateInterval|string|Stringable|int|null $date,
        bool $invert,
        ?int $parts,
        bool $short = false,
        bool $absolute = false,
        ?bool $positive = false,
        string|Locale|null $locale = null
    ): ?Element {
        $output = static::formatRawNowInterval($date, $interval, $invert, $parts, $short, $absolute, $positive, $locale);

        if ($output === null) {
            return null;
        }

        $output = static::wrap(
            (string)$date?->format(DateTimeInterface::W3C),
            $output,
            static::formatRawLocaleDate($date, true, true, true, $locale)
        );

        if ($interval?->invert) {
            $output->addClass('future');
        } else {
            $output->addClass('past');
        }

        if ($positive !== null) {
            $positiveClass = $positive ? 'positive' : 'negative';
            $negativeClass = $positive ? 'negative' : 'positive';

            if ($interval?->invert) {
                $output->addClass($invert ? $positiveClass : $negativeClass . ' pending');
            } else {
                $output->addClass($invert ? $negativeClass : $positiveClass);
            }
        }

        return $output;
    }


    protected static function formatBetweenInterval(
        DateTimeInterface|DateInterval|string|Stringable|int|null $date1,
        DateTimeInterface|DateInterval|string|Stringable|int|null $date2,
        ?int $parts = 1,
        bool $short = false,
        string|Locale|null $locale = null
    ): ?Element {
        $output = static::formatRawBetweenInterval($date1, $date2, $interval, $parts, $short, $locale);

        if ($output === null) {
            return null;
        }

        $output = new Element(
            'span.interval',
            $output
        );

        if ($interval?->invert) {
            $output->addClass('negative');
        } else {
            $output->addClass('positive');
        }

        return $output;
    }





    protected static function wrap(
        string $w3c,
        string $formatted,
        ?string $title = null
    ): Element {
        $output = new Element('time', $formatted, [
            'datetime' => $w3c
        ]);

        if ($title !== null) {
            $output->setTitle($title);
        }

        return $output;
    }
}
