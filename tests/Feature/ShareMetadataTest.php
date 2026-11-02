<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShareMetadataTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_expose_the_thirty_day_image_without_javascript(): void
    {
        foreach (['/', '/participar'] as $path) {
            $response = $this->get($path)->assertOk();
            $response->assertSee('property="og:title" content="Desafio da Virada — 30 dias, 5 objetivos"', false);
            $response->assertSee('property="og:image"', false);
            $response->assertSee('/images/desafio-da-virada-30-dias-share-v2.png', false);
            $response->assertSee('href="/images/logo-mark-30-virada.png?v=30-v2"', false);
            $response->assertDontSee('/images/logo.png?v=2', false);
            $this->assertMatchesRegularExpression('/property="og:image" content="https?:\/\/[^" ]+\/images\/desafio-da-virada-30-dias-share-v2\.png"/', $response->getContent());
        }
    }

    public function test_legacy_png_paths_and_share_image_use_the_current_brand(): void
    {
        $currentLogo = public_path('images/logo-30-virada.png');
        foreach (['images/logo.png', 'images/desafio-da-virada-30-dias-share-v2.png'] as $path) {
            $this->assertSame(hash_file('sha256', $currentLogo), hash_file('sha256', public_path($path)));
        }
        $this->assertSame(
            hash_file('sha256', public_path('images/logo-mark-30-virada.png')),
            hash_file('sha256', public_path('images/logo_sem_objetivo.png')),
        );
        $size = getimagesize(public_path('images/desafio-da-virada-30-dias-share-v2.png'));
        $this->assertSame([1254, 1254], [$size[0], $size[1]]);
        $this->assertSame('image/webp', getimagesize(public_path('images/logo.webp'))['mime']);
    }
}