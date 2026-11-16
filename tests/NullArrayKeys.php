<?php

/**
 * Tagged
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Tests\NullArrayKeys;

require dirname(__DIR__) . '/vendor/autoload.php';

error_reporting(E_ALL);
set_error_handler(static function (int $severity, string $message, string $file, int $line): never {
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

function expectSame(mixed $expected, mixed $actual): void
{
    if ($expected !== $actual) {
        throw new \RuntimeException('Unexpected null-key regression result: ' . var_export($actual, true));
    }
}

try {
    /** @return \Generator<int|string|null,string> */
    function keyedValues(): \Generator
    {
        yield null => 'first';
        yield '' => 'last';
        yield '0' => 'zero';
        yield 'named' => 'value';
    }

    $tag = new \DecodeLabs\Tagged\Tag('div');
    $tag->setAttribute('data-json', keyedValues());
    expectSame('{"":"last","0":"zero","named":"value"}', $tag->getAttribute('data-json'));
    (new \ReflectionMethod($tag, 'setDataAttributes'))->invoke($tag, keyedValues());
    expectSame('last', $tag->getDataAttribute(''));
    expectSame('zero', $tag->getDataAttribute('0'));
    foreach ([
        (new \ReflectionMethod(\DecodeLabs\Tagged::class, 'tag'))->invoke(null, 'div', keyedValues()),
        (new \ReflectionMethod(\DecodeLabs\Tagged\Element::class, 'create'))->invoke(null, 'div', 'content', keyedValues()),
    ] as $element) {
        if (!$element instanceof \DecodeLabs\Tagged\Tag) {
            throw new \RuntimeException('Expected a Tag');
        }
        expectSame('last', $element->getAttribute(''));
        expectSame('zero', $element->getAttribute('0'));
    }
} finally {
    restore_error_handler();
}

echo "Null array-key regression checks passed.\n";
