<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);

/**
 * global helpers
 */
namespace
{
    use DecodeLabs\Tagged\Markup;
    use DecodeLabs\Tagged\Html\Element;

    function html(string $name, $content, array $attributes=null): Markup
    {
        return new Element($name, $content, $attributes);
    }
}


/**
 * global helpers
 */
namespace html
{
    use DecodeLabs\Tagged\Markup;
    use DecodeLabs\Tagged\Buffer;
    use DecodeLabs\Tagged\Html\Tag;
    use DecodeLabs\Tagged\Html\Element;

    function tag(string $name, array $attributes=null): Markup
    {
        return new Tag($name, $attributes);
    }

    function el(string $name, $content, array $attributes=null): Markup
    {
        return new Element($name, $content, $attributes);
    }

    function wrap(string $html): Markup
    {
        return new Buffer($html);
    }
}
