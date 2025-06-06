<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged;

use DecodeLabs\Monarch;
use Throwable;

trait RenderableTrait
{
    /**
     * Render to string
     */
    public function __toString(): string
    {
        try {
            return (string)$this->render(true);
        } catch (Throwable $e) {
            Monarch::logException($e);
            $message = '<strong>' . $e->getMessage() . '</strong>';

            if (!Monarch::isProduction()) {
                $message .= '<br /><samp>' . Monarch::$paths->prettify($e->getFile()) . '</samp> : <samp>' . $e->getLine() . '</samp>';
                $title = $this->esc((string)$e);
            } else {
                $title = 'HTML Error';
            }

            return '<div class="error" style="color: red; background: white; padding: 0.5rem;" title="' . $title . '">' . $message . '</div>';
        }
    }
}
