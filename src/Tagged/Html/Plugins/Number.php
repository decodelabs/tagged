<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged\Html\Plugins;

use DecodeLabs\Veneer\FacadePlugin;

use DecodeLabs\Tagged\Markup;
use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\Html\Factory as HtmlFactory;

use DecodeLabs\Tagged\Html\ContentCollection;
use DecodeLabs\Tagged\Html\Element;

class Number implements FacadePlugin
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
     * Format and wrap currency
     */
    public function currency($value, string $code): ?Markup
    {
        if ($value === null) {
            return null;
        }

        $formatter = new \NumberFormatter(Systemic::$locale->get(),  \NumberFormatter::CURRENCY);
        $output = $formatter->formatCurrency($value, $code);

        if (!preg_match('/^(([^0-9.,\s][^0-9]*)([\s]*))?([0-9.,]+)(([\s]*)([^0-9.,\s][^0-9]*))?$/u', $output, $matches)) {
            return $this->html->el('span.currency', $output);
        }

        return $this->html->el('span.currency', function () use ($matches) {
            if (!empty($matches[2])) {
                yield $this->html->el('span.symbol', $matches[2]);

                if (!empty($matches[3])) {
                    yield $matches[3];
                }
            }

            yield $this->html->el('span.value', $matches[4]);

            if (isset($matches[7])) {
                if (isset($matches[6])) {
                    yield $matches[6];
                }

                yield $this->html->el('span.symbol', $matches[7]);
            }
        });
    }


    /**
     * Format and wrap number
     */
    public function format($value, ?string $unit=null): ?Markup
    {
        if ($value === null) {
            return null;
        }

        if ($unit === null && is_string($value) && false !== strpos($value, ' ')) {
            list($value, $unit) = explode(' ', $value, 2);
        }

        return $this->html->el('span.number', function () use ($value, $unit) {
            if (is_int($value)
            || is_float($value)
            || is_string($value) && (string)((float)$value) === $value) {
                $formatter = new \NumberFormatter(Systemic::$locale->get(), \NumberFormatter::DECIMAL);
                $value = $formatter->format($value);
            }

            yield $this->html->el('span.value', $value);

            if ($unit !== null) {
                yield $this->html->el('span.unit', $unit);
            }
        });
    }


    /**
     * Render difference of number from 0
     */
    public function diff(?float $diff, ?bool $invert=false, string $tag='span'): Markup
    {
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
            $this->format(abs($diff))
        ])->addClass('diff');

        if ($invert !== null) {
            if ($invert) {
                $diff *= -1;
            }

            $output->addClass($diff < 0 ? 'negative' : 'positive');
        }

        return $output;
    }
}
