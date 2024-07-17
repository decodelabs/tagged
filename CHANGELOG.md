* Updated Veneer dependency

## v0.15.3 (2024-06-20)
* Added source accessors to InlineScript

## v0.15.2 (2024-06-20)
* Fixed InlineScript

## v0.15.1 (2024-06-20)
* Added ViewAssetContainer structure
* Added initial Asset implementations

## v0.15.0 (2024-05-07)
* Added JsonSerializable to Markup interface

## v0.14.14 (2024-04-29)
* Fixed Veneer stubs in gitattributes

## v0.14.13 (2024-04-23)
* Allow raw Buffers in attributes

## v0.14.12 (2023-11-23)
* Added consent controls to media embeds
* Made PHP8.1 minimum version
* Refactored package file structure

## v0.14.11 (2023-09-26)
* Converted phpstan doc comments to generic

## v0.14.10 (2023-07-11)
* Fixed callable instance issue

## v0.14.9 (2023-07-11)
* Added callable resolver to attribute handler

## v0.14.8 (2023-07-11)
* Added array attribute json encoding

## v0.14.7 (2023-01-27)
* Fixed buffer handling in ContentCollections

## v0.14.6 (2022-11-28)
* Switched to Cosmos for plugin interfaces
* Migrated to use effigy in CI workflow
* Fixed PHP8.1 testing

## v0.14.5 (2022-09-27)
* Updated media embed return signatures

## v0.14.4 (2022-09-27)
* Updated Veneer stub
* Updated composer check script

## v0.14.3 (2022-09-27)
* Converted Veneer plugins to load with Attributes

## v0.14.2 (2022-09-08)
* Updated Collections dependency
* Updated CI environment

## v0.14.1 (2022-08-24)
* Added concrete types to all members

## v0.14.0 (2022-08-23)
* Removed PHP7 compatibility
* Updated ECS to v11
* Updated PHPUnit to v9

## v0.13.0 (2022-03-13)
* Moved $parse and $toText plugins to Metamorph

## v0.12.0 (2022-03-10)
* Added pattern() to Time plugin
* Transitioned from Travis to GHA
* Updated PHPStan and ECS dependencies

## v0.11.10 (2021-10-20)
* Updated Veneer dependency

## v0.11.9 (2021-05-11)
* Added Veneer IDE support stub

## v0.11.8 (2021-04-30)
* Updated data attribute return type defs

## v0.11.7 (2021-04-30)
* Updated return type defs

## v0.11.6 (2021-04-20)
* Improved Factory Markup wrapper return types

## v0.11.5 (2021-04-15)
* Updated Time and Number plugins to use Dictum interfaces
* Simplified Time and Number plugin implementations

## v0.11.4 (2021-04-09)
* Updated Systemic dependency

## v0.11.3 (2021-04-07)
* Updated Elementary

## v0.11.2 (2021-04-07)
* Updated Collections

## v0.11.1 (2021-04-01)
* Fixed plugin class generators

## v0.11.0 (2021-04-01)
* Moved XML codebase to Exemplar
* Moved HTML codebase to root
* Updated for max PHPStan conformance

## v0.10.1 (2021-03-30)
* Switched Element to use ElementTrait
* Fixed Element interface on PHP7.2/3
* Fixed MarkupProxy for PHP7.2/3

## v0.10.0 (2021-03-30)
* Moved Builder library over to Elementary package
* Fixed new static() PHPStan issues
* Moved HTML Veneer binding to DecodeLabs\Tagged

## v0.9.1 (2021-03-19)
* Fixed number formatter input type handling
* Updated root use refs

## v0.9.0 (2021-03-18)
* Enabled PHP8 testing

## v0.8.10 (2020-12-17)
* Improved Media embed URL check

## v0.8.9 (2020-12-17)
* Added URL check to Media embed loader
* Applied full PSR12 standards
* Added PSR12 check to Travis build

## v0.8.8 (2020-10-06)
* Removed dependency on Atlas
* Removed dependency on Systemic

## v0.8.7 (2020-10-05)
* Improved readme
* Updated PHPStan

## v0.8.6 (2020-10-05)
* Updated to Veneer 0.6

## v0.8.5 (2020-10-04)
* Switched to Glitch Proxy incomplete()

## v0.8.4 (2020-10-02)
* Removed Glitch dependency

## v0.8.3 (2020-09-30)
* Switched to Exceptional for exception generation

## v0.8.2 (2020-09-25)
* Switched to Glitch Dumpable interface

## v0.8.1 (2020-09-24)
* Updated Composer dependency handling

## v0.8.0 (2019-11-12)
* Added HTML mail generator
* Handle exceptions in HTML element rendering

## v0.7.0 (2019-11-06)
* Refactored namespaces
* Added XML Element library
* Added XML Writer library
* Added XML Serializable interfaces
* Fixed Html embeds
* Updated veneer dependency

## v0.6.9 (2019-10-16)
* Added PHPStan support
* Bugfixes and updates from max level PHPStan scan

## v0.6.8 (2019-10-04)
* Added meta lookup for audioboom playlists
* Forced all video embeds to use https
* Fixed youtube embed URLs

## v0.6.7 (2019-10-03)
* Fixed video embed URL handling

## v0.6.6 (2019-10-03)
* Fixed audioboom embed attribute methods

## v0.6.5 (2019-10-03)
* Fixed embed provider return value
* Split embed handlers to separate classes
* Add meta lookup to embed handlers

## v0.6.4 (2019-10-03)
* Fixed ChildRendererTrait

## v0.6.3 (2019-10-03)
* Fixed TagTrait for PHP 7.2

## v0.6.2 (2019-10-03)
* Fixed media embed \__toString() method

## v0.6.1 (2019-10-02)
* Added StyleBlock class
* Added fromNow() date formatter
* Updated veneer version

## v0.6.0 (2019-09-26)
* Updated facade support to Veneer 0.3
* Updated Glitch support
* Bug fixes in element generation
* Added markdown parser support
* Added tweet parser support
* Added HTML to text tools
* Added icon plugin
* Added number format plugin
* Added date format plugin
* Added media embed plugin and handlers

## v0.5.0 (2019-09-10)
* Added initial codebase (ported from Df)
