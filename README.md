# Tagged

[![PHP from Packagist](https://img.shields.io/packagist/php-v/decodelabs/tagged?style=flat)](https://packagist.org/packages/decodelabs/tagged)
[![Latest Version](https://img.shields.io/packagist/v/decodelabs/tagged.svg?style=flat)](https://packagist.org/packages/decodelabs/tagged)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/tagged.svg?style=flat)](https://packagist.org/packages/decodelabs/tagged)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/decodelabs/tagged/integrate.yml?branch=develop)](https://github.com/decodelabs/tagged/actions/workflows/integrate.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/tagged?style=flat)](https://packagist.org/packages/decodelabs/tagged)

### PHP markup generation without the fuss.

Tagged provides a simple, powerful and beautiful way to create HTML markup without the spaghetti.

---


## Installation

```bash
composer require decodelabs/tagged
```

## Usage

## HTML markup

Generate markup using a simple, flexible interface.

```php
use DecodeLabs\Tagged as Html;

echo Html::{'div.my-class#my-id'}(
    content: 'This is element content',
    title: 'This is a title'
);
```

...creates:

```html
<div class="my-class" id="my-id" title="This is a title">This is element content</div>
```

Create individual tags without content:

```php
use DecodeLabs\Tagged as Html;

$tag = Html::tag('div.my-class');

echo $tag->open();
echo 'Content';
echo $tag->close();
```

Wrap HTML strings to be used where an instance of <code>Markup</code> is needed:

```php
use DecodeLabs\Tagged as Html;

$buffer = Html::raw('<span class="test">My span</span>');
```

Dump script data to Html:

```php
yield Html::script(
    Html::raw(json_encode( $some_data )),
    type: 'application/json'
);
```

Prepare arbitrary input for Markup output:

```php
use DecodeLabs\Tagged as Html;

$markup = Html::wrap(
    function() {
        yield Html::h1('My title');
    },
    [Html::p(['This is ', Html::strong('mixed'), ' content'])]
);
```

### Attributes

Set attributes inline:

```php
use DecodeLabs\Tagged as Html;

echo Html::{'div[data-attr=foo]'}('This is a div with an attribute');
```

Set attributes via an array:

```php
use DecodeLabs\Tagged as Html;

echo Html::{'div'}(
    content: 'This is a div with an attribute',
    dataAttr: 'foo'
);
```

### Nesting

You can nest elements in multiple ways:

```php
use DecodeLabs\Tagged as Html;

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

    // Generator return values are rendered too
    return Html::{'div.awesome'}('This is awesome!');
});
```


### Time and date
Format and wrap dates and intervals

```php
use DecodeLabs\Tagged\Time;

// Custom format
Time::format('now', 'd/m/Y', 'Europe/London');

// Locale format
// When timezone is true it is fetched from Cosmos
Time::locale('now', 'long', 'long', true);

// Locale shortcuts
Time::dateTime('tomorrow'); // medium
Time::longTime('yesterday');
Time::shortDate('yesterday');
// ...etc


// Intervals
Time::since('yesterday'); // 1 day ago
Time::until('tomorrow'); // 1 day from now
Time::sinceAbs('yesterday'); // 1 day
Time::untilAbs('yesterday'); // -1 day
Time::between('yesterday', 'tomorrow'); // 1 day
```

### Media embeds
Normalize embed codes shared from media sites:

```php
use DecodeLabs\Tagged\Embed\Video;

echo Video::parse('https://www.youtube.com/watch?v=RG9TMn1FJzc');
```

## Components

Tagged also supports a higher level component abstraction allowing for more complex markup generation via the same interface. Components are called using an `@name` syntax:

```php
use DecodeLabs\Tagged as Html;

echo Html::{'@list'}($iterable, 'div.container', 'div.item', function($item) {
    return Html::{'span'}($item);
});

echo Html::{'@img'}('path/to/image.jpg', 'alt text');
```

See the [components](./src/Tagged/Component) directory for a list of available components.

## Licensing
Tagged is licensed under the MIT License. See [LICENSE](./LICENSE) for the full license text.
