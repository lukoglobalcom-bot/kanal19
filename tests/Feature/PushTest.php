<?php

namespace Tests\Feature;

use App\Models\MapReport;
use App\Models\PushSubscription;
use App\Models\User;
use App\Services\Push;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PushTest extends TestCase
{
    use RefreshDatabase;

    private function driver(): User
    {
        $u = User::create(['name' => 'Jan', 'email' => fake()->unique()->safeEmail(), 'password' => 'haslo1234', 'phone' => '+48500000001']);
        $u->forceFill(['phone_verified_at' => now()])->save();

        return $u;
    }

    private function subscribe(User $u, float $lat, float $lng, int $radius = 50): void
    {
        $this->actingAs($u)->postJson('/powiadomienia', [
            'endpoint' => 'https://push.example.com/'.fake()->uuid(),
            'keys' => ['p256dh' => 'BPk'.str_repeat('a', 84), 'auth' => 'abcdefghijklmnopqrstuv'],
            'radius_km' => $radius, 'lat' => $lat, 'lng' => $lng,
        ])->assertOk();
    }

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_vapid_keys_are_created_once(): void
    {
        $key = $this->getJson('/powiadomienia/klucz')->assertUnauthorized();
        $this->actingAs($this->driver());
        $k1 = $this->getJson('/powiadomienia/klucz')->assertOk()->json('key');
        $this->assertSame($k1, Push::publicKey());
        $this->assertGreaterThan(80, strlen($k1));
    }

    public function test_subscription_saved_updated_and_removed(): void
    {
        $u = $this->driver();
        $this->subscribe($u, 52.2, 21.0, 25);
        $sub = PushSubscription::first();
        $this->assertSame(25, $sub->radius_km);
        $this->assertNotNull($sub->located_at);

        $this->postJson('/powiadomienia/pozycja', ['endpoint' => $sub->endpoint, 'lat' => 50.06, 'lng' => 19.94])->assertJsonPath('ok', true);
        $this->assertEquals(50.06, $sub->fresh()->lat);

        // cudza subskrypcja nie zmienia się
        $this->actingAs($this->driver())->postJson('/powiadomienia/pozycja', ['endpoint' => $sub->endpoint, 'lat' => 1, 'lng' => 1])->assertJsonPath('ok', false);

        $this->actingAs($u)->deleteJson('/powiadomienia', ['endpoint' => $sub->endpoint])->assertOk();
        $this->assertSame(0, PushSubscription::count());
    }

    public function test_sos_targets_only_nearby_recent_subscribers(): void
    {
        $caller = $this->driver();
        $near = $this->driver();
        $far = $this->driver();
        $stale = $this->driver();
        $this->subscribe($near, 52.30, 21.05, 50);   // ok. 11 km
        $this->subscribe($far, 50.06, 19.94, 50);    // Kraków – za daleko
        $this->subscribe($stale, 52.25, 21.01, 50);
        PushSubscription::where('user_id', $stale->id)->update(['located_at' => now()->subDays(2)]);
        $this->subscribe($caller, 52.23, 21.01, 50); // sam wzywający – bez powiadomienia

        $report = new MapReport(['kind' => 'sos', 'lat' => 52.23, 'lng' => 21.01, 'country' => 'PL', 'data' => ['problem' => 'health']]);
        $report->user_id = $caller->id;
        $report->expires_at = now()->addHours(6);
        $report->save();

        $targets = Push::targets($report);
        $this->assertSame([$near->id], $targets->map(fn ($t) => $t[0]->user_id)->all());
        $this->assertEqualsWithDelta(8.5, $targets[0][1], 3);

        // Wysyłka do nieistniejącego serwera push nie może zepsuć wezwania SOS
        $this->actingAs($caller)->postJson('/mapa/zgloszenia', ['kind' => 'sos', 'problem' => 'vehicle', 'lat' => 52.23, 'lng' => 21.01, 'country' => 'PL'])->assertOk();
    }
}
