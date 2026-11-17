<?php

namespace Tests\Feature;

use App\Models\AdCampaign;
use App\Models\User;
use App\Models\VehicleListing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class Stage2Test extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        File::deleteDirectory(public_path('uploads/pojazdy'));
        File::deleteDirectory(public_path('uploads/reklamy'));
        parent::tearDown();
    }

    private function user(string $role = 'driver', bool $verified = true): User
    {
        $u = User::create(['name' => 'U '.$role, 'email' => fake()->unique()->safeEmail(), 'password' => 'haslo1234', 'phone' => '+48500000009']);
        $u->forceFill(['role' => $role, 'phone_verified_at' => $verified ? now() : null])->save();
        if ($role === 'advertiser') {
            $u->advertiserProfile()->create(['brand' => 'Opony XYZ', 'nip' => '5260250995']);
        }

        return $u;
    }

    private function vehiclePayload(array $over = []): array
    {
        return array_merge([
            'type' => 'ciagnik', 'condition' => 'used', 'make' => 'Volvo', 'model' => 'FH 500', 'year' => 2020,
            'mileage' => 450000, 'power_hp' => 500, 'fuel' => 'diesel', 'gearbox' => 'auto', 'euro' => 'euro6',
            'price' => 289000, 'currency' => 'PLN', 'price_type' => 'netto', 'vat_invoice' => '1',
            'city' => 'Poznań', 'country' => 'pl', 'description' => 'Ciągnik w bardzo dobrym stanie, serwisowany w ASO.',
            'phone' => '+48 600 100 200',
        ], $over);
    }

    public function test_vehicle_flow_draft_preview_publish_with_photos(): void
    {
        $u = $this->user();
        $this->actingAs($u)->post('/pojazdy', $this->vehiclePayload([
            'photos' => [UploadedFile::fake()->image('a.jpg', 800, 600), UploadedFile::fake()->image('b.png', 800, 600)],
        ]))->assertRedirect();

        $v = VehicleListing::first();
        $this->assertNull($v->published_at);
        $this->assertSame('PL', $v->country);
        $this->assertCount(2, $v->photos);
        $this->assertFileExists(public_path($v->photos[0]->path));

        $this->get('/pojazdy')->assertDontSee('FH 500');
        $this->get('/pojazdy/'.$v->id.'/podglad')->assertOk()->assertSee('FH 500');
        $this->post('/pojazdy/'.$v->id.'/publikuj')->assertRedirect();
        $this->assertTrue($v->fresh()->isActive());

        auth()->logout();
        $this->get('/pojazdy?typ=ciagnik&stan=used')->assertOk()->assertSee('FH 500');
        $this->get('/pojazdy?stan=new')->assertOk()->assertDontSee('FH 500');
        $this->get('/pojazdy/'.$v->id)->assertOk()->assertSee('289 000');
        $this->get('/')->assertOk()->assertSee('FH 500');
    }

    public function test_vehicle_rejects_non_images_and_other_users(): void
    {
        $owner = $this->user();
        $this->actingAs($owner)->post('/pojazdy', $this->vehiclePayload([
            'photos' => [UploadedFile::fake()->create('shell.php', 10, 'application/x-php')],
        ]))->assertSessionHasErrors('photos.0');
        $this->assertSame(0, VehicleListing::count());

        $this->post('/pojazdy', $this->vehiclePayload())->assertRedirect();
        $v = VehicleListing::first();

        $this->actingAs($this->user())->put('/pojazdy/'.$v->id, $this->vehiclePayload(['price' => 1]))->assertForbidden();
        $this->delete('/pojazdy/'.$v->id)->assertForbidden();
        $this->get('/pojazdy/'.$v->id)->assertNotFound(); // szkic niewidoczny dla innych
    }

    public function test_unverified_user_cannot_sell(): void
    {
        $this->actingAs($this->user('driver', false))->get('/sprzedaj-pojazd')->assertRedirect(route('verify.show'));
    }

    public function test_campaign_requires_all_languages_then_locks(): void
    {
        $adv = $this->user('advertiser');
        $this->actingAs($adv)->post('/reklamodawca/kampanie', [
            'name' => 'Zimowe opony', 'format' => 'billboard', 'target_url' => 'https://example.com', 'languages' => ['pl', 'de'],
        ])->assertRedirect();
        $c = AdCampaign::first();

        $creative = fn ($h) => ['headline' => $h, 'text' => 'Opony zimowe dla ciężarówek', 'article_title' => 'Jak wybrać opony', 'article_body' => str_repeat('Treść artykułu. ', 10)];
        $this->post("/kampanie/{$c->id}/jezyk/pl", $creative('Opony PL'))->assertRedirect(route('advertiser.campaigns.edit', [$c, 'jezyk' => 'de']));
        $this->post("/kampanie/{$c->id}/jezyk/fr", $creative('X'))->assertNotFound();

        $this->post("/reklamodawca/kampanie/{$c->id}/akceptuj", ['confirm' => '1'])->assertSessionHasErrors('confirm');
        $this->assertNull($c->fresh()->accepted_at);

        $this->post("/kampanie/{$c->id}/jezyk/de", $creative('Reifen DE'))->assertRedirect(route('advertiser.campaigns.preview', $c));
        $this->get("/kampanie/{$c->id}/podglad")->assertOk()->assertSee('Opony PL')->assertSee('Reifen DE');
        $this->post("/reklamodawca/kampanie/{$c->id}/akceptuj", ['confirm' => '1'])->assertRedirect();
        $this->assertNotNull($c->fresh()->accepted_at);

        // Po akceptacji reklamodawca nie może zmieniać
        $this->post("/kampanie/{$c->id}/jezyk/pl", $creative('Zmiana'))->assertForbidden();
        $this->put("/kampanie/{$c->id}", ['name' => 'X', 'format' => 'box', 'target_url' => 'https://x.com', 'languages' => ['pl']])->assertForbidden();

        // Nie wyświetla się, dopóki admin nie włączy
        auth()->logout();
        $this->get('/')->assertDontSee('Opony PL');

        $admin = $this->user('admin');
        $this->actingAs($admin)->post("/admin/kampanie/{$c->id}/wlacz")->assertRedirect();
        $this->post("/kampanie/{$c->id}/jezyk/pl", $creative('Opony PL poprawione'))->assertRedirect();
        auth()->logout();

        $this->get('/')->assertSee('Opony PL poprawione')->assertDontSee('Reifen DE');
        $this->withSession(['locale' => 'de'])->get('/')->assertSee('Reifen DE');
        $this->withSession(['locale' => 'fr'])->get('/')->assertDontSee('Reifen DE');
    }

    public function test_other_roles_cannot_open_campaigns(): void
    {
        $adv = $this->user('advertiser');
        $c = new AdCampaign(['name' => 'A', 'format' => 'box', 'target_url' => 'https://a.pl', 'languages' => ['pl']]);
        $c->user_id = $adv->id;
        $c->save();

        $this->actingAs($this->user('company'))->get("/kampanie/{$c->id}")->assertForbidden();
        $this->get('/reklamodawca')->assertForbidden();
        $this->actingAs($this->user('advertiser'))->get("/kampanie/{$c->id}")->assertForbidden();
    }
}
