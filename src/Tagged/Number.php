<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged;

use DecodeLabs\Cosmos\Extension\Number as NumberExtension;
use DecodeLabs\Cosmos\Extension\NumberTrait as NumberExtensionTrait;
use DecodeLabs\Cosmos\Locale;

/**
 * @implements NumberExtension<Element>
 */
class Number implements NumberExtension
{
    /**
     * @use NumberExtensionTrait<Element>
     */
    use NumberExtensionTrait;


    public static function wrap(
        int|float|string|null $value,
        ?string $unit = null,
        string|Locale|null $locale = null
    ): ?Element {
        /** @var ?Element */
        $output = static::format($value, $unit, $locale);
        return $output;
    }

    public static function format(
        int|float|string|null $value,
        ?string $unit = null,
        string|Locale|null $locale = null
    ): ?Element {
        static::expandStringUnitValue($value, $unit);

        if (null === ($value = static::normalizeNumeric($value, true))) {
            return null;
        }

        return new Element('span.number', function () use ($value, $unit, $locale) {
            $value = static::formatRawDecimal($value, null, $locale);

            yield new Element('span.value', $value);

            if ($unit !== null) {
                yield new Element('span.unit', $unit);
            }
        });
    }

    public static function pattern(
        int|float|string|null $value,
        string $pattern,
        string|Locale|null $locale = null
    ): ?Element {
        if (null === ($value = static::normalizeNumeric($value))) {
            return null;
        }

        return new Element('span.number.pattern', function () use ($value, $pattern, $locale) {
            $value = static::formatRawPatternDecimal($value, $pattern, $locale);
            yield new Element('span.value', $value);
        });
    }

    public static function decimal(
        int|float|string|null $value,
        ?int $precision = null,
        string|Locale|null $locale = null
    ): ?Element {
        if (null === ($value = static::normalizeNumeric($value))) {
            return null;
        }

        return new Element('span.number.decimal', function () use ($value, $precision, $locale) {
            $value = static::formatRawDecimal($value, $precision, $locale);
            yield new Element('span.value', $value);
        });
    }

    public static function currency(
        int|float|string|null $value,
        ?string $code,
        ?bool $rounded = null,
        string|Locale|null $locale = null
    ): ?Element {
        if (
            null === ($value = static::normalizeNumeric($value)) ||
            $code === null
        ) {
            return null;
        }

        $value = static::formatRawCurrency($value, $code, $rounded, $locale);

        if (!preg_match('/^(([^0-9.,\s][^0-9]*)([\s]*))?([0-9.,]+)(([\s]*)([^0-9.,\s][^0-9]*))?$/u', $value, $matches)) {
            return new Element('span.number.currency', $value);
        }

        return new Element('span.number.currency', function () use ($matches) {
            if (!empty($matches[2])) {
                yield static::wrapCurrencySymbol($matches[2]);
            }

            yield new Element('span.value', $matches[4]);

            if (isset($matches[7])) {
                yield static::wrapCurrencySymbol($matches[7]);
            }
        });
    }

    protected static function wrapCurrencySymbol(
        string $symbolInput
    ): Element {
        if (empty($symbol = str_replace('&nbsp;', '', htmlentities($symbolInput)))) {
            $symbol = $symbolInput;
        } else {
            $symbol = html_entity_decode($symbol);
        }

        $symbolTag = new Element('span.unit.symbol', $symbol);

        if (preg_match('/^[A-Z]{2,}$/', $symbol)) {
            $symbolTag->addClass('code');
        }

        return $symbolTag;
    }

    public static function percent(
        int|float|string|null $value,
        float $total = 100.0,
        int $decimals = 0,
        string|Locale|null $locale = null
    ): ?Element {
        if (
            null === ($value = static::normalizeNumeric($value, true)) ||
            $total <= 0
        ) {
            return null;
        }

        return new Element('span.number.percent', function () use ($value, $total, $decimals, $locale) {
            $value = static::formatRawPercent($value, $total, $decimals, $locale);

            if (!preg_match('/^(%)?([0-9,.]+)(%)?$/u', $value, $matches)) {
                return $value;
            }

            if (!empty($matches[1])) {
                yield new Element('span.unit', '%');
            }

            yield new Element('span.value', $matches[2]);

            if (!empty($matches[3])) {
                yield new Element('span.unit', '%');
            }
        });
    }

    public static function scientific(
        int|float|string|null $value,
        string|Locale|null $locale = null
    ): ?Element {
        if (null === ($value = static::normalizeNumeric($value))) {
            return null;
        }

        return new Element('span.number.scientific', function () use ($value, $locale) {
            $value = static::formatRawScientific($value, $locale);
            yield new Element('span.value', $value);
        });
    }

    public static function spellout(
        int|float|string|null $value,
        string|Locale|null $locale = null
    ): ?Element {
        if (null === ($value = static::normalizeNumeric($value))) {
            return null;
        }

        return new Element('span.number.spellout', function () use ($value, $locale) {
            $value = static::formatRawSpellout($value, $locale);
            yield new Element('span.value', $value);
        });
    }

    public static function ordinal(
        int|float|string|null $value,
        string|Locale|null $locale = null
    ): ?Element {
        if (null === ($value = static::normalizeNumeric($value))) {
            return null;
        }

        return new Element('span.number.ordinal', function () use ($value, $locale) {
            $value = static::formatRawOrdinal($value, $locale);
            yield new Element('span.value', $value);
        });
    }


    public static function diff(
        int|float|string|null $diff,
        ?bool $invert = false,
        string|Locale|null $locale = null
    ): ?Element {
        if (null === ($diff = static::normalizeNumeric($diff))) {
            return null;
        }

        $diff = (float)$diff;

        if ($invert) {
            $diff *= -1;
        }

        $output = new Element('span.number.diff', [
            new Element('span.arrow', static::getDiffArrow($diff)),
            static::format(abs($diff), null, $locale)
        ])->addClass('diff');

        if ($invert !== null) {
            $output->addClass($diff < 0 ? 'negative' : 'positive');
        }

        return $output;
    }




    public static function fileSize(
        ?int $bytes,
        string|Locale|null $locale = null
    ): ?Element {
        if ($bytes === null) {
            return null;
        }

        $output = static::formatRawFileSize($bytes, $locale);
        $parts = explode(' ', $output);
        $value = $parts[0];
        $unit = $parts[1] ?? 'B';

        return new Element('span.number.filesize', [
            new Element('span.value', $value),
            new Element('span.unit', $unit)
        ]);
    }


    public static function fileSizeDec(
        ?int $bytes,
        string|Locale|null $locale = null
    ): ?Element {
        if ($bytes === null) {
            return null;
        }

        $output = static::formatRawFileSizeDec($bytes, $locale);
        $parts = explode(' ', $output);
        $value = $parts[0];
        $unit = $parts[1] ?? 'B';

        return new Element('span.number.filesize', [
            new Element('span.value', $value),
            new Element('span.unit', $unit)
        ]);
    }
}
