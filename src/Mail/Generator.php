<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Mail;

use DecodeLabs\Elementary\Style\Collection as StyleList;
use DecodeLabs\Elementary\Style\Sheet as StyleSheet;
use DecodeLabs\Tagged as Html;
use DecodeLabs\Tagged\Element;
use DecodeLabs\Tagged\Markup;

class Generator
{
    public StyleSheet $styles;
    public StyleSheet $mobileStyles;

    /**
     * Init with default collections
     */
    public function __construct()
    {
        $this->styles = new StyleSheet(static::STYLES);
        $this->mobileStyles = new StyleSheet(static::MOBILE_STYLES);
    }

    /**
     * Generate document
     *
     * @param array<string, mixed>|null $bodyAttributes
     */
    public function document(
        string $subject,
        mixed $content,
        ?array $bodyAttributes = null
    ): Markup {
        $output =
            '<!doctype html>' . "\n" .
            '<html>' . "\n" .
            '<head>' . "\n" .
            '    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . "\n" .
            '    <meta name="viewport" content="width=device-width" />' . "\n" .
            '    <meta name="robots" content="noindex, nofollow" />' . "\n" .
            '    <meta name="googlebot" content="noindex, nofollow, noarchive" />' . "\n" .
            '    ' . Html::{'title'}($subject) . "\n" .
            '    ' . $this->css() . "\n" .
            '</head>' . "\n" .
            $this->body($content, $bodyAttributes) .
            '</html>';

        return Html::raw($output);
    }

    /**
     * Render css
     */
    public function css(): Element
    {
        $width = null;

        if ($content = $this->styles->get('content')) {
            $width = $content->get('width');
        }

        if ($width === null) {
            $width = '600px';
        }

        $css = "\n" . '@media only screen and (max-width: ' . $width . ') {' . "\n    " . $this->mobileStyles->renderBlocks() . "\n" . '}' . "\n";

        return Html::{'style'}(Html::raw($css), [
            'type' => 'text/css'
        ]);
    }

