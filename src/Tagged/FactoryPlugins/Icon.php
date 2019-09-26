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

use DecodeLabs\Tagged\Builder\Html\ContentCollection;
use DecodeLabs\Tagged\Builder\Html\Element;

class Icon implements FacadePlugin
{
    protected $html;
    protected $format = 'svg';
    protected $svgReference = null;
    protected $baseClass = 'icon';

    /**
     * Init with parent factory
     */
    public function __construct(HtmlFactory $html)
    {
        $this->html = $html;
    }

    /**
     * Set format mode
     */
    public function setFormat(string $format): Icon
    {
        switch ($format) {
            case 'svg':
            case 'font':
                $this->format = $format;
                break;

            default:
                throw Glitch::EInvalidArgument('Invalid icon format: '.$format);
        }

        return $this;
    }

    /**
     * Get format
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * Set SVG reference
     */
    public function setSvgReference(?string $reference): Icon
    {
        $this->svgReference = $reference;
        return $this;
    }

    /**
     * Get SVG reference
     */
    public function getSvgReference(): ?string
    {
        return $this->svgReference;
    }

    /**
     * Set base element class
     */
    public function setBaseClass(string $class): Icon
    {
        $this->baseClass = $class;
        return $this;
    }

    /**
     * Get base element class
     */
    public function getBaseClass(): string
    {
        return $this->baseClass;
    }

    /**
     * Create icon without args
     */
    public function __get(string $name): Markup
    {
        switch ($this->format) {
            case 'svg':
                return $this->html->el('svg.'.$this->baseClass.' > /use', null, [
                    'xlink:href' => $this->svgReference.'#'.$name
                ]);

            case 'font':
                return $this->html->el('i.'.$this->baseClass.'.icon-'.$name);
        }
    }

    /**
     * Create icon with args
     */
    public function __call(string $name, array $args): Markup
    {
        return $this->__get($name);
    }


    /**
     * Boolean icon
     */
    public function boolean(?bool $value): Markup
    {
        return $this->__get($value ? 'tick' : 'cross')
            ->addClass($value ? 'positive' : 'negative');
    }


    /**
     * Yes / no icon
     */
    public function yesNo(?bool $value, bool $allowNull=true): ?Markup
    {
        if ($value === null && $allowNull) {
            return null;
        }

        return $this->__get($value ? 'yes' : 'no')
            ->addClass($value ? 'positive' : 'negative');
    }


    /**
     * Locked / unlocked icon
     */
    public function locked(?bool $value): Markup
    {
        return $this->__get($value ? 'lock' : 'unlock')
            ->addClass($value ? 'locked' : 'unlocked');
    }
}
