<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Tagged\Html;

use DecodeLabs\Tagged\Markup;
use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\Builder\Element as ElementInterface;

use DecodeLabs\Collections\Sequence;
use DecodeLabs\Collections\Native\SequenceTrait;

use DecodeLabs\Glitch;
use DecodeLabs\Glitch\Inspectable;
use DecodeLabs\Glitch\Dumper\Entity;
use DecodeLabs\Glitch\Dumper\Inspector;

class Element extends Tag implements \IteratorAggregate, ElementInterface
{
    const MUTABLE = true;

    use SequenceTrait;

    /**
     * Apply nested by string name
     */
    public static function create(string $name, $content=null, array $attributes=null): Element
    {
        if (false !== strpos($name, '>')) {
            $parts = explode('>', $name);

            foreach (array_reverse($parts) as $name) {
                $content = new self(trim($name), $content, $attributes);
                $attributes = null;
            }

            return $content;
        }

        return new self($name, $content, $attributes);
    }

    /**
     * Init with name, content and attributes
     */
    public function __construct(string $name, $content, array $attributes=null)
    {
        parent::__construct($name, $attributes);

        if (!is_iterable($content) || $content instanceof Markup) {
            $content = $content === null ? [] : [$content];
        }

        $this->merge($content);
    }

    /**
     * Render to string
     */
    public function __toString(): string
    {
        try {
            return (string)$this->renderWith($this->renderContent());
        } catch (\Throwable $e) {
            Glitch::logException($e);
            $message = '<strong>'.$e->getMessage().'</strong>';

            if (!Glitch::isProduction()) {
                $message .= '<br /><samp>'.Glitch::normalizePath($e->getFile()).'</samp> : <samp>'.$e->getLine().'</samp>';
                $title = $this->esc((string)$e);
            } else {
                $title = 'HTML Error';
            }

            return '<div class="error" style="color: red; background: white; padding: 0.5rem;" title="'.$title.'">'.$message.'</div>';
        }
    }

    /**
     * Render to more readable string (for dump)
     */
    public function render(bool $pretty=false): ?string
    {
        if (null === ($output = $this->renderWith($this->renderContent($pretty), $pretty))) {
            return null;
        }

        return (string)$output;
    }

    /**
     * Render inner content
     */
    public function renderContent(bool $pretty=false): ?Buffer
    {
        $output = '';

        foreach ($this->items as $value) {
            if (empty($value) && $value != '0') {
                continue;
            }

            $output .= $this->renderChild($value, $pretty);
        }

        if (empty($output) && $output != '0') {
            return null;
        }

        return new Buffer($output);
    }

    /**
     * Replace all content with new body
     */
    public function setBody($body): ElementInterface
    {
        $this->clear();
        $this->append($body);
        return $this;
    }


    /**
     * Inspect for Glitch
     */
    public function glitchInspect(Entity $entity, Inspector $inspector): void
    {
        $renderEmpty = $this->renderEmpty;
        $this->renderEmpty = true;
        $def = (string)$this->render(true);
        $this->renderEmpty = $renderEmpty;

        if (!$renderEmpty) {
            $def = '<?'.substr($def, 1);
        }

        $entity
            ->setClassName($this->name)
            ->setDefinition($def)
            ->setProperties([
                //'*name' => $inspector($this->name),
                '*renderEmpty' => $inspector($this->renderEmpty),
                '*attributes' => $inspector($this->attributes),
            ])
            ->setValues($inspector->inspectList($this->items))
            ->hideSection('properties')
            ->hideSection('values');
    }
}
