<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged\Tests;

use DecodeLabs\Tagged as Html;
use DecodeLabs\Tagged\Component\Inline;
use DecodeLabs\Tagged\Component\Ul;
use DecodeLabs\Tagged\Element;

class AnalyzeFactoryCall {


    public function getElement(): Element {
        $output = Html::div('Hello World', [
            'class' => 'test',
            ':array' => [
                'data-test' => 'true'
            ],
            'style' => [
                'color' => 'red'
            ],
            'closure' => function() {
                yield 'nice';
                return 'inner';
            }
        ]);

        $output['offset'] = 'value';
        return $output;
    }

    public function getInlineList(): Inline {
        return Html::{'@inline'}([
            'Item 1',
            'Item 2',
            'Item 3'
        ]);
    }

    public function getList(): Ul {
        return Html::{'@ul'}([
            'Item 1',
            'Item 2',
            'Item 3'
        ]);
    }
}

$obj = new AnalyzeFactoryCall();
$obj->getList();
