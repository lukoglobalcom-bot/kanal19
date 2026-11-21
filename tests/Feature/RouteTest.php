<?php

namespace Tests\Feature;

use App\Models\MapReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RouteTest extends TestCase
{
    use RefreshDatabase;

    private function driver(): User
    {
        $u = User::create(['name' => 'Jan', 'email' => fake()->unique()->safeEmail(), 'password' => 'haslo1234', 'phone' => '+48500000001']);
        $u->forceFill(['phone_verified_at' => now()])->save();

        return $u;
    }

    private function payload(array $over = []): array
    {
        return array_merge([
            'points' => [['lat' => 52.23, 'lng' => 21.01], ['lat' => 52.41, 'lng' => 16.93]],
            'height' => 4.0, 'width' => 2.55, 'length' => 16.5, 'weight' => 40, 'axle_load' => 11.5, 'axle_count' => 5,
        ], $over);
    }

    private function road(string $problem, float $lat, float $lng, ?float $limit = null): MapReport
    {
        $r = new MapReport(['kind' => 'road', 'lat' => $lat, 'lng' => $lng, 'country' => 'PL', 'data' => array_filter(['problem' => $problem, 'limit' => $limit])]);
        $r->user_id = $this->driver()->id;
        $r->expires_at = now()->addHours(MapReport::ttlFor('road', ['problem' => $problem]));
        $r->save();

        return $r;
    }

    public function test_planner_page_shows_coming_soon_without_server(): void
    {
        config(['services.routing.url' => null]);
        $this->get('/trasa')->assertOk()->assertSee('startuje wkrótce');
        $this->postJson('/trasa/wyznacz', $this->payload())->assertStatus(503);
    }

    public function test_route_is_calculated_for_truck_and_blocked_roads_are_avoided(): void
    {
        config(['services.routing.url' => 'http://valhalla.test', 'services.routing.key' => 'tajne']);
        $this->road('closure', 52.3, 19.0);                 // zamknięta – omijamy
        $this->road('low_bridge', 52.3, 18.0, 4.2);         // wiadukt wyższy niż pojazd – nie omijamy
        $this->road('low_bridge', 52.35, 18.5, 3.5);        // za niski – omijamy
        $this->road('jam', 52.3, 18.2);                     // korek – nie omijamy
        $this->road('closure', 40.0, 3.0);                  // daleko (Hiszpania) – nie dotyczy

        Http::fake(['valhalla.test/*' => Http::response(['trip' => [
            'summary' => ['length' => 310.4, 'time' => 13500, 'has_toll' => true],
            'legs' => [['shape' => '_p~iF~ps|U', 'maneuvers' => [['instruction' => 'Jedź na zachód.', 'length' => 1.2]]]],
        ]])]);

        $this->postJson('/trasa/wyznacz', $this->payload(['hazmat' => true]))->assertOk()
            ->assertJsonPath('routes.0.km', 310.4)->assertJsonPath('routes.0.minutes', 225)
            ->assertJsonPath('routes.0.toll', true)->assertJsonPath('routes.0.steps.0.text', 'Jedź na zachód.')
            ->assertJsonPath('avoided', 2);

        Http::assertSent(function (HttpRequest $r) {
            return $r->url() === 'http://valhalla.test/route' && $r->hasHeader('X-K19-Key', 'tajne')
                && $r['costing'] === 'truck' && $r['costing_options']['truck']['weight'] == 40
                && $r['costing_options']['truck']['hazmat'] === true
                && $r['directions_options']['language'] === 'pl-PL'
                && count($r['exclude_locations']) === 2;
        });
    }

    public function test_route_validation_rejects_bad_vehicle(): void
    {
        $this->postJson('/trasa/wyznacz', $this->payload(['height' => 9]))->assertUnprocessable();
        $this->postJson('/trasa/wyznacz', $this->payload(['points' => [['lat' => 1, 'lng' => 1]]]))->assertUnprocessable();
    }

    public function test_place_search_is_cached(): void
    {
        Http::fake(['nominatim.openstreetmap.org/*' => Http::response([['display_name' => 'Poznań, Polska', 'lat' => '52.4082', 'lon' => '16.9335']])]);
        $this->getJson('/trasa/szukaj?q=Poznań')->assertOk()->assertJsonPath('0.name', 'Poznań, Polska');
        $this->getJson('/trasa/szukaj?q=poznań')->assertOk();
        Http::assertSentCount(1);
    }

    public function test_driver_reports_road_problem_with_limit(): void
    {
        $this->actingAs($this->driver())->postJson('/mapa/zgloszenia', [
            'kind' => 'road', 'lat' => 52.1, 'lng' => 20.1, 'country' => 'PL', 'problem' => 'low_bridge', 'road' => 'dk92', 'limit' => 3.6,
        ])->assertOk();
        $r = MapReport::first();
        $this->assertSame('DK92', $r->data['road']);
        $this->assertEquals(3.6, $r->data['limit']);
        $this->assertTrue($r->expires_at->greaterThan(now()->addDays(29)));
        $this->getJson('/mapa/dane')->assertJsonPath('0.problem_label', 'Niski wiadukt');

        $this->postJson('/mapa/zgloszenia', ['kind' => 'road', 'lat' => 52.1, 'lng' => 20.1, 'country' => 'PL', 'problem' => 'jam', 'limit' => 5])->assertOk();
        $this->assertArrayNotHasKey('limit', MapReport::latest('id')->first()->data);
    }

    public function test_drive_mode_page_and_country_lookup(): void
    {
        $this->get('/jazda')->assertOk()->assertSee('k19-speed', false)->assertDontSee('id="k19-report-btn"', false);
        $this->actingAs($this->driver())->get('/jazda')->assertOk()->assertSee('id="k19-report-btn"', false);

        Http::fake(['nominatim.openstreetmap.org/*' => Http::response(['address' => ['country_code' => 'de']])]);
        $this->getJson('/trasa/kraj?lat=52.52&lng=13.40')->assertOk()->assertJsonPath('country', 'DE');
        $this->getJson('/trasa/kraj?lat=52.53&lng=13.41')->assertOk()->assertJsonPath('country', 'DE');
        Http::assertSentCount(1);
    }

    public function test_passing_drivers_confirm_or_clear_road_problem(): void
    {
        $jam = $this->road('jam', 52.2, 21.0);
        $author = \App\Models\User::find($jam->user_id);
        $this->actingAs($author)->postJson('/mapa/zgloszenia/'.$jam->id.'/glos', ['still' => true])->assertForbidden();

        $this->travel(90)->minutes();
        $this->actingAs($this->driver())->postJson('/mapa/zgloszenia/'.$jam->id.'/glos', ['still' => true])->assertOk();
        $this->assertTrue($jam->fresh()->expires_at->greaterThan(now()->addMinutes(110)));
        $this->getJson('/mapa/dane')->assertJsonPath('0.confirmations', 1);

        // jeden „nie ma” nie wystarcza, dwa przeważające – zgłoszenie znika
        $this->actingAs($this->driver())->postJson('/mapa/zgloszenia/'.$jam->id.'/glos', ['still' => false])->assertOk();
        $this->assertTrue($jam->fresh()->expires_at->isFuture());
        $this->actingAs($this->driver())->postJson('/mapa/zgloszenia/'.$jam->id.'/glos', ['still' => false])->assertOk();
        $this->assertTrue($jam->fresh()->expires_at->isFuture());
        $this->actingAs($this->driver())->postJson('/mapa/zgloszenia/'.$jam->id.'/glos', ['still' => false])->assertOk();
        $this->assertFalse($jam->fresh()->expires_at->isFuture());
        $this->postJson('/mapa/zgloszenia/'.$jam->id.'/glos', ['still' => true])->assertNotFound();
    }

    public function test_parking_counts_tags_status_and_lng_fuel(): void
    {
        $this->actingAs($this->driver())->postJson('/mapa/zgloszenia', [
            'kind' => 'parking', 'lat' => 52.1, 'lng' => 20.1, 'country' => 'PL',
            'free_truck' => 3, 'free_rigid' => 1, 'free_van' => 0, 'free_bus' => 12, 'tags' => ['lit', 'theft'],
        ])->assertOk();
        $d = MapReport::latest('id')->first()->data;
        $this->assertSame(['lit', 'theft'], $d['tags']);
        $this->assertSame(12, $d['free_bus']);
        $this->postJson('/mapa/zgloszenia', ['kind' => 'parking', 'lat' => 52.1, 'lng' => 20.1, 'country' => 'PL', 'tags' => ['pool']])->assertUnprocessable();
        $this->postJson('/mapa/zgloszenia', ['kind' => 'parking', 'lat' => 52.1, 'lng' => 20.1, 'country' => 'PL', 'status' => 'none', 'free_truck' => 0])->assertOk();
        $this->assertSame('none', MapReport::latest('id')->first()->data['status']);

        $this->postJson('/mapa/zgloszenia', ['kind' => 'fuel', 'lat' => 52.1, 'lng' => 20.1, 'country' => 'DE', 'diesel' => 1.69, 'lng_price' => 1.35])->assertOk();
        $fuel = MapReport::latest('id')->first();
        $this->assertEquals(1.35, $fuel->data['lng_price']);
        $this->assertEquals(20.1, $fuel->lng);
        $this->assertSame('EUR', $fuel->data['currency']);

        $this->postJson('/mapa/zgloszenia', ['kind' => 'road', 'lat' => 52.1, 'lng' => 20.1, 'country' => 'PL', 'problem' => 'obstacle'])->assertOk();
        $this->assertTrue(MapReport::latest('id')->first()->expires_at->lessThan(now()->addHours(3)));
    }
}
