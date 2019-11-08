<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged\Html\Mail;

use DecodeLabs\Tagged\Builder\StyleBlock;
use DecodeLabs\Tagged\Builder\StyleList;

use DecodeLabs\Tagged\Markup;
use DecodeLabs\Tagged\Html;
use DecodeLabs\Tagged\Html\Element;

class Generator
{
    public $styles;
    public $mobileStyles;

    /**
     * Init with default collections
     */
    public function __construct()
    {
        $this->styles = new StyleBlock(static::STYLES);
        $this->mobileStyles = new StyleBlock(static::MOBILE_STYLES);
    }

    /**
     * Generate document
     */
    public function document(string $subject, $content, array $bodyAttributes=null): Markup
    {
        $output =
            '<!doctype html>'."\n".
            '<html>'."\n".
            '<head>'."\n".
            '    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />'."\n".
            '    <meta name="viewport" content="width=device-width" />'."\n".
            '    <meta name="robots" content="noindex, nofollow" />'."\n".
            '    <meta name="googlebot" content="noindex, nofollow, noarchive" />'."\n".
            '    '.Html::title($subject)."\n".
            '    '.$this->css()."\n".
            '</head>'."\n".
            $this->body($content, $bodyAttributes).
            '</html>';

        return Html::raw($output);
    }

    /**
     * Render css
     */
    public function css(): Element
    {
        $width = $this->styles->get('content')->get('width');
        $css = "\n".'@media only screen and (max-width: '.$width.') {'."\n    ".$this->mobileStyles->renderStyles()."\n".'}'."\n";

        return Html::style(Html::raw($css), [
            'type' => 'text/css'
        ]);
    }

    /**
     * Render body tag
     */
    public function body($content, array $attributes=null): Element
    {
        $styles = $this->getStylesFor('body', 'text');

        return Html::{'body.email'}(
            function () use ($content, $styles) {
                $output = $this->container(function ($el) use ($content) {
                    $containerStyles = $this->getStylesFor('bodyContainer');
                    $contentStyles = $this->getStylesFor('content');
                    $width = $contentStyles->get('width');

                    $containerStyles->set('max-width', $width);
                    $containerStyles->set('width', $width);
                    $contentStyles->set('max-width', $width);

                    $el->addClass('bodyContainer');
                    $el->addStyles($containerStyles);

                    return Html::{'div.content'}($content, [
                        'style' => $contentStyles
                    ]);
                });

                $output->addClass('body');

                $output->addStyles($styles->export(
                    'background-color'
                ));

                return $output;
            },
            $attributes
        )->addStyles($styles);
    }


    /**
     * Render hidden preview content
     */
    public function previewText(?string $content): Element
    {
        return Html::{'?span.previewText'}($content)
            ->addStyles($this->getStylesFor('previewText'));
    }


    /**
     * Render content block
     */
    public function contentArea($content, array $attributes=null): Element
    {
        $styles = $this->getStylesFor('contentArea');

        return $this->container(
            function ($el) use ($content, $styles) {
                $el->addClass('contentArea');
                $el->addStyles($styles);
                yield $content;
            },
            $attributes
        );
    }

    /**
     * Render banner
     */
    public function banner(string $url, int $width, int $height, ?string $alt=null): Element
    {
        return $this->container(function ($el) use ($url, $width, $height, $alt) {
            $el->addClass('banner');
            $el->addStyles($this->getStylesFor('banner'));

            return $this->image($url, $width, $height, $alt ?? 'Banner');
        });
    }

    /**
     * Render section block
     */
    public function section($content, array $attributes=null): Element
    {
        return $this->container(
            function ($el) use ($content) {
                $el->addClass('section');
                $el->addStyles($this->getStylesFor('section'));

                yield $content;
            },
            $attributes
        );
    }

    /**
     * Render h1 heading
     */
    public function h1($content, array $attributes=null): Element
    {
        return $this->h(1, $content, $attributes);
    }

    /**
     * Render h2 heading
     */
    public function h2($content, array $attributes=null): Element
    {
        return $this->h(2, $content, $attributes);
    }

    /**
     * Render h3 heading
     */
    public function h3($content, array $attributes=null): Element
    {
        return $this->h(3, $content, $attributes);
    }

    /**
     * Render h4 heading
     */
    public function h4($content, array $attributes=null): Element
    {
        return $this->h(4, $content, $attributes);
    }

    /**
     * Render h5 heading
     */
    public function h5($content, array $attributes=null): Element
    {
        return $this->h(5, $content, $attributes);
    }

    /**
     * Render h6 heading
     */
    public function h6($content, array $attributes=null): Element
    {
        return $this->h(6, $content, $attributes);
    }

    /**
     * Render heading
     */
    public function h(int $size, $content, array $attributes=null): Element
    {
        return Html::{'h'.$size.'.heading'}($content, $attributes)
            ->addStyles($this->getStylesFor('h'.$size, 'heading'));
    }


    /**
     * Render paragraph
     */
    public function p($content, array $attributes=null): Element
    {
        return Html::p($content, $attributes)
            ->addStyles($this->getStylesFor('p', 'text'));
    }

