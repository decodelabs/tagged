<?php
/**
 * This file is part of the Tagged package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);

namespace
{
    use DecodeLabs\Tagged\Markup;
    use DecodeLabs\Tagged\Factory;
    use DecodeLabs\Tagged\Builder\Html\Element;
    use DecodeLabs\Tagged\Builder\Html\ContentCollection;

    function Html($name=null, $content=null, array $attributes=null): Markup
    {
        if (is_string($name)) {
            return Element::create($name, $content, $attributes);
        } elseif ($name === null && $content === null && $attributes === null) {
            return Factory::getDefault();
        } elseif (is_callable($name)) {
            return ContentCollection::normalize($name(Factory::getDefault()));
        } else {
            return ContentCollection::normalize($name);
        }
    }
}
