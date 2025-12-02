# Tagged — Package Specification

> **Cluster:** `frontend`
> **Language:** `php`
> **Milestone:** `m3`
> **Repo:** `https://github.com/decodelabs/tagged`
> **Role:** HTML markup generation

## Overview

### Purpose

Tagged provides a simple, powerful and beautiful way to create HTML markup without the spaghetti. It offers a fluent interface for generating HTML elements, components, and markup with support for attributes, nesting, and content handling.

Key features:
- **Simple syntax**: CSS selector-like syntax for creating elements (`div.my-class#my-id`)
- **Fluent interface**: Method chaining and generator support for complex markup
- **Component system**: Higher-level component abstraction via `@name` syntax
- **Time formatting**: Date and time formatting with locale support
- **Media embeds**: Normalize embed codes from media sites (YouTube, Vimeo, etc.)
- **Number formatting**: Number formatting with locale support
- **Content handling**: Flexible content handling (strings, arrays, generators, closures)
- **Attribute management**: Rich attribute handling with class lists and styles
- **Raw HTML**: Support for raw HTML injection when needed

### Non-Goals

- Tagged does not provide template engines or template compilation.
- It does not handle form validation or form processing.
- It does not provide CSS or JavaScript generation.
- It does not handle HTML parsing or DOM manipulation.
- It does not provide server-side rendering frameworks.

## Role in the Ecosystem

### Cluster & Positioning

Tagged belongs to the **frontend** cluster, focusing on HTML markup generation. It complements other frontend packages by providing a simple and powerful way to generate HTML markup programmatically.

### Usage Contexts

- **HTML generation**: Creating HTML markup programmatically
- **Component rendering**: Rendering reusable HTML components
- **Form generation**: Generating form elements and structures
- **API responses**: Generating HTML responses from APIs
- **Email templates**: Generating HTML email content
- **Documentation**: Generating HTML documentation

## Public Surface

### Key Types

- **`Tagged`** (class): Main service class providing static methods for element creation, components, and utilities. Implements `Service` for Kingdom integration.

- **`Element`** (class): Element class representing HTML elements. Extends `Tag` and implements `ElementInterface`. Provides content handling and rendering.

- **`Tag`** (class): Tag class representing HTML tags. Implements `TagInterface`, `Markup`, `ClassListContainer`, `StyleContainer`. Provides attribute management and rendering.

- **`Markup`** (interface): Markup interface extending `RootMarkup`. Defines `__toString()` method for string conversion.

- **`Buffer`** (class): Buffer class for raw HTML content. Implements `BufferInterface` and `Markup`. Used for raw HTML injection.

- **`ContentCollection`** (class): Content collection class for managing multiple content items. Implements `Markup`, `SequenceInterface`, `ArrayAccess`. Provides content normalization and rendering.

- **`Component`** (interface): Component interface for higher-level components. Extends `ClassListContainer`, `Renderable`, `StyleContainer`, `Stringable`, `Tag`.

- **`Component\Img`** (class): Image component for generating `<img>` elements.

- **`Component\ContainedList`** (class): List component for generating lists from iterables.

- **`Component\Ol`** (class): Ordered list component.

- **`Component\Ul`** (class): Unordered list component.

- **`Component\Dl`** (class): Description list component.

- **`Component\Each`** (class): Each component for iterating over items.

- **`Component\Elements`** (class): Elements component.

- **`Component\Inline`** (class): Inline component.

- **`Component\AudioEmbed`** (class): Audio embed component.

- **`Component\VideoEmbed`** (class): Video embed component.

- **`Time`** (class): Time formatting class implementing `TimeExtension`. Provides date/time formatting and interval formatting.

- **`Number`** (class): Number formatting class implementing `NumberExtension`. Provides number formatting with locale support.

- **`Embed\Video`** (class): Video embed class for video embeds.

- **`Embed\Audio`** (class): Audio embed class for audio embeds.

- **`Embed\Youtube`** (class): YouTube embed implementation.

- **`Embed\Vimeo`** (class): Vimeo embed implementation.

- **`Embed\Audioboom`** (class): Audioboom embed implementation.

- **`Embed\Media`** (interface): Media embed interface.

- **`Embed\MediaTrait`** (trait): Media embed trait providing common functionality.

