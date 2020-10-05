# Tagged

[![PHP from Packagist](https://img.shields.io/packagist/php-v/decodelabs/tagged?style=flat-square)](https://packagist.org/packages/decodelabs/tagged)
[![Latest Version](https://img.shields.io/packagist/v/decodelabs/tagged.svg?style=flat-square)](https://packagist.org/packages/decodelabs/tagged)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/tagged.svg?style=flat-square)](https://packagist.org/packages/decodelabs/tagged)
[![Build Status](https://img.shields.io/travis/com/decodelabs/tagged/master.svg?style=flat-square)](https://travis-ci.org/decodelabs/tagged)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat-square)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/tagged?style=flat-square)](https://packagist.org/packages/decodelabs/tagged)

PHP markup generation without the fuss.


## Installation
```bash
composer install decodelabs/tagged
```

### Importing

Tagged uses [Veneer](https://github.com/decodelabs/veneer) to provide a unified frontage under <code>DecodeLabs\Tagged\Html</code>.
You can access all the primary HTML functionality via this static frontage without compromising testing and dependency injection.


## HTML markup

Generate markup using a simple, flexible interface.

```php
use DecodeLabs\Tagged\Html;

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
use DecodeLabs\Tagged\Html;

$tag = Html::tag('div.my-class');

echo $tag->open();
echo 'Content';
echo $tag->close();
```

Wrap HTML strings to be used where an instance of <code>Markup</code> is needed:

```php
use DecodeLabs\Tagged\Html;

$buffer = Html::raw('<span class="test">My span</span>');
```

Prepare arbitrary input for Markup output:

```php
use DecodeLabs\Tagged\Html;

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
use DecodeLabs\Tagged\Html;

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


### Convert to HTML
Parse various formats and convert to HTML:

```php
use DecodeLabs\Tagged\Html;

// Plain text
echo Html::$parse->plainText($plainText); // Replace \n with <br />

// Markdown
echo Html::$parse->markdown($myMarkdown); // Trusted markdown
echo Html::$parse->userMarkdown($myMarkdown); // Untrusted markdown
echo Html::$parse->inlineMarkdown($myMarkdown); // Trusted inline markdown
echo Html::$parse->userInlineMarkdown($myMarkdown); // Untrusted inline markdown

// Tweet
echo Html::$parse->tweet($plainTweet); // Convert tweet source to HTML
```


### Time and date
Format and wrap dates and intervals

```php
use DecodeLabs\Tagged\Html;

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
use DecodeLabs\Tagged\Html;

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
use DecodeLabs\Tagged\Html;

echo Html::$embed->video('https://www.youtube.com/watch?v=RG9TMn1FJzc');
```


### To text
Convert and normalize html to plain text:

```php
use DecodeLabs\Tagged\Html;

Html::$toText->convert('<h1>My html</h1>'); // My html
Html::$toText->preview('<h1>My html</h1>', 5); // My ht...
```


## XML handling

### Reading & manipulating

Access and manipulate XML files with a consolidated interface wrapping the DOM functionality available in PHP:

```php
use DecodeLabs\Tagged\Xml\Element as XmlElement;

$element = XmlElement::fromFile('/path/to/my/file.xml');

if($element->hasAttribute('old')) {
    $element->removeAttribute('old');
}

$element->setAttribute('new', 'value');

foreach($element->scanChildrenOfType('section') as $sectTag) {
    $inner = $sectTag->getFirstChildOfType('title');
    $sectTag->removeChild($inner);

    // Flatten to plain text
    echo $sectTag->getComposedTextContent();
}

file_put_contents('newfile.xml', (string)$element);
```

See [Element.php](./src/Tagged/Xml/Element.php) for the full interface.


### Writing

Programatically generate XML output with a full-featured wrapper around PHP's XML Writer:

```php
use DecodeLabs\Tagged\Xml\Writer as XmlWriter;

$writer = new XmlWriter();
$writer->writeHeader();

$writer->{'ns:section[ns:attr1=value].test'}(function ($writer) {
    $writer->{'title#main'}('This is a title');

    $writer->{'@body'}('This is an element with content wrapped in CDATA tags.');
    $writer->writeCData('This is plain CDATA');
});

echo $writer;
```

This creates:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<ns:section ns:attr1="value" class="test">
    <title id="main">This is a title</title>
    <body><![CDATA[This is an element with content wrapped in CDATA tags.]]></body>
<![CDATA[This is plain CDATA]]></ns:section>
```

See [Writer.php](./src/Tagged/Xml/Writer.php) for the full interface.


## Licensing
Tagged is licensed under the MIT License. See [LICENSE](./LICENSE) for the full license text.
