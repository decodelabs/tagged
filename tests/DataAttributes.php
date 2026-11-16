<?php

/**
 * Tagged
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Tests;

use ArrayIterator;
use DecodeLabs\Tagged\Tag;
use RuntimeException;

require dirname(__DIR__) . '/vendor/autoload.php';

$sources = [
    ['shared' => 'first', 'zero' => 0],
    new ArrayIterator(['shared' => 'first', 'zero' => 0]),
    (static function () {
        yield 'shared' => 'first';
        yield 'zero' => 0;
    })()
];

foreach ($sources as $source) {
    $tag = new Tag('div');
    $tag->setDataAttributes($source, shared: 'second', extra: 'named');

    if (
        $tag->getDataAttribute('shared') !== 'first' ||
        $tag->getDataAttribute('zero') !== '0' ||
        $tag->getDataAttribute('extra') !== 'named'
    ) {
        throw new RuntimeException('Data attributes must accept iterables and preserve input precedence');
    }
}

$tag = new Tag('div');
$tag->setDataAttributes([], 'numbered');

if ($tag->getDataAttribute('0') !== 'numbered') {
    throw new RuntimeException('Numeric variadic keys must be converted to attribute names');
}

echo "Data attribute regression checks passed.\n";
