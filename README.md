# Tagged

[![PHP from Packagist](https://img.shields.io/packagist/php-v/decodelabs/tagged?style=flat)](https://packagist.org/packages/decodelabs/tagged)
[![Latest Version](https://img.shields.io/packagist/v/decodelabs/tagged.svg?style=flat)](https://packagist.org/packages/decodelabs/tagged)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/tagged.svg?style=flat)](https://packagist.org/packages/decodelabs/tagged)
[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/decodelabs/tagged/Integrate)](https://github.com/decodelabs/tagged/actions/workflows/integrate.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/tagged?style=flat)](https://packagist.org/packages/decodelabs/tagged)

### PHP markup generation without the fuss.

Tagged provides a simple, powerful and beautiful way to create HTML markup without the spaghetti.

_Get news and updates on the [DecodeLabs blog](https://blog.decodelabs.com)._

---


## Installation

```bash
composer require decodelabs/tagged
```

## Usage

Tagged uses [Veneer](https://github.com/decodelabs/veneer) to provide a unified frontage under <code>DecodeLabs\Tagged</code>.
You can access all the primary HTML functionality via this static frontage without compromising testing and dependency injection.


## HTML markup

Generate markup using a simple, flexible interface.

```php
use DecodeLabs\Tagged as Html;

echo Html::{'div.my-class#my-id'}('This is element content', [
    'title' => 'This is a title'
]);
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
use DecodeLabs\Tagged as Html;

// Custom format
Html::$time->format('now', 'd/m/Y', 'Europe/London');

// Locale format
// When timezone is true it is fetched from Systemic::$timezone
Html::$time->locale('now', 'long', 'long', true);

// Locale shortcuts
Html::$time->dateTime('tomorrow'); // medium
Html::$time->longTime('yesterday');
Html::$time->shortDate('yesterday');
// ...etc


// Intervals
Html::$time->since('yesterday'); // 1 day ago
Html::$time->until('tomorrow'); // 1 day from now
Html::$time->sinceAbs('yesterday'); // 1 day
Html::$time->untilAbs('yesterday'); // -1 day
Html::$time->between('yesterday', 'tomorrow'); // 1 day
```


### Icons
Create the markup needed for font or SVG icons:

```php
use DecodeLabs\Tagged as Html;

Html::$icon->setFormat('font');
echo Html::$icon->aubergine; // <i class="icon icon-aubergine"></i>

Html::$icon->setFormat('svg');
echo Html::$icon->aubergine; // <svg><use xlink:href="#aubergine" /></svg>

Html::$icon->setSvgReference('path/to/my/file.svg');
echo Html::$icon->aubergine; // <svg><use xlink:href="path/to/my/file.svg#aubergine" /></svg>
```


### Media embeds
Normalize embed codes shared from media sites:

```php
use DecodeLabs\Tagged as Html;

echo Html::$embed->video('https://www.youtube.com/watch?v=RG9TMn1FJzc');
```


## Deprecated functionality

Looking for the functionality that was here before? It's likely been moved to a dedicated library:

### XML handling

The XML manipulation functionality of Tagged has been moved to its own project, [Exemplar](https://github.com/decodelabs/exemplar/).


### Content parsing (Tagged::$parse and ::$toText plugins)

Content transformations are now handled by the [Metamorph](https://github.com/decodelabs/metamorph/) library.


## Licensing
Tagged is licensed under the MIT License. See [LICENSE](./LICENSE) for the full license text.
