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

use DecodeLabs\Chirp\Parser as Chirp;

class Parse implements FacadePlugin
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
     * Convert plain text string to renderable HTML
     */
    public function plainText(?string $text): Markup
    {
        if (empty($text) && $text !== '0') {
            return null;
        }

        $text = $this->html->esc($text);
        $text = str_replace("\n", '<br />'."\n", $text);

        return new Buffer($text);
    }



    /**
     * Parse and render markdown
     */
    public function markdown(?string $text, ?callable $prep=null): Markup
    {
        return $this->parseMarkdown($text, false, false, $prep);
    }

    /**
     * Parse and render unsafe markdown
     */
    public function userMarkdown(?string $text, ?callable $prep=null): Markup
    {
        return $this->parseMarkdown($text, false, true, $prep);
    }

    /**
     * Parse and render inline markdown
     */
    public function inlineMarkdown(?string $text, ?callable $prep=null): Markup
    {
        return $this->parseMarkdown($text, true, false, $prep);
    }

    /**
     * Parse and render unsafe inline markdown
     */
    public function userInlineMarkdown(?string $text, ?callable $prep=null): Markup
    {
        return $this->parseMarkdown($text, true, true, $prep);
    }

    /**
     * Load markdown processor and parse the content
     */
    protected function parseMarkdown(?string $text, bool $inline=false, bool $unsafe=true): Markup
    {
        if (empty($text)) {
            return new Buffer('');
        }

        if (class_exists('\Parsedown')) {
            // Parsedown
            $parser = new \Parsedown();
            $parser->setSafeMode($unsafe);

            if ($prep) {
                $prep($parser, $text);
            }

            if ($inline) {
                return new Buffer($parser->line($text));
            } else {
                return new Buffer($parser->text($text));
            }
        } elseif (!$inline && class_exists('\\Michelf\\Markdown')) {
            // PHP Markdown
            $parser = new \Michelf\Markdown();

            if ($unsafe) {
                $parser->no_markup = true;
                $parser->no_entities = true;
            }

            if ($prep) {
                $prep($parser, $text);
            }

            return new Buffer($parser->transform($text));
        } else {
            throw Glitch::EComponentUnavailable(
                'No supported Markdown processors could be found for the requested format - try installing Parsedown'
            );
        }
    }




    /**
     * Parse and render simpleTags
     */
    public function simpleTags(?string $text): Markup
    {
        Glitch::incomplete($text);
    }

    /**
     * Parse and render unsafe simpleTags
     */
    public function userSimpleTags(?string $text): Markup
    {
        Glitch::incomplete($text);
    }

    /**
     * Parse and render inline simpleTags
     */
    public function inlineSimpleTags(?string $text): Markup
    {
        Glitch::incomplete($text);
    }

    /**
     * Parse and render unsafe inline simpleTags
     */
    public function inlineUserSimpleTags(?string $text): Markup
    {
        Glitch::incomplete($text);
    }




    /**
     * Parse and render tweet
     */
    public function tweet(?string $text): Markup
    {
        if (empty($text)) {
            return new Buffer('');
        }

        if (!class_exists(Chirp::class)) {
            throw Glitch::EComponentUnavailable(
                'No supported Tweet processors could be found - try installing decodelabs/chirp'
            );
        }

        $parser = new Chirp();
        return new Buffer($parser->parse($text));
    }
}
