<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged;

use DecodeLabs\Glitch\Inspectable;
use DecodeLabs\Glitch\Dumper\Entity;
use DecodeLabs\Glitch\Dumper\Inspector;

use DecodeLabs\Collections\AttributeContainerTrait;

trait TagTrait
{
    use AttributeContainerTrait;
    use ChildRendererTrait;

    // const BOOLEAN_ATTRIBUTES = [];
    // const INLINE_TAGS = [];

    protected $name;
    protected $closable = true;
    protected $renderEmpty = true;


    /**
     * Init with name and attributes
     */
    public function __construct(string $name, array $attributes=null)
    {
        $this->setName($name);

        if ($attributes !== null) {
            $this->setAttributes($attributes);
        }
    }


    /**
     * Parse css style selector into tag name, classes, etc
     */
    public function setName(string $name): Tag
    {
        if (false !== strpos($name, '[')) {
            $name = preg_replace_callback('/\[([^\]]*)\]/', function ($res) {
                $parts = explode('=', $res[1], 2);
                $key = array_shift($parts);
                $value = array_shift($parts);
                $first = substr($value, 0, 1);
                $last = substr($value, -1);

                if (strlen($value) > 1
                && (($first == '"' && $last == '"')
                || ($first == "'" && $last == "'"))) {
                    $value = substr($value, 1, -1);
                }

                $this->setAttribute($key, $value);
                return '';
            }, $name);
        }

        if (false !== strpos($name, '#')) {
            $name = preg_replace_callback('/\#([^ .\[\]]+)/', function ($res) {
                $this->setId($res[1]);
                return '';
            }, $name);
        }

        $parts = explode('.', $name);
        $this->name = array_shift($parts);

        if (false !== ($pos = strpos($this->name, '?'))) {
            $this->name = str_replace('?', '', $this->name);
            $this->renderEmpty = false;
        }

        if (!empty($parts)) {
            if ($this instanceof ClassListContainer) {
                $this->addClasses(...$parts);
            } else {
                $this->setAttribute('class', implode(' ', $parts));
            }
        }

        return $this;
    }

    /**
     * Get tag name
     */
    public function getName(): string
    {
        return $this->name;
    }


    /**
     * Direct set id attribute
     */
    public function setId(?string $id): Tag
    {
        if ($id === null) {
            $this->removeAttribute('id');
            return $this;
        }

        if (preg_match('/\s/', $id)) {
            throw \Glitch::EInvalidArgument('Invalid tag id: '.$id);
        }

        $this->setAttribute('id', $id);
        return $this;
    }

    /**
     * Get id attribute value
     */
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }



    /**
     * Is this element inline?
     */
    public function isInline(): bool
    {
        return in_array(strtolower($this->name), self::INLINE_TAGS);
    }

    /**
     * Is this a block element?
     */
    public function isBlock(): bool
    {
        return !$this->isInline();
    }


    /**
     * Render tag with inner content
     */
    public function renderWith($content=null): ?Markup
    {
        if ($this->closable && $content !== null) {
            $content = $this->renderChild($content);
        } elseif (!$this->closable && $this->renderEmpty) {
            return null;
        } else {
            $content = null;
        }

        return new Buffer($this->open().$content.$this->close());
    }




    /**
     * Set whether to render tag if no content
     */
    public function setRenderEmpty(bool $render): Tag
    {
        $this->renderEmpty = $render;
        return $this;
    }

    /**
     * Render tag if no content?
     */
    public function willRenderEmpty(): bool
    {
        return $this->renderEmpty;
    }


    /**
     * Create opening tag string
     */
    public function open(): string
    {
        $attributes = [];

        foreach ($this->attributes as $key => $value) {
            if ($value === null) {
                $attributes[] = $key;
            } elseif (is_bool($value)) {
                if (substr($key, 0, 5) == 'data-' || in_array($key, static::BOOLEAN_ATTRIBUTES)) {
                    $attributes[] = $key.'="'.($value ? 'true' : 'false').'"';
                } else {
                    if ($value) {
                        $attributes[] = $key;
                    } else {
                        continue;
                    }
                }
            } elseif (is_array($value) || is_callable($value)) {
                $attributes[] = $key.'="'.(string)$this->renderChild($value).'"';
            } elseif ($value instanceof Markup) {
                $attributes[] = $key.'="'.(string)$value.'"';
            } else {
                $attributes[] = $key.'="'.$this->esc((string)$value).'"';
            }
        }

        if ($attributes = implode(' ', $attributes)) {
            $attributes = ' '.$attributes;
        }

        $output = '<'.$this->name.$attributes;

        if (!$this->closable) {
            $output .= ' /';
        }

        $output .= '>';
        return $output;
    }

    /**
     * Render closing </tag>
     */
    public function close(): string
    {
        if (!$this->closable) {
            return '';
        }

        return '</'.$this->name.'>';
    }


    /**
     * Render to string
     */
    public function __toString(): string
    {
        return $this->open();
    }

    abstract protected function esc(?string $value): ?string;


    /**
     * Inspect for Glitch
     */
    public function glitchInspect(Entity $entity, Inspector $inspector): void
    {
        $output = $this->__toString();

        if (!$this->renderEmpty) {
            $output = '<?'.substr($output, 1);
        }

        $entity
            ->setText($output)
            ->setProperties([
                '*name' => $inspector($this->name),
                '*renderEmpty' => $inspector($this->renderEmpty),
                '*attributes' => $inspector($this->attributes),
            ])
            ->hideSection('properties');
    }
}
