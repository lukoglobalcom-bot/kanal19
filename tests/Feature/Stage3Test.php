<?php

namespace Tests\Feature;

use App\Models\MapReport;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Stage3Test extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'driver', bool $verified = true, string $phone = '+48500000001'): User
    {
        $u = User::create(['name' => 'Kierowca '.$role, 'email' => fake()->unique()->safeEmail(), 'password' => 'haslo1234', 'phone' => $phone]);
        $u->forceFill(['role' => $role, 'phone_verified_at' => $verified ? now() : null])->save();

        return $u;
    }

    public function test_parking_and_fuel_reports_with_country_currency(): void
    {
        $this->actingAs($this->user())->postJson('/mapa/zgloszenia', [
            'kind' => 'parking', 'lat' => 52.1, 'lng' => 20.5, 'name' => 'MOP Wiskitki', 'country' => 'PL', 'free_truck' => 4, 'paid' => '1',
        ])->assertOk()->assertJson(['ok' => true]);

        $this->postJson('/mapa/zgloszenia', ['kind' => 'parking', 'lat' => 52.1, 'lng' => 20.5, 'country' => 'PL'])->assertStatus(422);

        $this->postJson('/mapa/zgloszenia', ['kind' => 'fuel', 'lat' => 51.3, 'lng' => 12.4, 'country' => 'DE', 'diesel' => 1.69])->assertOk();
        $this->postJson('/mapa/zgloszenia', ['kind' => 'fuel', 'lat' => 50.0, 'lng' => 14.4, 'country' => 'CZ', 'diesel' => 36.9])->assertOk();

        $data = $this->getJson('/mapa/dane')->assertOk()->json();
        $fuel = collect($data)->where('kind', 'fuel')->pluck('data.currency', 'country');
        $this->assertSame('EUR', $fuel['DE']);
        $this->assertSame('CZK', $fuel['CZ']);
        $this->assertSame(4, collect($data)->firstWhere('kind', 'parking')['data']['free_truck']);

        $this->travel(7)->hours();
        $this->assertCount(2, $this->getJson('/mapa/dane')->json()); // parking wygasł po 6 h, paliwo zostaje
    }

    public function test_sos_flow_phone_visible_only_to_verified_and_help(): void
    {
        $caller = $this->user('driver', true, '+48600111222');
        $this->actingAs($caller)->postJson('/mapa/zgloszenia', ['kind' => 'sos', 'problem' => 'trailer', 'message' => 'A2 km 312', 'lat' => 52.3, 'lng' => 16.9, 'country' => 'PL'])->assertOk();
        // drugie wezwanie zastępuje pierwsze
        $this->postJson('/mapa/zgloszenia', ['kind' => 'sos', 'problem' => 'life', 'lat' => 52.3, 'lng' => 16.9, 'country' => 'PL'])->assertOk();
        $this->assertSame(1, MapReport::active()->where('kind', 'sos')->count());
        $sos = MapReport::active()->where('kind', 'sos')->first();

        $this->postJson("/mapa/sos/{$sos->id}/pomoc")->assertForbidden(); // nie można pomagać sobie

        auth()->logout();
        $guest = collect($this->getJson('/mapa/dane')->json())->firstWhere('kind', 'sos');
        $this->assertNull($guest['phone']);

        $helper = $this->user('driver', true, '+48600999888');
        $this->actingAs($helper)->postJson("/mapa/sos/{$sos->id}/pomoc")->assertOk();
        $this->postJson("/mapa/sos/{$sos->id}/pomoc")->assertOk();
        $seen = collect($this->getJson('/mapa/dane')->json())->firstWhere('kind', 'sos');
        $this->assertSame('+48600111222', $seen['phone']);
        $this->assertSame(1, $seen['helpers']);
        $this->assertTrue($seen['helping']);

        $this->postJson("/mapa/zgloszenia/{$sos->id}/zamknij")->assertForbidden();
        $this->actingAs($caller)->postJson("/mapa/zgloszenia/{$sos->id}/zamknij")->assertOk();
        $this->assertSame(0, MapReport::active()->count());
    }

    public function test_unverified_cannot_report(): void
    {
        $this->actingAs($this->user('driver', false))->postJson('/mapa/zgloszenia', ['kind' => 'sos', 'problem' => 'health', 'lat' => 1, 'lng' => 1, 'country' => 'PL'])->assertRedirect();
        $this->assertSame(0, MapReport::count());
        $this->get('/sos')->assertOk()->assertSee('tel:112', false);
    }

    public function test_posts_admin_only_and_per_locale(): void
    {
        $this->actingAs($this->user())->get('/admin/wpisy/nowy')->assertForbidden();

        $admin = $this->user('admin');
        $this->actingAs($admin)->post('/admin/wpisy', ['category' => 'mandaty', 'locale' => 'pl', 'title' => 'Jak odwołać się od mandatu', 'body' => str_repeat('Treść poradnika. ', 6), 'publish' => '1'])->assertRedirect();
        $this->post('/admin/wpisy', ['category' => 'eu', 'locale' => 'de', 'title' => 'Neue Regeln in Deutschland', 'body' => str_repeat('Inhalt des Artikels. ', 6), 'publish' => '1'])->assertRedirect();
        $this->post('/admin/wpisy', ['category' => 'pl', 'locale' => 'pl', 'title' => 'Szkic niewidoczny', 'body' => str_repeat('Treść szkicu. ', 6)])->assertRedirect();
        auth()->logout();

        $this->get('/aktualnosci')->assertOk()->assertSee('Jak odwołać się od mandatu')->assertDontSee('Neue Regeln')->assertDontSee('Szkic niewidoczny');
        $this->get('/')->assertSee('Jak odwołać się od mandatu');
        $this->withSession(['locale' => 'de'])->get('/aktualnosci')->assertSee('Neue Regeln')->assertDontSee('Jak odwołać');
        $draft = Post::where('title', 'Szkic niewidoczny')->first();
        $this->get('/aktualnosci/'.$draft->id)->assertNotFound();
        $this->withSession(['locale' => 'pl'])->get('/aktualnosci?kategoria=mandaty')->assertSee('Jak odwołać');
    }

    public function test_app_calculator_and_pwa_files(): void
    {
        $this->get('/app')->assertOk()->assertSee('tel:112', false)->assertSee(route('map.sos'), false);
        $this->get('/kalkulator')->assertOk()->assertSee('561/2006');
        $this->get('/')->assertSee('manifest.webmanifest', false)->assertSee('serviceWorker', false);
        $this->assertFileExists(public_path('sw.js'));
        $this->assertJson(file_get_contents(public_path('manifest.webmanifest')));
    }
}