    /**
     * Render body tag
     *
     * @param array<string, mixed>|null $tagStyles
     * @param array<string, mixed>|null $attributes
     */
    public function body(
        mixed $content,
        ?array $tagStyles = null,
        ?array $attributes = null
    ): Element {
        $styles = $this->getStylesFor('body', 'text');

        return Html::{'body.email'}(
            function () use ($content, $styles) {
                $output = $this->container(function ($el) use ($content) {
                    $containerStyles = $this->getStylesFor('bodyContainer');
                    $contentStyles = $this->getStylesFor('content');
                    $width = $contentStyles->get('width');

                    if ($width !== null) {
                        $containerStyles->set('max-width', $width);
                        $containerStyles->set('width', $width);
                        $contentStyles->set('max-width', $width);
                    }

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
            $tagStyles,
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
     *
     * @param array<string, mixed>|null $tagStyles
     * @param array<string, mixed>|null $attributes
     */
    public function contentArea(
        mixed $content,
        ?array $tagStyles = null,
        ?array $attributes = null
    ): Element {
        $styles = $this->getStylesFor('contentArea');

        return $this->container(
            function ($el) use ($content, $styles) {
                $el->addClass('contentArea');
                $el->addStyles($styles);
                yield $content;
            },
            $tagStyles,
            $attributes
        );
    }

    /**
     * Render banner
     */
    public function banner(
        string $url,
        int $width,
        int $height,
        ?string $alt = null
    ): Element {
        return $this->container(function ($el) use ($url, $width, $height, $alt) {
            $el->addClass('banner');
            $el->addStyles($this->getStylesFor('banner'));

            return $this->image($url, $width, $height, $alt ?? 'Banner');
        });
    }

    /**
     * Render section block
     *
     * @param array<string, mixed>|null $tagStyles
     * @param array<string, mixed>|null $attributes
     */
    public function section(
        mixed $content,
        ?array $tagStyles = null,
        ?array $attributes = null
    ): Element {
        return $this->container(
            function ($el) use ($content) {
                $el->addClass('section');
                $el->addStyles($this->getStylesFor('section'));

                yield $content;
            },
            $tagStyles,
            $attributes
        );
    }

    /**
     * Render h1 heading
     *
     * @param array<string, mixed>|null $tagStyles
     * @param array<string, mixed>|null $attributes
     */
    public function h1(
        mixed $content,
        ?array $tagStyles = null,
        ?array $attributes = null
    ): Element {
        return $this->h(1, $content, $tagStyles, $attributes);
    }

    /**
     * Render h2 heading
     *
     * @param array<string, mixed>|null $tagStyles
     * @param array<string, mixed>|null $attributes
     */
    public function h2(
        mixed $content,
        ?array $tagStyles = null,
        ?array $attributes = null
    ): Element {
        return $this->h(2, $content, $tagStyles, $attributes);
    }

    /**
     * Render h3 heading
     *
     * @param array<string, mixed>|null $tagStyles
     * @param array<string, mixed>|null $attributes
     */
    public function h3(
        mixed $content,
        ?array $tagStyles = null,
        ?array $attributes = null
    ): Element {
        return $this->h(3, $content, $tagStyles, $attributes);
    }

    /**
     * Render h4 heading
     *
     * @param array<string, mixed>|null $tagStyles
     * @param array<string, mixed>|null $attributes
     */
    public function h4(
        mixed $content,
        ?array $tagStyles = null,
        ?array $attributes = null
    ): Element {
        return $this->h(4, $content, $tagStyles, $attributes);
    }

    /**
     * Render h5 heading
     *
     * @param array<string, mixed>|null $tagStyles
     * @param array<string, mixed>|null $attributes
     */
    public function h5(
        mixed $content,
        ?array $tagStyles = null,
        ?array $attributes = null
    ): Element {
        return $this->h(5, $content, $tagStyles, $attributes);
    }

    /**
     * Render h6 heading
     *
     * @param array<string, mixed>|null $tagStyles
     * @param array<string, mixed>|null $attributes
     */
    public function h6(
        mixed $content,
        ?array $tagStyles = null,
        ?array $attributes = null
    ): Element {
        return $this->h(6, $content, $tagStyles, $attributes);
    }

    /**
     * Render heading
     *
     * @param array<string, mixed>|null $tagStyles
     * @param array<string, mixed>|null $attributes
     */
    public function h(
        int $size,
        mixed $content,
        ?array $tagStyles = null,
        ?array $attributes = null
    ): Element {
        return Html::{'h' . $size . '.heading'}($content, $attributes)
            ->addStyles($this->getStylesFor('h' . $size, 'heading'))
            ->addStyles((array)$tagStyles);
    }


    /**
     * Render paragraph
     *
     * @param array<string, mixed>|null $tagStyles
     * @param array<string, mixed>|null $attributes
     */
    public function p(
        mixed $content,
        ?array $tagStyles = null,
        ?array $attributes = null
    ): Element {
        return Html::{'p'}($content, $attributes)
            ->addStyles($this->getStylesFor('p'))
            ->addStyles((array)$tagStyles);
    }

    /**
     * Render link
     *
     * @param array<string, mixed>|null $tagStyles
     * @param array<string, mixed>|null $attributes
     */
    public function link(
        string $url,
        mixed $content,
        ?array $tagStyles = null,
        ?array $attributes = null
    ): Element {
        return Html::{'a'}($content, $attributes)
            ->setAttribute('href', $url)
            ->setAttribute('target', '_blank')
            ->addStyles($this->getStylesFor('link'))
            ->addStyles((array)$tagStyles);
    }

    /**
     * Render image
     *
     * @param array<string, mixed>|null $tagStyles
     * @param array<string, mixed>|null $attributes
     */
    public function image(
        string $url,
        int $width,
        int $height,
        ?string $alt = null,
        ?array $tagStyles = null,
        ?array $attributes = null
    ): Element {
        return Html::{'img'}(null, [
            'src' => $url,
            'width' => $width,
            'height' => $height,
            'alt' => $alt
        ])->setAttributes(
            (array)$attributes
        )->addStyles(
            $this->getStylesFor('image')
        )->addStyles(
            (array)$tagStyles
        )->addClass('image');
    }


    /**
     * Render card element
     *
     * @param array<string, mixed>|null $tagStyles
     * @param array<string, mixed>|null $attributes
     */
    public function card(
        mixed $content,
        array $tagStyles = null,
        array $attributes = null
    ): Element {
        $output = $this->container(
            function ($el) use ($content) {
                $el->addClass('card');
                $el->addStyles($this->getStylesFor('card'));

                yield $content;
            },
            $tagStyles,
            $attributes
        );

        $output->setStyle('margin-bottom', '20px');
        return $output;
    }

    /**
     * Render list of columns
     */
    public function columns(mixed ...$contents): Element
    {
        return Html::{'table.columns'}([
            Html::{'tbody > tr'}(function () use ($contents) {
                foreach ($contents as $content) {
                    yield Html::{'td.container'}($content)
                        ->setStyle('vertical-align', 'top')
                        ->addStyles($this->getStylesFor('text'));
                }
            })
        ], [
            'border' => '0',
            'cellpadding' => '0',
            'cellspacing' => '0',
            'style' => $this->getStylesFor('container')
        ]);
    }

    /**
     * Render list of rows
     */
    public function rows(mixed ...$contents): Element
    {
        return Html::{'table.rows'}([
            Html::{'tbody'}(function () use ($contents) {
                foreach ($contents as $content) {
                    yield Html::{'tr'}(
                        Html::{'td.container'}($content)
                            ->setStyle('vertical-align', 'top')
                            ->addStyles($this->getStylesFor('text'))
                    );
                }
            })
        ], [
            'border' => '0',
            'cellpadding' => '0',
            'cellspacing' => '0',
            'style' => $this->getStylesFor('container')
        ]);
    }

    /**
     * Render container with gutter columns
     *
     * @param array<string, mixed>|null $tagStyles
     * @param array<string, mixed>|null $attributes
     */
    public function gutter(
        string $width,
        mixed $content,
        ?array $tagStyles = null,
        ?array $attributes = null
    ): Element {
        return Html::{'table'}([
            Html::{'tbody > tr'}(function () use ($content, $width, $tagStyles, $attributes) {
                yield Html::{'td.gutter'}('')
                    ->setStyle('width', $width);

                yield Html::{'td.container'}($content, $attributes)
                    ->setStyle('vertical-align', 'top')
                    ->addStyles($this->getStylesFor('text'))
                    ->addStyles((array)$tagStyles);

                yield Html::{'td.gutter'}('')
                    ->setStyle('width', $width);
            })
        ], [
            'border' => '0',
            'cellpadding' => '0',
            'cellspacing' => '0',
            'style' => $this->getStylesFor('container')
        ]);
    }


    /**
     * Render smallprint element
     *
     * @param array<string, mixed>|null $tagStyles
     * @param array<string, mixed>|null $attributes
     */
    public function smallprint(
        mixed $content,
        ?array $tagStyles = null,
        ?array $attributes = null
    ): Element {
        $output = $this->container(
            function ($el) use ($content) {
                $el->addClass('smallprint');
                $el->addStyles($this->getStylesFor('smallprint'));

                yield $content;
            },
            $tagStyles,
            $attributes
        );

        $output->setStyle('margin-bottom', '20px');
        return $output;
    }


    /**
     * Render foot block
     *
     * @param array<string, mixed>|null $tagStyles
     * @param array<string, mixed>|null $attributes
     */
    public function footer(
        mixed $content,
        ?array $tagStyles = null,
        ?array $attributes = null
    ): Element {
        return Html::{'div.clearContent'}(
            $this->container(
                function ($el) use ($content) {
                    $el->addClass('footer');
                    $el->addStyles($this->getStylesFor('footer', 'text'));

                    yield $content;
                },
                $tagStyles,
                $attributes
            )
        )->addStyles(
            $this->getStylesFor('clearContent')
        );
    }


    /**
     * Container table
     *
     * @param array<string, mixed>|null $tagStyles
     * @param array<string, mixed>|null $attributes
     */
    public function container(
        mixed $content,
        ?array $tagStyles = null,
        ?array $attributes = null
    ): Element {
        return Html::{'table'}([
            Html::{'tbody > tr'}(function () use ($content, $tagStyles, $attributes) {
                return Html::{'td.container'}($content, $attributes)
                    ->setStyle('vertical-align', 'top')
                    ->addStyles($this->getStylesFor('text'))
                    ->addStyles((array)$tagStyles);
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


    public const STYLES = [
        'text' => [
            'font-size' => '15px',
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
            'border-radius' => '5px',
            'overflow' => 'hidden'
        ],
        'banner' => [
            'padding' => '0 0 15px 0'
        ],
        'section' => [
            'padding' => '20px 20px 10px'
        ],
        'heading' => [
            'margin' => '0 0 8px',
            'font-weight' => 'bold',
            'line-height' => '1.2'
        ],
        'h1' => [
            'font-weight' => '200',
            'font-size' => '28px',
            'margin-bottom' => '20px'
        ],
        'h2' => [
            'font-size' => '25px'
        ],
        'h3' => [
            'font-weight' => '300',
            'font-size' => '22px',
            'margin-bottom' => '15px'
        ],
        'h4' => [
            'font-weight' => '300',
            'font-size' => '20px',
            'margin-bottom' => '15px'
        ],
        'h5' => [
            'color' => '#AAAAAA',
            'font-weight' => 'bold',
            'font-size' => '12px',
            'margin-bottom' => '5px',
            'text-transform' => 'uppercase'
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
        'card' => [
            'padding' => '20px',
            'background' => '#F5F5F5',
            'border-radius' => '4px'
        ],
        'smallprint' => [
            'border-top' => '1px #EEEEEE solid',
            'padding' => '20px 0 20px',
            'color' => '#CCCCCC',
            'font-size' => '12px'
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

    public const MOBILE_STYLES = [
        'table[class=body] .bodyContainer, table[class=body] .content' => [
            'width' => '100% !important'
        ],
        'table[class=body] .content' => [
            'padding' => '15px 0 !important'
        ],
        'table[class=body] .contentArea' => [
            'border-radius' => '0 !important'
        ],
        'table[class=body] .image' => [
            'height' => 'auto !important',
            'max-width' => '600px !important',
            'width' => '100% !important'
        ],
        'table[class=body] table.columns.collapse > tbody > tr > td' => [
            'display' => 'block !important',
            'margin-bottom' => '20px',
            'width' => 'auto !important',
            'text-align' => 'left !important'
        ],
        'table[class=body] .gutter' => [
            'width' => '0 !important'
        ],
        'table[class=body] .footer' => [
            'font-size' => '14px !important'
        ]
    ];
}
