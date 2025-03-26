<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged;

use DecodeLabs\Elementary\Attribute\ClassList;
use DecodeLabs\Elementary\Attribute\ClassList\Container as ClassListContainer;
use DecodeLabs\Elementary\Renderable;
use DecodeLabs\Elementary\Style\Collection as StyleCollection;
use DecodeLabs\Elementary\Style\Container as StyleContainer;
use DecodeLabs\Elementary\Tag;
use Stringable;

/**
 * @phpstan-type TAttributeValue = string|int|float|bool|iterable<mixed>|ClassList|StyleCollection|Buffer
 * @phpstan-type TAttributeInput = mixed
 * @extends Renderable<Buffer>
 * @extends Tag<TAttributeValue,TAttributeInput>
 */
interface Component extends
    ClassListContainer,
    Renderable,
    StyleContainer,
    Stringable,
    Tag
{
}
