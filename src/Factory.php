<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged;

use DecodeLabs\Tagged\Markup;
use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\Builder\Html\ContentCollection;
use DecodeLabs\Tagged\Builder\Html\Tag;
use DecodeLabs\Tagged\Builder\Html\Element;

use DecodeLabs\Veneer\FacadeTargetTrait;

class Factory implements Markup
{
    const FACADE = 'Html';

    use FacadeTargetTrait;

    /**
     * Instance shortcut to el
     */
    public function __invoke(string $name, $content, array $attributes=null): Markup
    {
        return $this->el($name, $content, $attributes);
    }

    /**
     * Call named widget from instance
     */
    public function __call(string $name, array $args): Markup
    {
        return Element::create($name, ...$args);
    }

    /**
     * Dummy string generator to satisfy Markup dep
     */
    public function __toString(): string
    {
        return '';
    }




    /**
     * Create a standalone tag
     */
    public function tag(string $name, array $attributes=null): Markup
    {
        return new Tag($name, $attributes);
    }

    /**
     * Create a standalone element
     */
    public function el(string $name, $content, array $attributes=null): Markup
    {
        return Element::create($name, $content, $attributes);
    }

    /**
     * Wrap raw html string
     */
    public function raw(string $html): Markup
    {
        return new Buffer($html);
    }

    /**
     * Normalize arbitrary content
     */
    public function wrap(...$content): Markup
    {
        return ContentCollection::normalize($content);
    }

    /**
     * Wrap arbitrary content as collection
     */
    public function content(...$content): Markup
    {
        return new ContentCollection($content);
    }




    /**
     * Generate nested list
     */
    public function list(?iterable $list, string $container, string $name, callable $callback=null, array $attributes=[]): Markup
    {
        return Element::create($container, function () use ($list, $name, $callback) {
            if (!$list) {
                return;
            }

            $i = 0;

            foreach ($list as $key => $item) {
                yield Element::create($name, function ($el) use ($key, $item, $callback, &$i) {
                    if ($callback) {
                        return $callback($item, $el, $key, ++$i);
                    } else {
                        return $item;
                    }
                });
            }
        }, $attributes)->setRenderEmpty(false);
    }


    /**
     * Generate naked list
     */
    public function elements(?iterable $list, string $name, callable $callback=null, array $attributes=[]): Markup
    {
        return ContentCollection::normalize(function () use ($list, $name, $callback, $attributes) {
            if (!$list) {
                return;
            }

            $i = 0;

            foreach ($list as $key => $item) {
                yield el($name, function ($el) use ($key, $item, $callback, &$i) {
                    if ($callback) {
                        return $callback($item, $el, $key, ++$i);
                    } else {
                        return $item;
                    }
                }, $attributes);
            }
        });
    }



    /**
     * Convert arbitrary html to text
     */
    public function toText($html): ?string
    {
        if (is_string($html)) {
            $html = new Buffer($html);
        }

        $html = (string)ContentCollection::normalize($html);

        if (empty($html)) {
            return null;
        }

        $output = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5);
        $output = str_replace("\r\n", "\n", $output);
        return $output;
    }

    /**
     * Convert HTML to text and shorten if needed
     */
    public function previewText($html, int $maxLength=null): ?string
    {
        if (null === ($output = self::toText($html))) {
            return null;
        }

        if ($maxLength !== null) {
            $length = mb_strlen($output);

            if ($length > $maxLength) {
                $output = mb_substr($output, 0, $maxLength).'…';
            }
        }

        return $output;
    }

    /**
     * Convert HTML to text and shorten if neededm wrapping in Markup
     */
    public function preview($html, int $maxLength=null): Markup
    {
        if (null === ($output = self::toText($html))) {
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




    /**
     * Convert plain text string to renderable HTML
     */
    public function plainText(?string $text): Markup
    {
        if (empty($text) && $text !== '0') {
            return null;
        }

        $text = self::esc($text);
        $text = str_replace("\n", "\n".'<br />', $text);

        return new Buffer($text);
    }

    /**
     * Parse and render markdown
     */
    public function markdown(?string $text): Markup
    {
        \Glitch::incomplete($text);
    }

    /**
     * Parse and render simpleTags
     */
    public function simpleTags(?string $text): Markup
    {
        \Glitch::incomplete($text);
    }

    /**
     * Parse and render tweet
     */
    public function tweet(?string $text): Markup
    {
        \Glitch::incomplete($text);
    }

    /**
     * Escape HTML
     */
    public function esc(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}
