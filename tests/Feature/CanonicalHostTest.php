<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CanonicalHostTest extends TestCase
{
    use RefreshDatabase;

    public function test_host_asing_diredirect_permanen_ke_domain_resmi(): void
    {
        $response = $this->get('http://pionclinician.com/berita');

        $response->assertStatus(301);
        $response->assertRedirect(rtrim(config('app.url'), '/').'/berita');
    }

    public function test_host_asing_diredirect_dengan_query_string_utuh(): void
    {
        $this->get('http://domain-asing.test/berita?page=2')
            ->assertStatus(301)
            ->assertRedirect(rtrim(config('app.url'), '/').'/berita?page=2');
    }

    public function test_host_resmi_dilayani_normal(): void
    {
        $this->get(route('home'))->assertOk();
    }

    public function test_canonical_meta_memakai_app_url_bukan_host_request(): void
    {
        $base = rtrim(config('app.url'), '/');

        $this->get(route('posts.index'))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.$base.'/berita" />', false)
            ->assertSee('<meta property="og:url" content="'.$base.'/berita" />', false);
    }
}
