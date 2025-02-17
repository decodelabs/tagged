<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged;

use DecodeLabs\Collections\AttributeContainer;

/**
 * @phpstan-import-type TAttributeValue from Tag
 * @phpstan-import-type TAttributeInput from Tag
 * @extends AttributeContainer<TAttributeValue,TAttributeInput>
 */
interface Asset extends AttributeContainer
{
    public function getPriority(): int;
    public function render(): Element;
}
