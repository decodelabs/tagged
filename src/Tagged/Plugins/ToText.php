<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Plugins;

use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\ContentCollection;
use DecodeLabs\Tagged\Element;
use DecodeLabs\Tagged\Factory;
use DecodeLabs\Tagged\Markup;
use DecodeLabs\Veneer\Plugin;

use Soundasleep\Html2Text;

class ToText implements Plugin
{
    protected $html;

    /**
     * Init with parent factory
     */
    public function __construct(Factory $html)
    {
        $this->html = $html;
    }

    /**
     * Shortcut to convert
     */
    public function __invoke(?string $html): ?string
    {
        return $this->convert($html);
    }

    /**
     * Convert HTML to text
     */
    public function convert(?string $html): ?string
    {
        if ($html === null) {
            return null;
        } elseif (is_string($html)) {
            $html = new Buffer($html);
        }

        $html = (string)ContentCollection::normalize($html);

        if (!strlen($html)) {
            return null;
        }

        if (class_exists(Html2Text::class)) {
            return Html2Text::convert($html, [
                'ignore_errors' => true
            ]);
        } else {
            $output = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5);
            return str_replace("\r\n", "\n", $output);
        }
    }



    /**
     * Convert HTML to text and shorten if needed
     */
    public function previewText($html, int $maxLength = null): ?string
    {
        if (null === ($output = $this->convert($html))) {
            return null;
        }

        if ($maxLength !== null) {
            $length = mb_strlen($output);

            if ($length > $maxLength) {
                $output = mb_substr($output, 0, $maxLength) . '…';
            }
        }

        return $output;
    }

    /**
     * Convert HTML to text and shorten if needed, wrapping in Markup
     */
    public function preview($html, int $maxLength = null): ?Markup
    {
        if (null === ($output = $this->convert($html))) {
            return null;
        }

        if ($maxLength !== null) {
            $length = mb_strlen($output);

            if ($length > $maxLength) {
                $output = [
                    Element::create('abbr', mb_substr($output, 0, $maxLength), [
                        'title' => $output
                    ]),
                    Element::create('span.suffix', '…')
                ];
            }
        }

        return ContentCollection::normalize($output);
    }
}
