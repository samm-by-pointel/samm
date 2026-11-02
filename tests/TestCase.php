<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    /**
     * The data a React page receives, as the browser gets it: the props are
     * printed in the page source, so a value absent from here never left the
     * server. The interface texts (lang files) are left out: they are the same
     * for everyone and hold no data.
     */
    protected function pageProps(TestResponse $response): string
    {
        $props = $response->viewData('page')['props'];
        unset($props['translations']);

        return json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    protected function assertPageContains(TestResponse $response, string $text, string $message = ''): void
    {
        $this->assertStringContainsString($text, $this->pageProps($response), $message);
    }

    protected function assertPageMissing(TestResponse $response, string $text, string $message = ''): void
    {
        $this->assertStringNotContainsString($text, $this->pageProps($response), $message);
        $this->assertStringNotContainsString(e($text), preg_replace('/<script data-page[^>]*>.*?<\/script>/s', '', $response->getContent()), $message);
    }
}
