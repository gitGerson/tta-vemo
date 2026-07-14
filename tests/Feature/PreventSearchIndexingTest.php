<?php

namespace Tests\Feature;

use Tests\TestCase;

class PreventSearchIndexingTest extends TestCase
{
    public function test_web_responses_include_noindex_header(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet, noimageindex');
    }

    public function test_robots_txt_disallows_all_crawlers(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));

        $this->assertStringContainsString('User-agent: *', $robots);
        $this->assertStringContainsString('Disallow: /', $robots);
    }
}
