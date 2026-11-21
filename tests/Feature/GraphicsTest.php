<?php

namespace Tests\Feature;

use App\Support\Graphics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class GraphicsTest extends TestCase
{
    use RefreshDatabase;

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dir = public_path('images/grafiki');
        $this->existed = is_dir($this->dir);
    }

    private bool $existed;

    protected function tearDown(): void
    {
        if (! $this->existed) {
            File::deleteDirectory($this->dir);
        }
        parent::tearDown();
    }

    public function test_banners_use_language_version_and_photo_fallback(): void
    {
        if ($this->existed) {
            $this->markTestSkipped('Prawdziwe grafiki są w katalogu – test działa na pustym.');
        }
        File::ensureDirectoryExists($this->dir.'/de');
        foreach (['N02-praca.webp', 'N01-portal.webp', 'A07-uscisk-dloni.webp', 'A01-ciezarowki-autostrada.webp', 'de/N02-praca.webp', 'kanal19-logo-biale.svg'] as $f) {
            File::put($this->dir.'/'.$f, 'x');
        }

        app()->setLocale('pl');
        $this->assertStringEndsWith('images/grafiki/N02-praca.webp', Graphics::banner('N02-praca'));
        app()->setLocale('de');
        $this->assertStringEndsWith('images/grafiki/de/N02-praca.webp', Graphics::banner('N02-praca'));
        app()->setLocale('fr');
        $this->assertStringEndsWith('images/grafiki/A07-uscisk-dloni.webp', Graphics::banner('N02-praca', 'A07-uscisk-dloni'));
        $this->assertNull(Graphics::banner('N99-brak'));

        $html = $this->withSession(['locale' => 'pl'])->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('images/grafiki/N01-portal.webp', $html);
        $this->assertStringContainsString('images/grafiki/kanal19-logo-biale.svg', $html);
        $this->assertStringContainsString('Wszystko dla kierowcy w jednym miejscu', $html);
    }
}
