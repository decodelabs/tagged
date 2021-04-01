<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged;

use DecodeLabs\Elementary\Element as ElementInterface;
use DecodeLabs\Elementary\ElementTrait as ElementTrait;
use DecodeLabs\Glitch\Proxy as Glitch;

use IteratorAggregate;
use Throwable;

class Element extends Tag implements IteratorAggregate, ElementInterface
{
    use ElementTrait;

    /**
     * Apply nested by string name
     */
    public static function create(string $name, $content = null, array $attributes = null): Element
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
     * Render to string
     */
    public function __toString(): string
    {
        try {
            return (string)$this->renderWith($this->renderContent());
        } catch (Throwable $e) {
            Glitch::logException($e);
            $message = '<strong>' . $e->getMessage() . '</strong>';

            if (!Glitch::isProduction()) {
                $message .= '<br /><samp>' . Glitch::normalizePath($e->getFile()) . '</samp> : <samp>' . $e->getLine() . '</samp>';
                $title = $this->esc((string)$e);
            } else {
                $title = 'HTML Error';
            }

            return '<div class="error" style="color: red; background: white; padding: 0.5rem;" title="' . $title . '">' . $message . '</div>';
        }
    }
}
