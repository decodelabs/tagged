<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged\FactoryPlugins;

use DecodeLabs\Veneer\FacadePlugin;

use DecodeLabs\Tagged\Markup;
use DecodeLabs\Tagged\HtmlFactory;
use DecodeLabs\Tagged\Buffer;

use DecodeLabs\Tagged\Builder\Html\ContentCollection;
use DecodeLabs\Tagged\Builder\Html\Element;

use DateTime;
use DateInterval;
use DateTimeZone;
use IntlDateFormatter;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonInterface;

class Time implements FacadePlugin
{
    protected $html;

    /**
     * Init with parent factory
     */
    public function __construct(HtmlFactory $html)
    {
        $this->html = $html;
    }

    /**
     * Custom format a date and wrap it
     */
    public function format($date, string $format, $timezone=true): ?Markup
    {
        if (!$date = $this->prepare($date, $timezone, true)) {
            return null;
        }

        return $this->wrap(
            $date->format($timezone === false ? 'Y-m-d' : \DateTime::W3C),
            $date->format($format)
        );
    }

    /**
     * Format date according to locale
     */
    public function locale($date, $dateSize=true, $timeSize=true, $timezone=true): ?Markup
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
        }

        if (!$date = $this->prepare($date, $timezone, $hasTime)) {
            return null;
        }

        $formatter = new IntlDateFormatter(
            Systemic::$locale->get(),
            $dateSize,
            $timeSize
        );

        return $this->wrap(
            $date->format($format),
            $formatter->format($date)
        );
    }

    /**
     * Format full date time
     */
    public function fullDateTime($date, $timezone=true): ?Markup
    {
        return $this->locale($date, 'full', 'full', $timezone);
    }

    /**
     * Format full date
     */
    public function fullDate($date, $timezone=true): ?Markup
    {
        return $this->locale($date, 'full', false, $timezone);
    }

    /**
     * Format full time
     */
    public function fullTime($date, $timezone=true): ?Markup
    {
        return $this->locale($date, false, 'full', $timezone);
    }


    /**
     * Format long date time
     */
    public function longDateTime($date, $timezone=true): ?Markup
    {
        return $this->locale($date, 'long', 'long', $timezone);
    }

    /**
     * Format long date
     */
    public function longDate($date, $timezone=true): ?Markup
    {
        return $this->locale($date, 'long', false, $timezone);
    }

    /**
     * Format long time
     */
    public function longTime($date, $timezone=true): ?Markup
    {
        return $this->locale($date, false, 'long', $timezone);
    }


    /**
     * Format medium date time
     */
    public function mediumDateTime($date, $timezone=true): ?Markup
    {
        return $this->locale($date, 'medium', 'medium', $timezone);
    }

    /**
     * Format medium date
     */
    public function mediumDate($date, $timezone=true): ?Markup
    {
        return $this->locale($date, 'medium', false, $timezone);
    }

    /**
     * Format medium time
     */
    public function mediumTime($date, $timezone=true): ?Markup
    {
        return $this->locale($date, false, 'medium', $timezone);
    }


    /**
     * Format short date time
     */
    public function shortDateTime($date, $timezone=true): ?Markup
    {
        return $this->locale($date, 'short', 'short', $timezone);
    }

    /**
     * Format short date
     */
    public function shortDate($date, $timezone=true): ?Markup
    {
        return $this->locale($date, 'short', false, $timezone);
    }

    /**
     * Format short time
     */
    public function shortTime($date, $timezone=true): ?Markup
    {
        return $this->locale($date, false, 'short', $timezone);
    }




    /**
     * Format interval since date
     */
    public function since($date, ?int $parts=2, bool $short=false): ?Markup
    {
        $this->checkCarbon();

        if (!$date = $this->normalizeDate($date)) {
            return null;
        }

        $now = $this->normalizeDate('now');
        $interval = CarbonInterval::make($date->diff($now));

        $output = $this->wrapInterval($date, $interval, $parts, $short);

        if ($interval->invert) {
            $output->addClass('future negative');
        } else {
            $output->addClass('passed positive');
        }

        return $output;
    }

    /**
     * Format interval until date
     */
    public function until($date, ?int $parts=2, bool $short=false): ?Markup
    {
        $this->checkCarbon();

        if (!$date = $this->normalizeDate($date)) {
            return null;
        }

        $now = $this->normalizeDate('now');
        $interval = CarbonInterval::make($now->diff($date));

        $output = $this->wrapInterval($date, $interval, $parts, $short);

        if ($interval->invert) {
            $output->addClass('passed negative');
        } else {
            $output->addClass('future positive');
        }

        return $output;
    }


    /**
     * Format interval
     */
    protected function wrapInterval(DateTime $date, DateInterval $interval, ?int $parts, bool $short=false): Markup
    {
        $formatter = new IntlDateFormatter(
            Systemic::$locale->get(),
            IntlDateFormatter::LONG,
            IntlDateFormatter::LONG
        );

        $output = $this->wrap(
            $date->format(DateTime::W3C),
            ($interval->invert ? '-' : '').
            $interval->forHumans([
                'short' => $short,
                'join' => true,
                'parts' => $parts,
                'options' => CarbonInterface::JUST_NOW | CarbonInterface::ONE_DAY_WORDS,
                'syntax' => CarbonInterface::DIFF_ABSOLUTE
            ]),
            $formatter->format($date)
        );

        return $output;
    }


    /**
     * Format interval until date
     */
    public function fromNow($date, ?int $parts=2, bool $short=false): ?Markup
    {
        $this->checkCarbon();

        if (!$date = $this->normalizeDate($date)) {
            return null;
        }

        $now = $this->normalizeDate('now');
        $interval = CarbonInterval::make($date->diff($now));

        $formatter = new IntlDateFormatter(
            Systemic::$locale->get(),
            IntlDateFormatter::LONG,
            IntlDateFormatter::LONG
        );

        $output = $this->wrap(
            $date->format(DateTime::W3C),
            $interval->forHumans([
                'short' => $short,
                'join' => true,
                'parts' => $parts,
                'options' => CarbonInterface::JUST_NOW | CarbonInterface::ONE_DAY_WORDS,
                'syntax' => CarbonInterface::DIFF_RELATIVE_TO_NOW
            ]),
            $formatter->format($date)
        );

        if ($interval->invert) {
            $output->addClass('future');
        } else {
            $output->addClass('passed');
        }

        return $output;
    }



    /**
     * Format interval until date
     */
    public function between($date1, $date2, ?int $parts=2, bool $short=false): ?Markup
    {
        $this->checkCarbon();

        if (!$date1 = $this->normalizeDate($date1)) {
            return null;
        }

        if (!$date2 = $this->normalizeDate($date2)) {
            return null;
        }

        $interval = CarbonInterval::make($date1->diff($date2));

        $formatter = new IntlDateFormatter(
            Systemic::$locale->get(),
            IntlDateFormatter::LONG,
            IntlDateFormatter::LONG
        );

        $output = $this->html->el(
            'span.interval',
            ($interval->invert ? '-' : '').
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
     * Prepare date for formatting
     */
    protected function prepare($date, $timezone=true, bool $includeTime=true): ?\DateTime
    {
        if (null === ($date = $this->normalizeDate($date))) {
            return null;
        }

        if ($timezone === false) {
            $timezone = null;
            $includeTime = false;
        }

        if ($timezone !== null) {
            $date = clone $date;

            if ($timezone = $this->normalizeTimezone($timezone)) {
                $date->setTimezone($timezone);
            }
        }

        return $date;
    }

    /**
     * Normalize a date input
     */
    protected function normalizeDate($date): ?\DateTime
    {
        if ($date === null) {
            return null;
        } elseif ($date instanceof DateTime) {
            return $date;
        }

        if ($date instanceof DateInterval) {
            $int = $date;
            return $this->normalizeDate('now')->add($int);
        }

        $timestamp = null;

        if (is_numeric($date)) {
            $timestamp = $date;
            $date = 'now';
        }

        $date = new DateTime($date);

        if ($timestamp !== null) {
            $date->setTimestamp($timestamp);
        }

        return $date;
    }

    /**
     * Normalize timezone
     */
    protected function normalizeTimezone($timezone): ?\DateTimeZone
    {
        if ($timezone === true) {
            $timezone = Systemic::$timezone->get();
        }

        if ($timezone instanceof DateTimeZone) {
            return $timezone;
        }

        return new DateTimeZone((string)$timezone);
    }


    /**
     * Wrap date / time in Markup
     */
    protected function wrap(string $w3c, string $formatted, string $title=null): Markup
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
     * Normalize locale format size
     */
    protected function normalizeLocaleSize($size): int
    {
        if ($size === false || $size === null) {
            return IntlDateFormatter::NONE;
        }

        if ($size === true) {
            return IntlDateFormatter::LONG;
        }

        switch ($size) {
            case 'full':
                return IntlDateFormatter::FULL;

            case 'long':
                return IntlDateFormatter::LONG;

            case 'medium':
                return IntlDateFormatter::MEDIUM;

            case 'short':
                return IntlDateFormatter::SHORT;

            case IntlDateFormatter::FULL:
            case IntlDateFormatter::LONG:
            case IntlDateFormatter::MEDIUM:
            case IntlDateFormatter::SHORT:
                return $size;

            default:
                throw Glitch::EInvalidArgument('Invalid locale formatter size: '.$size);
        }
    }


    /**
     * Check Carbon installed
     */
    protected function checkCarbon(): void
    {
        if (!class_exists(Carbon::class)) {
            throw Glitch::EComponentUnavailable('nesbot/carbon is required for formatting intervals');
        }
    }
}
