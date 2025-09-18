<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Lucid\Processor;

use DecodeLabs\Coercion;
use DecodeLabs\Exceptional;
use DecodeLabs\Lucid\Processor;
use DecodeLabs\Lucid\ProcessorTrait;
use DecodeLabs\Lucid\Sanitizer;
use DecodeLabs\Tagged\Embed\Audio;
use Throwable;

/**
 * @implements Processor<Audio>
 */
class AudioEmbed implements Processor
{
    /**
     * @use ProcessorTrait<Audio>
     */
    use ProcessorTrait;

    public const array OutputTypes = ['Tagged:Audio', Audio::class];

    public function __construct(
        protected Sanitizer $sanitizer,
    ) {
    }

    public function coerce(
        mixed $value
    ): ?Audio {
        if ($value === null) {
            return null;
        }

        try {
            return Audio::parse(Coercion::asString($value));
        } catch (Throwable $e) {
            throw Exceptional::UnexpectedValue(
                message: 'Not a valid Audio embed',
                data: $value
            );
        }
    }
}