- **`RenderableTrait`** (trait): Renderable trait providing rendering functionality.

- **`BufferProviderTrait`** (trait): Buffer provider trait providing buffer functionality.

- **`MarkupProvider`** (interface): Markup provider interface.

- **`PriorityMarkup`** (class): Priority markup class.

### Main Entry Points

**Tagged Service:**
- `Tagged::__callStatic(string $tagName, array $args): Element|Component` — Create element or component via static call
- `Tagged::tag(string $tagName, iterable $attributes = [], mixed ...$attributeList): Tag` — Create tag without content
- `Tagged::el(string $tagName, mixed $content = null, iterable $attributes = [], mixed ...$attributeList): Element` — Create element
- `Tagged::component(string $tagName, mixed ...$args): Component` — Create component
- `Tagged::raw(mixed $html, bool $escaped = false): Buffer` — Create raw HTML buffer
- `Tagged::wrap(mixed ...$content): Buffer` — Wrap content
- `Tagged::render(mixed ...$content): string` — Render content to string
- `Tagged::content(mixed ...$content): ContentCollection` — Create content collection
- `Tagged::esc(mixed $value): ?string` — Escape HTML

**Element Creation:**
- `Element::create(string $tagName, mixed $content = null, iterable $attributes = [], mixed ...$attributeList): Element` — Create element
- CSS selector syntax: `div.my-class#my-id[data-attr=value]`
- Nested syntax: `header > h1` (creates nested elements)
- Generator content: Pass generators for dynamic content
- Closure content: Pass closures for dynamic content

**Component Creation:**
- `Tagged::{'@componentName'}(...$args)` — Create component via static call
- Component names converted from kebab-case to PascalCase
- `@list` → `ContainedList`
- `@img` → `Img`
- Components support tag definition syntax: `@list.div.container > div.item`

**Time Formatting:**
- `Time::format(DateTimeInterface|DateInterval|string|Stringable|int|null $date, string $format, DateTimeZone|string|Stringable|bool|null $timezone = true): ?Element` — Format date/time
- `Time::locale(DateTimeInterface|DateInterval|string|Stringable|int|null $date, string|int|bool|null $dateSize = true, string|int|bool|null $timeSize = true, DateTimeZone|string|Stringable|bool|null $timezone = true, string|Locale|null $locale = null): ?Element` — Format with locale
- `Time::since(DateTimeInterface|DateInterval|string|Stringable|int|null $date, ?int $parts = 1, bool $short = false, string|Locale|null $locale = null): ?Element` — Format "since" interval
- `Time::until(DateTimeInterface|DateInterval|string|Stringable|int|null $date, ?int $parts = 1, bool $short = false, string|Locale|null $locale = null): ?Element` — Format "until" interval
- `Time::between(DateTimeInterface|DateInterval|string|Stringable|int|null $date1, DateTimeInterface|DateInterval|string|Stringable|int|null $date2, ?int $parts = 1, bool $short = false, string|Locale|null $locale = null): ?Element` — Format "between" interval

**Number Formatting:**
- `Number::format(int|float|string|null $value, ?string $unit = null, string|Locale|null $locale = null): ?Element` — Format number
- `Number::pattern(int|float|string|null $value, string $pattern, string|Locale|null $locale = null): ?Element` — Format with pattern
- `Number::decimal(int|float|string|null $value, ?int $precision = null, string|Locale|null $locale = null): ?Element` — Format as decimal

**Media Embeds:**
- `Embed\Video::parse(string $url): Video` — Parse video URL
- `Embed\Audio::parse(string $url): Audio` — Parse audio URL
- Supports YouTube, Vimeo, Audioboom

## Dependencies

### Decode Labs

- **`decodelabs/archetype`**: Used for component resolution and custom component discovery.
- **`decodelabs/coercion`**: Used for type coercion in attribute and content handling.
- **`decodelabs/collections`**: Used for `SequenceInterface` support in `ContentCollection`.
- **`decodelabs/cosmos`**: Used for locale support in time and number formatting.
- **`decodelabs/elementary`**: Used for base element interfaces and traits (`ElementInterface`, `TagInterface`, `ClassListContainer`, `StyleContainer`).
- **`decodelabs/exceptional`**: Used for exception handling throughout the package.
- **`decodelabs/kingdom`**: Used for service container integration (`Service` interface).
- **`decodelabs/monarch`**: Used for logging and debugging capabilities.
- **`decodelabs/slingshot`**: Used for component instantiation with dependency injection.

