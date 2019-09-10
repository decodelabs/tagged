# Tagged
PHP markup generation without the fuss.


## Installation
```bash
composer install decodelabs/tagged
```

## Usage
Generate markup using a simple, predictable interface.

```php
echo html('div.my-class#my-id', 'This is element content', [
    'title' => 'This is a title'
]);

/*
Creates -

<div class="my-class" id="my-id" title="This is a title">This is element content</div>
*/
```

Create individual tags without content:

```php
$tag = html\tag('div.my-class');

echo $tag->open();
echo 'Content';
echo $tag->close();
```

Wrap HTML strings to be used where an instance of <code>Markup</code> is needed:

```php
$buffer = html\wrap('<span class="test">My span</span>');
```


### Nesting

You can nest elements in multiple ways:

```php
// Pass in nested elements via array
echo html('div', [
    html('span.inner1', 'Inner 1'),
    ' ',
    html('span.inner2', 'Inner 2')
]);


// Return anything and everything via a generator
echo html('div', function() {
    yield html('header > h1', 'This is a header');
    yield html('p', 'This is a paragraph');
});
```


## Licensing
Glitch is licensed under the MIT License. See [LICENSE](./LICENSE) for the full license text.
