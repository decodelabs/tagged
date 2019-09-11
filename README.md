# Tagged
PHP markup generation without the fuss.


## Installation
```bash
composer install decodelabs/tagged
```

## Setup

First, register the [Veneer](https://github.com/decodelabs/veneer) Facade:

```php
use DecodeLabs\Tagged\HtmlFactory;

HtmlFactory::registerFacade($myPsr11Container ?? null);
```

This allows access to the Html class under all contexts.

## Usage

Generate markup using a simple, flexible interface.

```php
echo Html::{'div.my-class#my-id'}('This is element content', [
    'title' => 'This is a title'
]);

/*
Creates -

<div class="my-class" id="my-id" title="This is a title">This is element content</div>
*/
```

Create individual tags without content:

```php
$tag = Html::tag('div.my-class');

echo $tag->open();
echo 'Content';
echo $tag->close();
```

Wrap HTML strings to be used where an instance of <code>Markup</code> is needed:

```php
$buffer = Html::raw('<span class="test">My span</span>');
```

Prepare arbitrary input for Markup output:

```php
$markup = Html::wrap(
    function() {
        yield Html::h1('My title');
    },
    [Html::p(['This is ', Html::strong('mixed'), ' content'])]
);
```


### Nesting

You can nest elements in multiple ways:

```php
// Pass in nested elements via array
echo Html::div([
    Html::{'span.inner1'}('Inner 1'),
    ' ',
    Html::{'span.inner2'}('Inner 2')
]);


// Return anything and everything via a generator
echo Html::div(function($el) {
    // $el is the root element
    $el->addClass('container');

    // Nest elements with a single call
    yield Html::{'header > h1'}('This is a header');
    yield Html::p('This is a paragraph');

    // Set attributes inline
    yield Html::{'p[data-target=open]'}('Target paragraph');
});
```


## Licensing
Glitch is licensed under the MIT License. See [LICENSE](./LICENSE) for the full license text.
