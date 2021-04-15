<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Plugins;

use DateInterval;
use DateTime;

use DecodeLabs\Dictum\Plugin\Time as TimePlugin;
use DecodeLabs\Dictum\Plugin\TimeTrait as TimePluginTrait;
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

    /**
     * @var Factory
     */
    protected $html;

    /**
     * Init with parent factory
     */
    public function __construct(Factory $html)
    {
        $this->html = $html;
    }

    /**
     * Custom format a date and wrap it
     */
    public function format($date, string $format, $timezone = true): ?Element
    {
        if (!$date = $this->prepare($date, $timezone, true)) {
            return null;
        }

        return $this->wrap(
            $date->format($timezone === false ? 'Y-m-d' : DateTime::W3C),
            $date->format($format)
        );
    }

    /**
     * Custom format a date and wrap it
     */
    public function formatDate($date, string $format): ?Element
    {
        if (!$date = $this->prepare($date, false, true)) {
            return null;
        }

        return $this->wrap(
            $date->format('Y-m-d'),
            $date->format($format)
        );
    }

    /**
     * Format date according to locale
     */
    public function locale($date, $dateSize = true, $timeSize = true, $timezone = true, ?string $locale = null): ?Element
    {
        $output = $this->formatRawLocaleDate($date, $dateSize, $timeSize, $timezone, $locale, $wrapFormat);

        if ($output === null) {
            return null;
        }

        return $this->wrap(
            $date->format($wrapFormat),
            $output
        );
    }



    /**
     * Format interval
     * @param DateTime|DateInterval|string|Stringable|int|null $date
     */
    protected function formatNowInterval($date, bool $invert, ?int $parts, bool $short = false, bool $absolute = false, ?bool $positive = false, ?string $locale = null): ?Element
    {
        $output = $this->formatRawNowInterval($date, $interval, $invert, $parts, $short, $absolute, $positive, $locale);

        if ($output === null) {
            return null;
        }

        $output = $this->wrap(
            $date->format(DateTime::W3C),
            $output,
            $this->formatRawLocaleDate($date, true, true, true, $locale)
        );

        if ($interval->invert) {
            $output->addClass('future');
        } else {
            $output->addClass('past');
        }

        if ($positive !== null) {
            $positiveClass = $positive ? 'positive' : 'negative';
            $negativeClass = $positive ? 'negative' : 'positive';

            if ($interval->invert) {
                $output->addClass($invert ? $positiveClass : $negativeClass . ' pending');
            } else {
                $output->addClass($invert ? $negativeClass : $positiveClass);
            }
        }

        return $output;
    }


    /**
     * Format interval until date
     *
     * @param DateTime|DateInterval|string|Stringable|int|null $date1
     * @param DateTime|DateInterval|string|Stringable|int|null $date2
     */
    protected function formatBetweenInterval($date1, $date2, ?int $parts = 1, bool $short = false, ?string $locale = null): ?Element
    {
        $output = $this->formatRawBetweenInterval($date1, $date2, $interval, $parts, $short, $locale);

        if ($output === null) {
            return null;
        }

        $output = $this->html->el(
            'span.interval',
            $output
        );

        if ($interval->invert) {
            $output->addClass('negative');
        } else {
            $output->addClass('positive');
        }

        return $output;
    }





    /**
     * Wrap date / time in Markup
     */
    protected function wrap(string $w3c, string $formatted, ?string $title = null): Element
    {
        $output = $this->html->el('time', $formatted, [
            'datetime' => $w3c
        ]);

        if ($title !== null) {
            $output->setTitle($title);
        }

        return $output;
    }
}
