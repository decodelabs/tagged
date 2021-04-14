<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Plugins;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonInterval;

use DateInterval;
use DateTime;

use DecodeLabs\Dictum\Plugin\Time as TimePlugin;
use DecodeLabs\Dictum\Plugin\TimeTrait as TimePluginTrait;
use DecodeLabs\Exceptional;
use DecodeLabs\Tagged\Element;
use DecodeLabs\Tagged\Factory;

use IntlDateFormatter;
use Stringable;

/**
 * @implements TimePlugin<Element>
 */
class Time implements TimePlugin
{
    use SystemicProxyTrait;

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
        $dateSize = $this->normalizeLocaleSize($dateSize);
        $timeSize = $this->normalizeLocaleSize($timeSize);

        $hasDate = $dateSize !== IntlDateFormatter::NONE;
        $hasTime = ($timeSize !== IntlDateFormatter::NONE) && ($timezone !== false);

        if (!$hasDate && !$hasTime) {
            return null;
        }

        if ($hasDate && $hasTime) {
            $format = DateTime::W3C;
        } elseif ($hasDate) {
            $format = 'Y-m-d';
        } elseif ($hasTime) {
            $format = 'H:i:s';
        } else {
            $format = '';
        }

        if (!$date = $this->prepare($date, $timezone, $hasTime)) {
            return null;
        }

        $formatter = new IntlDateFormatter(
            $this->getLocale($locale),
            $dateSize,
            $timeSize
        );

        $formatter->setTimezone($date->getTimezone());

        return $this->wrap(
            $date->format($format),
            $formatter->format($date)
        );
    }



    /**
     * Format interval since date
     */
    public function since($date, ?bool $positive = null, ?int $parts = 1, ?string $locale = null): ?Element
    {
        return $this->wrapInterval($date, false, $parts, false, false, $positive, $locale);
    }

    /**
     * Format interval since date
     */
    public function sinceAbs($date, ?bool $positive = null, ?int $parts = 1, ?string $locale = null): ?Element
    {
        return $this->wrapInterval($date, false, $parts, false, true, $positive, $locale);
    }

    /**
     * Format interval since date
     */
    public function sinceAbbr($date, ?bool $positive = null, ?int $parts = 1, ?string $locale = null): ?Element
    {
        return $this->wrapInterval($date, false, $parts, true, true, $positive, $locale);
    }

    /**
     * Format interval until date
     */
    public function until($date, ?bool $positive = null, ?int $parts = 1, ?string $locale = null): ?Element
    {
        return $this->wrapInterval($date, true, $parts, false, false, $positive, $locale);
    }

    /**
     * Format interval until date
     */
    public function untilAbs($date, ?bool $positive = null, ?int $parts = 1, ?string $locale = null): ?Element
    {
        return $this->wrapInterval($date, true, $parts, false, true, $positive, $locale);
    }

    /**
     * Format interval until date
     */
    public function untilAbbr($date, ?bool $positive = null, ?int $parts = 1, ?string $locale = null): ?Element
    {
        return $this->wrapInterval($date, true, $parts, true, true, $positive, $locale);
    }


    /**
     * Format interval
     * @param DateTime|DateInterval|string|Stringable|int|null $date
     */
    protected function wrapInterval($date, bool $invert, ?int $parts, bool $short = false, bool $absolute = false, ?bool $positive = false, ?string $locale = null): ?Element
    {
        $this->checkCarbon();

        if (!$date = $this->normalizeDate($date)) {
            return null;
        }

        if (null === ($now = $this->normalizeDate('now'))) {
            throw Exceptional::UnexpectedValue('Unable to create now date');
        }

        if (null === ($interval = CarbonInterval::make($date->diff($now)))) {
            throw Exceptional::UnexpectedValue('Unable to create interval');
        }

        $formatter = new IntlDateFormatter(
            $this->getLocale($locale),
            IntlDateFormatter::LONG,
            IntlDateFormatter::LONG
        );

        if (null === ($interval = CarbonInterval::make($interval))) {
            throw Exceptional::UnexpectedValue('Unable to create interval');
        }

        $inverted = $interval->invert;

        if ($invert) {
            if ($inverted) {
                $absolute = true;
            }

            $inverted = !$inverted;
        }

        $output = $this->wrap(
            $date->format(DateTime::W3C),
            ($inverted && $absolute ? '-' : '') .
            $interval->forHumans([
                'short' => $short,
                'join' => true,
                'parts' => $parts,
                'options' => CarbonInterface::JUST_NOW | CarbonInterface::ONE_DAY_WORDS,
                'syntax' => $absolute ? CarbonInterface::DIFF_ABSOLUTE : CarbonInterface::DIFF_RELATIVE_TO_NOW
            ]),
            $formatter->format($date)
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
     */
    public function between($date1, $date2, ?int $parts = 1, ?string $locale = null): ?Element
    {
        return $this->betweenRaw($date1, $date2, $parts, false, $locale);
    }

    /**
     * Format interval until date
     */
    public function betweenAbbr($date1, $date2, ?int $parts = 1, ?string $locale = null): ?Element
    {
        return $this->betweenRaw($date1, $date2, $parts, true, $locale);
    }

    /**
     * Format interval until date
     *
     * @param DateTime|DateInterval|string|Stringable|int|null $date1
     * @param DateTime|DateInterval|string|Stringable|int|null $date2
     */
    protected function betweenRaw($date1, $date2, ?int $parts = 1, bool $short = false, ?string $locale = null): ?Element
    {
        $this->checkCarbon();

        if (!$date1 = $this->normalizeDate($date1)) {
            return null;
        }

        if (!$date2 = $this->normalizeDate($date2)) {
            return null;
        }

        if (null === ($interval = CarbonInterval::make($date1->diff($date2)))) {
            throw Exceptional::UnexpectedValue('Unable to create interval');
        }

        $interval->locale($this->getLocale($locale));

        $output = $this->html->el(
            'span.interval',
            ($interval->invert ? '-' : '') .
            $interval->forHumans([
                'short' => $short,
                'join' => true,
                'parts' => $parts,
                'options' => CarbonInterface::JUST_NOW | CarbonInterface::ONE_DAY_WORDS,
                'syntax' => CarbonInterface::DIFF_ABSOLUTE
            ])
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

    /**
     * Check Carbon installed
     */
    protected function checkCarbon(): void
    {
        if (!class_exists(Carbon::class)) {
            throw Exceptional::ComponentUnavailable(
                'nesbot/carbon is required for formatting intervals'
            );
        }
    }
}
