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
use DecodeLabs\Tagged\Embed\Video;
use Throwable;

/**
 * @implements Processor<Video>
 */
class VideoEmbed implements Processor
{
    /**
     * @use ProcessorTrait<Video>
     */
    use ProcessorTrait;

    public const array OutputTypes = ['Tagged:Video', Video::class];

    public function coerce(
        mixed $value
    ): ?Video {
        if ($value === null) {
            return null;
        }

        try {
            return Video::parse(Coercion::asString($value));
        } catch (Throwable $e) {
            throw Exceptional::UnexpectedValue(
                message: 'Not a valid Video embed',
                data: $value
            );
        }
    }
}