    /**
     * Render link
     */
    public function link(string $url, $content, array $attributes=null): Element
    {
        return Html::a($content, $attributes)
            ->addStyles($this->getStylesFor('link'))
            ->setAttribute('href', $url)
            ->setAttribute('target', '_blank');
    }

    /**
     * Render image
     */
    public function image(string $url, int $width, int $height, ?string $alt=null): Element
    {
        return Html::img(null, [
            'src' => $url,
            'width' => $width,
            'height' => $height,
            'alt' => $alt
        ])->addStyles(
            $this->getStylesFor('image')
        )->addClass('image');
    }

    /**
     * Render foot block
     */
    public function footer($content, array $attributes=null): Element
    {
        return Html::{'div.clearContent'}(
            $this->container(
                function ($el) use ($content) {
                    $el->addClass('footer');
                    $el->addStyles($this->getStylesFor('footer', 'text'));

                    yield $content;
                },
                $attributes
            )
        )->addStyles(
            $this->getStylesFor('clearContent')
        );
    }


    /**
     * Container table
     */
    public function container($content, array $attributes=null): Element
    {
        return Html::{'table'}([
            Html::{'tbody > tr'}(function () use ($content, $attributes) {
                $styles = $attributes['style'] ?? [];
                return Html::{'td.container'}($content, $attributes)
                    ->setStyle('vertical-align', 'top')
                    ->addStyles($this->getStylesFor('text'))
                    ->addStyles($styles);
            })
        ], [
            'border' => '0',
            'cellpadding' => '0',
            'cellspacing' => '0',
            'style' => $this->getStylesFor('container')
        ]);
    }


    /**
     * Merge styles for tag
     */
    public function getStylesFor(string ...$tags): StyleList
    {
        $output = new StyleList();

        foreach (array_reverse($tags) as $tag) {
            if (null !== ($styles = $this->styles->get($tag))) {
                $output->merge($styles);
            }
        }

        return $output;
    }


    const STYLES = [
        'text' => [
            'font-size' => '14px',
            'font-family' => '-apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Ubuntu, sans-serif',
            'color' => '#444444',
            'line-height' => '1.4'
        ],
        'body' => [
            'background-color' => '#f3f3f3',
            '-webkit-text-size-adjust' => '100%',
            '-ms-text-size-adjust' => '100%',
            'min-width' => '100% !important',
            'width' => '100% !important',
            'margin' => '0',
            'padding' => '0',
            'border' => '0'
        ],
        'bodyContainer' => [
            'display' => 'block',
            'margin' => '0 auto',
            'padding' => '10px 0'
        ],
        'content' => [
            'box-sizing' => 'border-box',
            'display' => 'block',
            'margin' => '0 auto',
            'padding' => '10px',
            'width' => '600px'
        ],
        'previewText' => [
            'color' => 'transparent',
            'display' => 'none',
            'height' => '0',
            'max-height' => '0',
            'max-width' => '0',
            'opacity' => '0',
            'overflow' => 'hidden',
            'mso-hide' => 'all',
            'visibility' => 'hidden',
            'width' => '0'
        ],
        'contentArea' => [
            'background-color' => '#ffffff',
            'box-shadow' => '0 0 3px #DDDDDD',
            'border-radius' => '5px'
        ],
        'banner' => [
            'padding' => '0 0 15px 0'
        ],
        'section' => [
            'padding' => '15px'
        ],
        'heading' => [
            'margin' => '0 0 8px',
            'font-weight' => 'bold',
            'line-height' => '1.2'
        ],
        'h1' => [
            'font-size' => '24px'
        ],
        'h2' => [
            'font-size' => '21px'
        ],
        'h3' => [
            'font-size' => '17px'
        ],
        'h4' => [
            'font-size' => '15px'
        ],
        'h5' => [
            'font-size' => '14px'
        ],
        'h6' => [
            'font-size' => '14px'
        ],
        'p' => [
            'margin' => '0 0 15px'
        ],
        'link' => [
            'color' => '#3680C8'
        ],
        'image' => [
            'max-width' => '100%',
            'border' => 'none',
            'display' => 'block'
        ],
        'footer' => [
            'color' => '#999999',
            'text-align' => 'center',
            'font-size' => '12px',
            'padding' => '0 15px'
        ],
        'clearContent' => [
            'clear' => 'both',
            'padding-top' => '15px',
            'width' => '100%'
        ],
        'container' => [
            'border-collapse' => 'separate',
            'mso-table-lspace' => '0pt',
            'mso-table-rspace' => '0pt',
            'width' => '100%'
        ]
    ];

    const MOBILE_STYLES = [
        'table[class=body] .bodyContainer, table[class=body] .content' => [
            'width' => '100% !important'
        ],
        'table[class=body] .content' => [
            'padding' => '15px 0 !important'
        ],
        'table[class=body] .contentArea' => [
            'border-radius' => '0 !important'
        ],
        'table[class=body] p, table[class=body] ul, table[class=body] ol, table[class=body] td, table[class=body] span, table[class=body] a' => [
            'font-size' => '16px !important'
        ],
        'table[class=body] .image' => [
            'height' => 'auto !important',
            'max-width' => '600px !important',
            'width' => '100% !important'
        ],
        'table[class=body] .footer' => [
            'font-size' => '14px !important'
        ]
    ];
}
