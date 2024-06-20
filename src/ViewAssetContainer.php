<?php

/**
 * @package Tagged
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Tagged;

use DecodeLabs\Tagged;
use DecodeLabs\Tagged\Asset\Link;
use DecodeLabs\Tagged\Asset\Script;

class ViewAssetContainer
{
    /**
     * @var array<Link>
     */
    protected array $css = [];

    /**
     * @var array<Script>
     */
    protected array $headJs = [];

    /**
     * @var array<Script>
     */
    protected array $bodyJs = [];

    protected ?Markup $content = null;

    /**
     * Add css
     */
    public function addCss(
        Link $link
    ): void {
        if (!in_array($link, $this->css, true)) {
            $this->css[] = $link;
        }
    }

    /**
     * Get css
     *
     * @return array<Link>
     */
    public function getCss(): array
    {
        // sort by priority
        usort($this->css, function ($a, $b) {
            return $a->getPriority() <=> $b->getPriority();
        });

        return $this->css;
    }

    /**
     * Remove css
     */
    public function removeCss(
        Link $link
    ): void {
        if (($key = array_search($link, $this->css, true)) !== false) {
            unset($this->css[$key]);
        }
    }

    /**
     * Clear CSS
     */
    public function clearCss(): void
    {
        $this->css = [];
    }

    /**
     * Add head js
     */
    public function addHeadJs(
        Script $script
    ): void {
        if (!in_array($script, $this->headJs, true)) {
            $this->headJs[] = $script;
        }
    }

    /**
     * Get head js
     *
     * @return array<Script>
     */
    public function getHeadJs(): array
    {
        // sort by priority
        usort($this->headJs, function ($a, $b) {
            return $a->getPriority() <=> $b->getPriority();
        });

        return $this->headJs;
    }

    /**
     * Remove head js
     */
    public function removeHeadJs(
        Script $script
    ): void {
        if (($key = array_search($script, $this->headJs, true)) !== false) {
            unset($this->headJs[$key]);
        }
    }

    /**
     * Clear head js
     */
    public function clearHeadJs(): void
    {
        $this->headJs = [];
    }

    /**
     * Add body js
     */
    public function addBodyJs(
        Script $script
    ): void {
        if (!in_array($script, $this->bodyJs, true)) {
            $this->bodyJs[] = $script;
        }
    }

    /**
     * Get body js
     *
     * @return array<Script>
     */
    public function getBodyJs(): array
    {
        // sort by priority
        usort($this->bodyJs, function ($a, $b) {
            return $a->getPriority() <=> $b->getPriority();
        });

        return $this->bodyJs;
    }

    /**
     * Remove body js
     */
    public function removeBodyJs(
        Script $script
    ): void {
        if (($key = array_search($script, $this->bodyJs, true)) !== false) {
            unset($this->bodyJs[$key]);
        }
    }

    /**
     * Clear body js
     */
    public function clearBodyJs(): void
    {
        $this->bodyJs = [];
    }

    /**
     * Set content
     */
    public function setContent(
        ?Markup $content
    ): void {
        $this->content = $content;
    }

    /**
     * Get content
     */
    public function getContent(): ?Markup
    {
        return $this->content;
    }

    /**
     * Render all inline
     */
    public function renderInline(): ?Markup
    {
        return Tagged::wrap(function () {
            foreach ($this->getCss() as $link) {
                yield $link->render();
            }

            foreach ($this->getHeadJs() as $script) {
                yield $script->render();
            }

            foreach ($this->getBodyJs() as $script) {
                yield $script->render();
            }

            yield $this->content;
        });
    }
}
