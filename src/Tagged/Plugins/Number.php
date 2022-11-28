<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Plugins;

use DecodeLabs\Cosmos\Extension\Number as NumberPlugin;
use DecodeLabs\Cosmos\Extension\NumberTrait as NumberPluginTrait;
use DecodeLabs\Cosmos\Locale;
use DecodeLabs\Tagged\Element;
use DecodeLabs\Tagged\Factory;

/**
 * @implements NumberPlugin<Element>
 */
class Number implements NumberPlugin
{
    /**
     * @use NumberPluginTrait<Element>
     */
    use NumberPluginTrait;

    protected Factory $html;

    /**
     * Init with parent factory
     */
    public function __construct(Factory $html)
    {
        $this->html = $html;
    }


    /**
     * Format and wrap number
     */
    public function wrap(
        int|float|string|null $value,
        ?string $unit = null,
        string|Locale|null $locale = null
    ): ?Element {
        return $this->format($value, $unit, $locale);
    }

    /**
     * Format and wrap number
     */
    public function format(
        int|float|string|null $value,
        ?string $unit = null,
        string|Locale|null $locale = null
    ): ?Element {
        $this->expandStringUnitValue($value, $unit);

        if (null === ($value = $this->normalizeNumeric($value, true))) {
            return null;
        }

        return $this->html->el('span.number', function () use ($value, $unit, $locale) {
            $value = $this->formatRawDecimal($value, null, $locale);

            yield $this->html->el('span.value', $value);

            if ($unit !== null) {
                yield $this->html->el('span.unit', $unit);
            }
        });
    }

    /**
     * Format according to pattern and wrap
     */
    public function pattern(
        int|float|string|null $value,
        string $pattern,
        string|Locale|null $locale = null
    ): ?Element {
        if (null === ($value = $this->normalizeNumeric($value))) {
            return null;
        }

        return $this->html->el('span.number.pattern', function () use ($value, $pattern, $locale) {
            $value = $this->formatRawPatternDecimal($value, $pattern, $locale);
            yield $this->html->el('span.value', $value);
        });
    }

    /**
     * Format and render a decimal
     */
    public function decimal(
        int|float|string|null $value,
        ?int $precision = null,
        string|Locale|null $locale = null
    ): ?Element {
        if (null === ($value = $this->normalizeNumeric($value))) {
            return null;
        }

        return $this->html->el('span.number.decimal', function () use ($value, $precision, $locale) {
            $value = $this->formatRawDecimal($value, $precision, $locale);
            yield $this->html->el('span.value', $value);
        });
    }

    /**
     * Format and wrap currency
     */
    public function currency(
        int|float|string|null $value,
        ?string $code,
        ?bool $rounded = null,
        string|Locale|null $locale = null
    ): ?Element {
        if (
            null === ($value = $this->normalizeNumeric($value)) ||
            $code === null
        ) {
            return null;
        }

        $value = $this->formatRawCurrency($value, $code, $rounded, $locale);

        if (!preg_match('/^(([^0-9.,\s][^0-9]*)([\s]*))?([0-9.,]+)(([\s]*)([^0-9.,\s][^0-9]*))?$/u', $value, $matches)) {
            return $this->html->el('span.number.currency', $value);
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
    public function percent(
        int|float|string|null $value,
        float $total = 100.0,
        int $decimals = 0,
        string|Locale|null $locale = null
    ): ?Element {
        if (
            null === ($value = $this->normalizeNumeric($value, true)) ||
            $total <= 0
        ) {
            return null;
        }

        return $this->html->el('span.number.percent', function () use ($value, $total, $decimals, $locale) {
            $value = $this->formatRawPercent($value, $total, $decimals, $locale);

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
    public function scientific(
        int|float|string|null $value,
        string|Locale|null $locale = null
    ): ?Element {
        if (null === ($value = $this->normalizeNumeric($value))) {
            return null;
        }

        return $this->html->el('span.number.scientific', function () use ($value, $locale) {
            $value = $this->formatRawScientific($value, $locale);
            yield $this->html->el('span.value', $value);
        });
    }

    /**
     * Format and render a number as words
     */
    public function spellout(
        int|float|string|null $value,
        string|Locale|null $locale = null
    ): ?Element {
        if (null === ($value = $this->normalizeNumeric($value))) {
            return null;
        }

        return $this->html->el('span.number.spellout', function () use ($value, $locale) {
            $value = $this->formatRawSpellout($value, $locale);
            yield $this->html->el('span.value', $value);
        });
    }

    /**
     * Format and render a number as ordinal
     */
    public function ordinal(
        int|float|string|null $value,
        string|Locale|null $locale = null
    ): ?Element {
        if (null === ($value = $this->normalizeNumeric($value))) {
            return null;
        }

        return $this->html->el('span.number.ordinal', function () use ($value, $locale) {
            $value = $this->formatRawOrdinal($value, $locale);
            yield $this->html->el('span.value', $value);
        });
    }


    /**
     * Render difference of number from 0
     */
    public function diff(
        int|float|string|null $diff,
        ?bool $invert = false,
        string|Locale|null $locale = null
    ): ?Element {
        if (null === ($diff = $this->normalizeNumeric($diff))) {
            return null;
        }

        $diff = (float)$diff;

        if ($invert) {
            $diff *= -1;
        }

        $output = $this->html->el('span.number.diff', [
            $this->html->el('span.arrow', $this->getDiffArrow($diff)),
            $this->format(abs($diff), null, $locale)
        ])->addClass('diff');

        if ($invert !== null) {
            $output->addClass($diff < 0 ? 'negative' : 'positive');
        }

        return $output;
    }




    /**
     * Format filesize
     */
    public function fileSize(
        ?int $bytes,
        string|Locale|null $locale = null
    ): ?Element {
        if ($bytes === null) {
            return null;
        }

        $output = $this->formatRawFileSize($bytes, $locale);
        $parts = explode(' ', $output);
        $value = $parts[0] ?? '0';
        $unit = $parts[1] ?? 'B';

        return $this->html->el('span.number.filesize', [
            $this->html->el('span.value', $value),
            $this->html->el('span.unit', $unit)
        ]);
    }

    /**
     * Format filesize as decimal
     */
    public function fileSizeDec(
        ?int $bytes,
        string|Locale|null $locale = null
    ): ?Element {
        if ($bytes === null) {
            return null;
        }

        $output = $this->formatRawFileSizeDec($bytes, $locale);
        $parts = explode(' ', $output);
        $value = $parts[0] ?? '0';
        $unit = $parts[1] ?? 'B';

        return $this->html->el('span.number.filesize', [
            $this->html->el('span.value', $value),
            $this->html->el('span.unit', $unit)
        ]);
    }
}
