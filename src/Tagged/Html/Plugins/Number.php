<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Html\Plugins;

use DecodeLabs\Exceptional;
use DecodeLabs\Tagged\Html\Element;
use DecodeLabs\Tagged\Html\Factory as HtmlFactory;
use DecodeLabs\Tagged\Markup;
use DecodeLabs\Veneer\Plugin;

use NumberFormatter;

class Number implements Plugin
{
    use SystemicProxyTrait;

    protected $html;

    /**
     * Init with parent factory
     */
    public function __construct(HtmlFactory $html)
    {
        $this->html = $html;
    }


    /**
     * Format and wrap number
     */
    public function wrap($value, ?string $unit = null): ?Element
    {
        if ($value === null) {
            return null;
        }

        if ($unit === null && is_string($value) && false !== strpos($value, ' ')) {
            list($value, $unit) = explode(' ', $value, 2);
        }

        return $this->html->el('span.number', function () use ($value, $unit) {
            // Normalize string value
            if (
                is_string($value) &&
                is_numeric($value)
            ) {
                $value = $value == (int)$value ?
                    (int)$value : (float)$value;
            }

            if (
                is_int($value) ||
                is_float($value)
            ) {
                $formatter = new NumberFormatter($this->getLocale(), NumberFormatter::DECIMAL);
                $value = $formatter->format($value);
            } else {
                throw Exceptional::InvalidArgument('Value is not a number', null, $value);
            }

            yield $this->html->el('span.value', $value);

            if ($unit !== null) {
                yield $this->html->el('span.unit', $unit);
            }
        });
    }

    /**
     * Format and wrap currency
     */
    public function currency($value, ?string $code, ?bool $rounded = null): ?Markup
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

        $formatter = new NumberFormatter($this->getLocale(), NumberFormatter::CURRENCY);
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
    public function percent($value, float $total = 100.0, int $decimals = 0): ?Element
    {
        if ($value === null || $total <= 0) {
            return null;
        }

        return $this->html->el('span.number.percent', function () use ($value, $total, $decimals) {
            // Normalize string value
            if (
                is_string($value) &&
                is_numeric($value)
            ) {
                $value = $value == (int)$value ?
                    (int)$value : (float)$value;
            }

            if (
                is_int($value) ||
                is_float($value)
            ) {
                $formatter = new NumberFormatter($this->getLocale(), NumberFormatter::PERCENT);
                $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
                $value = $formatter->format($value / $total);
            } else {
                throw Exceptional::InvalidArgument('Percent value is not a number', null, $value);
            }

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
     * Render difference of number from 0
     */
    public function diff($diff, ?bool $invert = false, string $tag = 'sup'): Element
    {
        if (!is_numeric($diff)) {
            throw Exceptional::InvalidArgument(
                'Diff value is not a number',
                null,
                $diff
            );
        }

        $diff = (float)$diff;

        if ($diff > 0) {
            $arrow = '⬆';
        } elseif ($diff < 0) {
            $arrow = '⬇';
        } else {
            $arrow = '⬌';
        }

        $output = $this->html->el($tag, [
            $arrow,
            $this->wrap(abs($diff))
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
    public function fileSize(?int $bytes): ?Element
    {
        if ($bytes === null) {
            return null;
        }

        $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return $this->html->el('span.numeric.filesize', [
            $this->html->el('span.value', round($bytes, 2)),
            $this->html->el('span.unit', $units[$i])
        ]);
    }

    /**
     * Format filesize as decimal
     */
    public function fileSizeDec(?int $bytes): ?Element
    {
        if ($bytes === null) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        for ($i = 0; $bytes > 1000; $i++) {
            $bytes /= 1000;
        }

        return $this->html->el('span.numeric.filesize', [
            $this->html->el('span.value', round($bytes, 2)),
            $this->html->el('span.unit', $units[$i])
        ]);
    }
}
