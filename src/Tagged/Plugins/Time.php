<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Plugins;

use DateInterval;
use DateTimeInterface;
use DateTimeZone;
use DecodeLabs\Cosmos\Extension\Time as TimePlugin;
use DecodeLabs\Cosmos\Extension\TimeTrait as TimePluginTrait;
use DecodeLabs\Cosmos\Locale;
use DecodeLabs\Tagged\Element;
use DecodeLabs\Tagged\Factory;
use Stringable;

/**
 * @implements TimePlugin<Element>
 */
class Time implements TimePlugin
{
    /**
     * @use TimePluginTrait<Element>
     */
    use TimePluginTrait;

    protected Factory $html;

    /**
     * Init with parent factory
     */
    public function __construct(
        Factory $html
    ) {
        $this->html = $html;
    }

    /**
     * Custom format a date and wrap it
     */
    public function format(
        DateTimeInterface|DateInterval|string|Stringable|int|null $date,
        string $format,
        DateTimeZone|string|Stringable|bool|null $timezone = true
    ): ?Element {
        if (!$date = $this->prepare($date, $timezone, true)) {
            return null;
        }

        return $this->wrap(
            $date->format($timezone === false ? 'Y-m-d' : DateTimeInterface::W3C),
            $date->format($format)
        );
    }

    /**
     * Custom format a date and wrap it
     */
    public function formatDate(
        DateTimeInterface|DateInterval|string|Stringable|int|null $date,
        string $format
    ): ?Element {
        if (!$date = $this->prepare($date, false, true)) {
            return null;
        }

        return $this->wrap(
            $date->format('Y-m-d'),
            $date->format($format)
        );
    }

    /**
     * Custom locale format a date with ICU and wrap it
     */
    public function pattern(
        DateTimeInterface|DateInterval|string|Stringable|int|null $date,
        string $pattern,
        DateTimeZone|string|Stringable|bool|null $timezone = true,
        string|Locale|null $locale = null
    ): ?Element {
        $output = $this->formatRawIcuDate($date, $pattern, $timezone, $locale);

        if ($output === null) {
            return null;
        }

        return $this->wrap(
            (string)$date?->format($timezone === false ? 'Y-m-d' : DateTimeInterface::W3C),
            $output
        );
    }

    /**
     * Format date according to locale
     */
    public function locale(
        DateTimeInterface|DateInterval|string|Stringable|int|null $date,
        string|int|bool|null $dateSize = true,
        string|int|bool|null $timeSize = true,
        DateTimeZone|string|Stringable|bool|null $timezone = true,
        string|Locale|null $locale = null
    ): ?Element {
        $output = $this->formatRawLocaleDate($date, $dateSize, $timeSize, $timezone, $locale, $wrapFormat);

        if ($output === null) {
            return null;
        }

        return $this->wrap(
            (string)$date?->format((string)$wrapFormat),
            $output
        );
    }



    /**
     * Format interval
     */
    protected function formatNowInterval(
        DateTimeInterface|DateInterval|string|Stringable|int|null $date,
        bool $invert,
        ?int $parts,
        bool $short = false,
        bool $absolute = false,
        ?bool $positive = false,
        string|Locale|null $locale = null
    ): ?Element {
        $output = $this->formatRawNowInterval($date, $interval, $invert, $parts, $short, $absolute, $positive, $locale);

        if ($output === null) {
            return null;
        }

        $output = $this->wrap(
            (string)$date?->format(DateTimeInterface::W3C),
            $output,
            $this->formatRawLocaleDate($date, true, true, true, $locale)
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


    /**
     * Format interval until date
     */
    protected function formatBetweenInterval(
        DateTimeInterface|DateInterval|string|Stringable|int|null $date1,
        DateTimeInterface|DateInterval|string|Stringable|int|null $date2,
        ?int $parts = 1,
        bool $short = false,
        string|Locale|null $locale = null
    ): ?Element {
        $output = $this->formatRawBetweenInterval($date1, $date2, $interval, $parts, $short, $locale);

        if ($output === null) {
            return null;
        }

        $output = $this->html->el(
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





    /**
     * Wrap date / time in Markup
     */
    protected function wrap(
        string $w3c,
        string $formatted,
        ?string $title = null
    ): Element {
        $output = $this->html->el('time', $formatted, [
            'datetime' => $w3c
        ]);

        if ($title !== null) {
            $output->setTitle($title);
        }

        return $output;
    }
}
