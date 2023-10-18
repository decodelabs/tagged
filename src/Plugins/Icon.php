<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Plugins;

use DecodeLabs\Exceptional;
use DecodeLabs\Tagged\Element;
use DecodeLabs\Tagged\Factory;

class Icon
{
    protected Factory $html;
    protected string $format = 'svg';
    protected ?string $svgReference = null;
    protected string $baseClass = 'icon';

    /**
     * Init with parent factory
     */
    public function __construct(Factory $html)
    {
        $this->html = $html;
    }

    /**
     * Set format mode
     *
     * @return $this
     */
    public function setFormat(string $format): static
    {
        switch ($format) {
            case 'svg':
            case 'font':
                $this->format = $format;
                break;

            default:
                throw Exceptional::InvalidArgument('Invalid icon format: ' . $format);
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
     *
     * @return $this
     */
    public function setSvgReference(?string $reference): static
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
     *
     * @return $this
     */
    public function setBaseClass(string $class): static
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
                return $this->html->el('svg.' . $this->baseClass . ' > /use', null, [
                    'xlink:href' => $this->svgReference . '#' . $name
                ]);

            case 'font':
                return $this->html->el('i.' . $this->baseClass . '.icon-' . $name);

            default:
                throw Exceptional::UnexpectedValue('Unsupported icon format: ' . $this->format);
        }
    }

    /**
     * Create icon with args
     *
     * @param array<string> $args
     */
    public function __call(
        string $name,
        array $args
    ): Element {
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
    public function yesNo(
        ?bool $value,
        bool $allowNull = true
    ): ?Element {
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
