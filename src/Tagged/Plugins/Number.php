<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Plugins;

use DecodeLabs\Dictum\Plugin\Number as NumberPlugin;
use DecodeLabs\Dictum\Plugin\NumberTrait as NumberPluginTrait;
use DecodeLabs\Tagged\Element;
use DecodeLabs\Tagged\Factory;

use NumberFormatter;

/**
 * @implements NumberPlugin<Element>
 */
class Number implements NumberPlugin
{
    use SystemicProxyTrait;

    /**
     * @use NumberPluginTrait<Element>
     */
    use NumberPluginTrait;

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
     * Format and wrap number
     *
     * @param int|float|string|null $value
     */
    public function wrap($value, ?string $unit = null, ?string $locale = null): ?Element
    {
        return $this->format($value, $unit, $locale);
    }

    /**
     * Format and wrap number
     */
    public function format($value, ?string $unit = null, ?string $locale = null): ?Element
    {
        if ($unit === null && is_string($value) && false !== strpos($value, ' ')) {
            list($value, $unit) = explode(' ', $value, 2);
        }

        if (null === ($value = $this->normalizeNumeric($value, true))) {
            return null;
        }

        return $this->html->el('span.number', function () use ($value, $unit, $locale) {
            $formatter = new NumberFormatter($this->getLocale($locale), NumberFormatter::DECIMAL);
            $value = $formatter->format($value);

            yield $this->html->el('span.value', $value);

            if ($unit !== null) {
                yield $this->html->el('span.unit', $unit);
            }
        });
    }

    /**
     * Format according to pattern and wrap
     */
    public function pattern($value, string $pattern, ?string $locale = null): ?Element
    {
        if (null === ($value = $this->normalizeNumeric($value))) {
            return null;
        }

        return $this->html->el('span.number.pattern', function () use ($value, $pattern, $locale) {
            $formatter = new NumberFormatter($this->getLocale($locale), NumberFormatter::PATTERN_DECIMAL, $pattern);
            $value = $formatter->format($value);

            yield $this->html->el('span.value', $value);
        });
    }