### External

- **PHP**: See `composer.json` for supported PHP versions.
- **`ext-intl`**: Required for internationalization support (used in time and number formatting).
- **`symfony/polyfill-mbstring`**: Used for multibyte string support.

### Optional

- **`nesbot/carbon`**: Detected at runtime if installed, used for time interval formatting (suggested dependency).

## Behaviour & Contracts

### Invariants

- Elements are rendered as HTML strings when converted to string.
- Attributes are escaped by default (except for `Buffer` values).
- Content is escaped by default (except for `Buffer` values).
- Self-closing tags are rendered without closing tag.
- Inline tags are rendered without line breaks.
- CSS selector syntax parsed: `.class` → `class` attribute, `#id` → `id` attribute, `[attr=value]` → attribute.

### Input & Output Contracts

**Element Creation:**
- Tag name can include CSS selector syntax: `div.my-class#my-id[data-attr=value]`
- Content can be: string, array, generator, closure, `Markup`, `null`
- Attributes can be: array, named parameters, or inline in tag name
- Nested elements: `header > h1` creates nested structure

**Content Handling:**
- Strings: Escaped and rendered as text content
- Arrays: Iterated and rendered as content collection
- Generators: Iterated and yielded content rendered
- Closures: Called with element instance, return value rendered
- `Markup`: Rendered directly (not escaped)
- `Buffer`: Rendered directly (not escaped if `escaped = false`)

**Attribute Handling:**
- Attribute values coerced to strings (except booleans, arrays, `Buffer`)
- Arrays: JSON encoded
- Booleans: Rendered as attribute name only (if true) or omitted (if false)
- `Buffer`: Rendered directly (not escaped)
- Class lists: Managed via `ClassListContainer` interface
- Styles: Managed via `StyleContainer` interface

**Component Resolution:**
- Component names converted from kebab-case to PascalCase
- `@list` → `ContainedList`
- `@img` → `Img`
- Components resolved via Slingshot with dependency injection
- Tag definition syntax supported: `@component.tag.definition`

**Time Formatting:**
- Dates formatted with timezone support
- Locale formatting uses Cosmos locale system
- Interval formatting supports "since", "until", and "between"
- Returns `Element` with `datetime` attribute and formatted content

**Number Formatting:**
- Numbers formatted with locale support
- Pattern formatting uses ICU patterns
- Unit support for displaying units with numbers
- Returns `Element` with formatted number and optional unit

**Media Embeds:**
- URLs parsed to extract provider and ID
- Provider-specific embed code generation
- Returns `Element` with appropriate embed markup

## Error Handling

- **Invalid component name**: `component()` throws `InvalidArgument` exception for invalid component names.
- **Invalid attribute value**: Attribute encoding throws `UnexpectedValue` exception if unable to encode to JSON.
- **Invalid date/time**: Time formatting returns `null` for invalid dates.
- **Invalid number**: Number formatting returns `null` for invalid numbers.

## Configuration & Extensibility

### Custom Components

Implement `Component` interface or extend `Tag`:

```php
use DecodeLabs\Tagged\Component;
use DecodeLabs\Tagged\Tag;
use DecodeLabs\Tagged\RenderableTrait;

class MyComponent extends Tag implements Component
{
    use RenderableTrait;

    public function __construct(...$args)
    {
        parent::__construct('div', $attributes);
        // Component initialization
    }

    public function render(bool $pretty = false): ?Buffer
    {
        // Custom rendering logic
    }
}
```

Register component via Slingshot for `@my-component` syntax.

### Custom Media Embeds

Implement `Media` interface or extend embed classes:

```php
use DecodeLabs\Tagged\Embed\Media;
use DecodeLabs\Tagged\Embed\MediaTrait;

class MyEmbed implements Media
{
    use MediaTrait;

    // Implement Media interface methods
}
```

### Custom Time/Number Formatting

Extend `Time` or `Number` classes for custom formatting:

```php
use DecodeLabs\Tagged\Time;

class MyTime extends Time
{
    // Custom time formatting methods
}
```

