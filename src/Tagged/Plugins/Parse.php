<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Plugins;

use DecodeLabs\Chirp\Parser as Chirp;
use DecodeLabs\Exceptional;
use DecodeLabs\Glitch\Proxy as Glitch;
use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\Factory;
use DecodeLabs\Tagged\Markup;
use DecodeLabs\Veneer\Plugin;

use Michelf\Markdown;
use Parsedown;

class Parse implements Plugin
{
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
     * Convert plain text string to renderable HTML
     */
    public function plainText(?string $text): ?Markup
    {
        if (!strlen($text ?? '')) {
            return null;
        }

        $text = $this->html->esc($text);
        $text = str_replace("\n", '<br />' . "\n", (string)$text);

        return new Buffer($text);
    }



    /**
     * Parse and render markdown
     */
    public function markdown(?string $text, ?callable $prep = null): ?Markup
    {
        return $this->parseMarkdown($text, false, false, $prep);
    }

    /**
     * Parse and render unsafe markdown
     */
    public function userMarkdown(?string $text, ?callable $prep = null): ?Markup
    {
        return $this->parseMarkdown($text, false, true, $prep);
    }

    /**
     * Parse and render inline markdown
     */
    public function inlineMarkdown(?string $text, ?callable $prep = null): ?Markup
    {
        return $this->parseMarkdown($text, true, false, $prep);
    }

    /**
     * Parse and render unsafe inline markdown
     */
    public function userInlineMarkdown(?string $text, ?callable $prep = null): ?Markup
    {
        return $this->parseMarkdown($text, true, true, $prep);
    }

    /**
     * Load markdown processor and parse the content
     */
    protected function parseMarkdown(?string $text, bool $inline = false, bool $unsafe = true, ?callable $prep = null): ?Markup
    {
        if (!strlen($text ?? '')) {
            return null;
        }

        if (class_exists(Parsedown::class)) {
            // Parsedown
            $parser = new Parsedown();
            $parser->setSafeMode($unsafe);

            if ($prep) {
                $prep($parser, $text);
            }

            if ($inline) {
                return new Buffer($parser->line($text));
            } else {
                return new Buffer($parser->text($text));
            }
        } elseif (!$inline && class_exists(Markdown::class)) {
            // PHP Markdown
            $parser = new Markdown();

            if ($unsafe) {
                $parser->no_markup = true;
                $parser->no_entities = true;
            }

            if ($prep) {
                $prep($parser, $text);
            }

            return new Buffer($parser->transform((string)$text));
        } else {
            throw Exceptional::ComponentUnavailable(
                'No supported Markdown processors could be found for the requested format - try installing Parsedown'
            );
        }
    }




    /**
     * Parse and render simpleTags
     */
    public function simpleTags(?string $text, ?callable $prep = null): ?Markup
    {
        return $this->parseSimpleTags($text, false, false, $prep);
    }

    /**
     * Parse and render unsafe simpleTags
     */
    public function userSimpleTags(?string $text, ?callable $prep = null): ?Markup
    {
        return $this->parseSimpleTags($text, false, true, $prep);
    }

    /**
     * Parse and render inline simpleTags
     */
    public function inlineSimpleTags(?string $text, ?callable $prep = null): ?Markup
    {
        return $this->parseSimpleTags($text, true, false, $prep);
    }

    /**
     * Parse and render unsafe inline simpleTags
     */
    public function inlineUserSimpleTags(?string $text, ?callable $prep = null): ?Markup
    {
        return $this->parseSimpleTags($text, true, true, $prep);
    }

    /**
     * Load markdown processor and parse the content
     */
    protected function parseSimpleTags(?string $text, bool $inline = false, bool $unsafe = true, ?callable $prep = null): ?Markup
    {
        if (!strlen($text ?? '')) {
            return null;
        }

        Glitch::incomplete($text);
    }




    /**
     * Parse and render tweet
     */
    public function tweet(?string $text): ?Markup
    {
        if (!strlen($text ?? '')) {
            return null;
        }

        if (!class_exists(Chirp::class)) {
            throw Exceptional::ComponentUnavailable(
                'No supported Tweet processors could be found - try installing decodelabs/chirp'
            );
        }

        $parser = new Chirp();
        return new Buffer($parser->parse($text));
    }
}