    /**
     * Format and render a decimal
     */
    public function decimal($value, ?int $precision = null, ?string $locale = null): ?Element
    {
        if (null === ($value = $this->normalizeNumeric($value))) {
            return null;
        }

        return $this->html->el('span.number.decimal', function () use ($value, $precision, $locale) {
            $formatter = new NumberFormatter($this->getLocale($locale), NumberFormatter::DECIMAL);

            if ($precision !== null) {
                $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $precision);
            }

            $value = $formatter->format($value);
            yield $this->html->el('span.value', $value);
        });
    }

    /**
     * Format and wrap currency
     */
    public function currency($value, ?string $code, ?bool $rounded = null, ?string $locale = null): ?Element
    {
        if ($value === null || $code === null) {
            return null;
        }

        if (is_int($value)) {
            $value = (float)$value;
        } elseif (!is_float($value)) {
            $value = (float)((string)$value);
        }

        $code = strtoupper($code);

        $formatter = new NumberFormatter($this->getLocale($locale), NumberFormatter::CURRENCY);
        $formatter->setTextAttribute(NumberFormatter::CURRENCY_CODE, $code);

        if (
            $rounded === true ||
            (
                $rounded === null &&
                (round($value, 0) == round($value, 2))
            )
        ) {
            $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);
        }

        $output = $formatter->formatCurrency($value, $code);

        if (!preg_match('/^(([^0-9.,\s][^0-9]*)([\s]*))?([0-9.,]+)(([\s]*)([^0-9.,\s][^0-9]*))?$/u', $output, $matches)) {
            return $this->html->el('span.number.currency', $output);
        }

        return $this->html->el('span.number.currency', function () use ($matches) {
            if (!empty($matches[2])) {
                yield $this->wrapCurrencySymbol($matches[2]);
            }

            yield $this->html->el('span.value', $matches[4]);

            if (isset($matches[7])) {
                yield $this->wrapCurrencySymbol($matches[7]);
            }
        });
    }

    protected function wrapCurrencySymbol(string $symbolInput): Element
    {
        if (empty($symbol = str_replace('&nbsp;', '', htmlentities($symbolInput)))) {
            $symbol = $symbolInput;
        } else {
            $symbol = html_entity_decode($symbol);
        }

        $symbolTag = $this->html->el('span.unit.symbol', $symbol);

        if (preg_match('/^[A-Z]{2,}$/', $symbol)) {
            $symbolTag->addClass('code');
        }

        return $symbolTag;
    }

    /**
     * Format and render a percentage
     */
    public function percent($value, float $total = 100.0, int $decimals = 0, ?string $locale = null): ?Element
    {
        if (
            null === ($value = $this->normalizeNumeric($value, true)) ||
            $total <= 0
        ) {
            return null;
        }

        return $this->html->el('span.number.percent', function () use ($value, $total, $decimals, $locale) {
            $formatter = new NumberFormatter($this->getLocale($locale), NumberFormatter::PERCENT);
            $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
            $value = $formatter->format($value / $total);

            if (!preg_match('/^(%)?([0-9,.]+)(%)?$/u', $value, $matches)) {
                return $value;
            }

            if (!empty($matches[1])) {
                yield $this->html->el('span.unit', '%');
            }

            yield $this->html->el('span.value', $matches[2]);

            if (!empty($matches[3])) {
                yield $this->html->el('span.unit', '%');
            }
        });
    }

    /**
     * Format and render a scientific number
     */
    public function scientific($value, ?string $locale = null): ?Element
    {
        if (null === ($value = $this->normalizeNumeric($value))) {
            return null;
        }

        return $this->html->el('span.number.scientific', function () use ($value, $locale) {
            $formatter = new NumberFormatter($this->getLocale($locale), NumberFormatter::SCIENTIFIC);
            $value = $formatter->format($value);

            yield $this->html->el('span.value', $value);
        });
    }

    /**
     * Format and render a number as words
     */
    public function spellout($value, ?string $locale = null): ?Element
    {
        if (null === ($value = $this->normalizeNumeric($value))) {
            return null;
        }

        return $this->html->el('span.number.spellout', function () use ($value, $locale) {
            $formatter = new NumberFormatter($this->getLocale($locale), NumberFormatter::SPELLOUT);
            $value = $formatter->format($value);

            yield $this->html->el('span.value', $value);
        });
    }

    /**
     * Format and render a number as ordinal
     */
    public function ordinal($value, ?string $locale = null): ?Element
    {
        if (null === ($value = $this->normalizeNumeric($value))) {
            return null;
        }

        return $this->html->el('span.number.ordinal', function () use ($value, $locale) {
            $formatter = new NumberFormatter($this->getLocale($locale), NumberFormatter::ORDINAL);
            $value = $formatter->format($value);

            yield $this->html->el('span.value', $value);
        });
    }


    /**
     * Render difference of number from 0
     */
    public function diff($diff, ?bool $invert = false, ?string $locale = null): ?Element
    {
        if (null === ($diff = $this->normalizeNumeric($diff))) {
            return null;
        }

        $diff = (float)$diff;

        $output = $this->html->el('span.number.diff', [
            $this->html->el('span.arrow', $this->getDiffArrow($diff)),
            $this->format(abs($diff), null, $locale)
        ])->addClass('diff');

        if ($invert !== null) {
            if ($invert) {
                $diff *= -1;
            }

            $output->addClass($diff < 0 ? 'negative' : 'positive');
        }

        return $output;
    }




    /**
     * Format filesize
     */
    public function fileSize(?int $bytes, ?string $locale = null): ?Element
    {
        if ($bytes === null) {
            return null;
        }

        $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        $output = $this->wrap(round($bytes, 2), $units[$i], $locale);

        if ($output !== null) {
            $output->addClass('filesize');
        }

        return $output;
    }

    /**
     * Format filesize as decimal
     */
    public function fileSizeDec(?int $bytes, ?string $locale = null): ?Element
    {
        if ($bytes === null) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        for ($i = 0; $bytes > 1000; $i++) {
            $bytes /= 1000;
        }

        $output = $this->wrap(round($bytes, 2), $units[$i], $locale);

        if ($output !== null) {
            $output->addClass('filesize');
        }

        return $output;
    }
}