## Interactions with Other Packages

- **Elementary**: Used for base element interfaces and traits. Tagged extends Elementary interfaces for element and tag functionality.
- **Cosmos**: Used for locale support in time and number formatting. Time and Number classes implement Cosmos extension interfaces.
- **Collections**: Used for `SequenceInterface` support in `ContentCollection`.
- **Slingshot**: Used for component instantiation with dependency injection support.
- **Archetype**: Used for component resolution and custom component discovery.
- **Monarch**: Used for logging and debugging capabilities (exception logging).

## Usage Examples

### Basic Element Creation

```php
use DecodeLabs\Tagged as Html;

// Simple element
echo Html::div('Content');

// With CSS selector syntax
echo Html::{'div.my-class#my-id'}(
    content: 'This is element content',
    title: 'This is a title'
);
// <div class="my-class" id="my-id" title="This is a title">This is element content</div>

// With attributes
echo Html::{'div[data-attr=foo]'}('This is a div with an attribute');
```

### Nested Elements

```php
use DecodeLabs\Tagged as Html;

// Nested via array
echo Html::div([
    Html::{'span.inner1'}('Inner 1'),
    ' ',
    Html::{'span.inner2'}('Inner 2')
]);

// Nested via generator
echo Html::div(function($el) {
    $el->addClass('container');
    yield Html::{'header > h1'}('This is a header');
    yield Html::p('This is a paragraph');
    return Html::{'div.awesome'}('This is awesome!');
});

// Nested via selector syntax
echo Html::{'header > h1'}('Title');
```

### Tag Without Content

```php
use DecodeLabs\Tagged as Html;

$tag = Html::tag('div.my-class');

echo $tag->open();
echo 'Content';
echo $tag->close();
```

### Raw HTML

```php
use DecodeLabs\Tagged as Html;

// Raw HTML buffer
$buffer = Html::raw('<span class="test">My span</span>');

// Script data
yield Html::script(
    Html::raw(json_encode($some_data)),
    type: 'application/json'
);
```

### Content Wrapping

```php
use DecodeLabs\Tagged as Html;

$markup = Html::wrap(
    function() {
        yield Html::h1('My title');
    },
    [Html::p(['This is ', Html::strong('mixed'), ' content'])]
);
```

### Components

```php
use DecodeLabs\Tagged as Html;

// List component
echo Html::{'@list'}($iterable, 'div.container', 'div.item', function($item) {
    return Html::{'span'}($item);
});

// Image component
echo Html::{'@img'}('path/to/image.jpg', 'alt text');

// Component with tag definition
echo Html::{'@list.div.container > div.item'}($items, function($item) {
    return Html::span($item);
});
```

### Time Formatting

```php
use DecodeLabs\Tagged\Time;

// Custom format
Time::format('now', 'd/m/Y', 'Europe/London');

// Locale format
Time::locale('now', 'long', 'long', true);

// Locale shortcuts
Time::dateTime('tomorrow');
Time::longTime('yesterday');
Time::shortDate('yesterday');

// Intervals
Time::since('yesterday'); // 1 day ago
Time::until('tomorrow'); // 1 day from now
Time::sinceAbs('yesterday'); // 1 day
Time::untilAbs('yesterday'); // -1 day
Time::between('yesterday', 'tomorrow'); // 1 day
```

### Number Formatting

```php
use DecodeLabs\Tagged\Number;

// Format number
Number::format(1234.56, 'USD', 'en_US');

// Pattern formatting
Number::pattern(1234.56, '#,##0.00', 'en_US');

// Decimal formatting
Number::decimal(1234.56, 2, 'en_US');
```

### Media Embeds

```php
use DecodeLabs\Tagged\Embed\Video;

// Parse video URL
echo Video::parse('https://www.youtube.com/watch?v=RG9TMn1FJzc');
```

### Attribute Management

```php
use DecodeLabs\Tagged as Html;

$el = Html::div('Content');

// Set attributes
$el->setAttribute('data-attr', 'value');
$el->setAttributes(['class' => 'my-class', 'id' => 'my-id']);

// Class management
$el->addClass('new-class');
$el->removeClass('old-class');
$el->toggleClass('toggle-class');

// Style management
$el->setStyle('color', 'red');
$el->setStyles(['color' => 'red', 'background' => 'blue']);
```

