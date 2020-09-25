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

use DecodeLabs\Glitch;

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
    public function __get(string $name): Element
    {
        switch ($this->format) {
            case 'svg':
                return $this->html->el('svg.'.$this->baseClass.' > /use', null, [
                    'xlink:href' => $this->svgReference.'#'.$name
                ]);

            case 'font':
                return $this->html->el('i.'.$this->baseClass.'.icon-'.$name);

            default:
                throw Glitch::EUnexpectedValue('Unsupported icon format: '.$this->format);
        }
    }

    /**
     * Create icon with args
     */
    public function __call(string $name, array $args): Element
    {
        return $this->__get($name);
    }


    /**
     * Boolean icon
     */
    public function boolean(?bool $value): Element
    {
        $output = $this->__get($value ? 'tick' : 'cross');
        $output->addClass($value ? 'positive' : 'negative');
        return $output;
    }


    /**
     * Yes / no icon
     */
    public function yesNo(?bool $value, bool $allowNull=true): ?Element
    {
        if ($value === null && $allowNull) {
            return null;
        }

        $output = $this->__get($value ? 'yes' : 'no');
        $output->addClass($value ? 'positive' : 'negative');
        return $output;
    }


    /**
     * Locked / unlocked icon
     */
    public function locked(?bool $value): Element
    {
        $output = $this->__get($value ? 'lock' : 'unlock');
        $output->addClass($value ? 'locked' : 'unlocked');
        return $output;
    }
}