## Implementation Notes (for Contributors)

### CSS Selector Parsing

- Selector syntax parsed: `.class` → `class` attribute, `#id` → `id` attribute, `[attr=value]` → attribute
- Multiple classes supported: `.class1.class2`
- Multiple attributes supported: `[attr1=value1][attr2=value2]`
- Parsing happens in `Element::create()` and `Tag` constructor

### Content Rendering

- Content normalized via `ContentCollection::normalize()`
- Strings escaped via `Tagged::esc()`
- Arrays iterated and rendered recursively
- Generators iterated and yielded content rendered
- Closures called with element instance
- `Markup` instances rendered directly (not escaped)
- `Buffer` instances rendered directly (escaped based on `escaped` flag)

### Attribute Rendering

- Attribute values coerced to strings (except booleans, arrays, `Buffer`)
- Arrays JSON encoded
- Booleans rendered as attribute name only (if true) or omitted (if false)
- `Buffer` rendered directly (not escaped)
- Class lists managed via `ClassListContainer` interface
- Styles managed via `StyleContainer` interface

### Component Resolution

- Component names converted from kebab-case to PascalCase
- `@list` → `ContainedList` (special case)
- Components resolved via Slingshot with dependency injection
- Tag definition syntax parsed and applied to component

### Self-Closing Tags

- Self-closing tags defined in `Tag::SelfClosingTags` constant
- Rendered without closing tag: `<br />`
- Content ignored for self-closing tags

### Inline Tags

- Inline tags defined in `Tag::InlineTags` constant
- Rendered without line breaks
- Used for pretty printing optimization

### Time Formatting

- Dates formatted with timezone support via Cosmos
- Locale formatting uses Cosmos locale system
- Interval formatting supports "since", "until", and "between"
- Returns `Element` with `datetime` attribute and formatted content
- Classes added for past/future, positive/negative intervals

### Number Formatting

- Numbers formatted with locale support via Cosmos
- Pattern formatting uses ICU patterns
- Unit support for displaying units with numbers
- Returns `Element` with formatted number and optional unit

### Media Embeds

- URLs parsed to extract provider and ID
- Provider-specific embed code generation
- YouTube, Vimeo, Audioboom supported
- Returns `Element` with appropriate embed markup (iframe or div)

## Testing & Quality

**Current Status:**
- Code quality: 4.5/5
- README quality: 3/5
- Documentation: 0/5 (no formal docs yet)
- Tests: 0/5 (no test suite yet)

**Testing Considerations:**
- Element creation should be tested for:
  - CSS selector parsing
  - Attribute handling
  - Content handling (strings, arrays, generators, closures)
  - Nested elements
  - Self-closing tags
  - Inline tags

- Component system should be tested for:
  - Component resolution
  - Component rendering
  - Tag definition syntax
  - Custom components

- Time formatting should be tested for:
  - Date formatting
  - Locale formatting
  - Interval formatting
  - Timezone handling

- Number formatting should be tested for:
  - Number formatting
  - Pattern formatting
  - Locale formatting
  - Unit handling

- Media embeds should be tested for:
  - URL parsing
  - Provider detection
  - Embed code generation

- Edge cases should be tested for:
  - Empty content
  - Null values
  - Invalid selectors
  - Invalid attributes
  - Invalid dates/numbers
  - Invalid URLs

## Roadmap & Future Ideas

- **Template compilation**: Support for template compilation and caching
- **Form generation**: Enhanced form generation capabilities
- **CSS generation**: Support for CSS generation
- **JavaScript generation**: Support for JavaScript generation
- **HTML parsing**: Support for HTML parsing and DOM manipulation
- **Server-side rendering**: Enhanced server-side rendering capabilities
- **Performance optimization**: Caching and optimization for large markup generation
- **Better error messages**: More detailed error messages for invalid markup

## References

- Package repository: https://github.com/decodelabs/tagged
- Composer package: https://packagist.org/packages/decodelabs/tagged
- Related packages:
  - `decodelabs/elementary` — Base element interfaces
  - `decodelabs/cosmos` — Locale support
  - `decodelabs/collections` — Sequence interface
  - `decodelabs/slingshot` — Component instantiation
  - `decodelabs/archetype` — Component resolution

