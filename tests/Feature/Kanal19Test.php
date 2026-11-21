<?php

namespace Tests\Feature;

use App\Models\ContactInvitation;
use App\Models\JobOffer;
use App\Models\SmsCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Kanal19Test extends TestCase
{
    use RefreshDatabase;

    private function company(): User
    {
        $u = User::create(['name' => 'Firma', 'email' => fake()->unique()->safeEmail(), 'password' => 'haslo1234', 'phone' => '+48600000001']);
        $u->forceFill(['role' => 'company', 'phone_verified_at' => now()])->save();
        $u->companyProfile()->create(['name' => 'Firma', 'nip' => '5260250995', 'business_type' => 'carrier']);

        return $u;
    }

    private function driver(bool $verified = true, bool $inDb = true): User
    {
        $u = User::create(['name' => 'Jan Kowalski', 'email' => fake()->unique()->safeEmail(), 'password' => 'haslo1234', 'phone' => '+48500000001']);
        $u->forceFill(['phone_verified_at' => $verified ? now() : null])->save();
        $u->driverProfile()->create(['first_name' => 'Jan', 'last_name' => 'Kowalski', 'experience' => '5-10', 'vehicles' => ['firanka'], 'licences' => ['C+E'], 'in_database' => $inDb]);

        return $u;
    }

    private function offer(User $company, bool $publish = true): JobOffer
    {
        $o = $company->jobOffers()->create([
            'title' => 'Kierowca C+E chłodnia', 'category' => 'ce', 'city' => 'Poznań', 'country' => 'PL',
            'salary_from' => 9000, 'salary_to' => 11000, 'currency' => 'PLN', 'salary_type' => 'brutto',
            'contract' => 'uop', 'work_modes' => ['system'], 'description' => str_repeat('Opis oferty pracy. ', 3), 'language' => 'pl',
        ]);
        if ($publish) {
            $o->publish();
        }

        return $o;
    }

    private function offerPayload(array $over = []): array
    {
        return array_merge([
            'title' => 'Kierowca kat. C', 'category' => 'c', 'city' => 'Warszawa', 'country' => 'pl',
            'salary_from' => 8000, 'salary_to' => 9000, 'currency' => 'PLN', 'salary_type' => 'brutto',
            'contract' => 'uop', 'description' => str_repeat('Dobra praca dla kierowcy. ', 3), 'language' => 'pl',
        ], $over);
    }

    public function test_home_shows_only_active_offers(): void
    {
        $c = $this->company();
        $active = $this->offer($c);
        $draft = $this->offer($c, false);
        $draft->update(['title' => 'Szkic niewidoczny']);
        $old = $this->offer($c);
        $old->forceFill(['title' => 'Stara oferta', 'expires_at' => now()->subDay()])->save();

        $this->get('/')->assertOk()->assertSee($active->title)->assertDontSee('Szkic niewidoczny')->assertDontSee('Stara oferta');
    }

    public function test_offer_expires_after_30_days(): void
    {
        $o = $this->offer($this->company());
        $this->assertTrue($o->isActive());
        $this->travel(31)->days();
        $this->assertFalse($o->fresh()->isActive());
        $this->get('/oferty/'.$o->id)->assertNotFound();
    }

    public function test_driver_registration_requires_sms_verification(): void
    {
        $this->post('/rejestracja', [
            'role' => 'driver', 'first_name' => 'Anna', 'last_name' => 'Nowak', 'experience' => '3-5',
            'vehicles' => ['bus'], 'licences' => ['B', 'C'], 'phone' => '+48 501 222 333', 'locale' => 'uk',
            'email' => 'anna@example.com', 'password' => 'haslo1234', 'password_confirmation' => 'haslo1234', 'terms' => '1',
        ])->assertRedirect(route('verify.show'));

        $user = User::where('email', 'anna@example.com')->first();
        $this->assertSame('driver', $user->role);
        $this->assertSame('uk', $user->locale);
        $this->assertNull($user->phone_verified_at);

        $this->get('/kierowca')->assertRedirect(route('verify.show'));

        SmsCode::where('user_id', $user->id)->update(['code_hash' => Hash::make('123456')]);
        $this->post('/weryfikacja', ['code' => '000000'])->assertSessionHasErrors('code');
        $this->post('/weryfikacja', ['code' => '123456'])->assertRedirect();
        $this->assertNotNull($user->fresh()->phone_verified_at);
        $this->get('/kierowca')->assertOk()->assertSee('Anna');
    }

    public function test_sms_code_locks_after_three_wrong_attempts(): void
    {
        $u = $this->driver(false);
        SmsCode::create(['user_id' => $u->id, 'code_hash' => Hash::make('123456'), 'expires_at' => now()->addMinutes(10)]);
        $this->actingAs($u);
        foreach (range(1, 3) as $i) {
            $this->post('/weryfikacja', ['code' => '111111']);
        }
        $this->post('/weryfikacja', ['code' => '123456'])->assertSessionHasErrors('code');
        $this->assertNull($u->fresh()->phone_verified_at);
    }

    public function test_role_cannot_be_escalated_at_registration(): void
    {
        $this->post('/rejestracja', ['role' => 'admin'])->assertStatus(422);
        $this->assertSame(0, User::count());
    }

    public function test_company_nip_checksum_is_validated(): void
    {
        $this->post('/rejestracja', [
            'role' => 'company', 'company_name' => 'X', 'nip' => '1234567890', 'business_type' => 'carrier',
            'phone' => '+48600111222', 'locale' => 'pl', 'email' => 'x@example.com',
            'password' => 'haslo1234', 'password_confirmation' => 'haslo1234', 'terms' => '1',
        ])->assertSessionHasErrors('nip');
    }

    public function test_company_flow_draft_preview_publish_then_locked(): void
    {
        $c = $this->company();
        $this->actingAs($c)->post('/firma/oferty', $this->offerPayload())->assertRedirect();
        $offer = JobOffer::first();
        $this->assertNull($offer->published_at);
        $this->assertSame('PL', $offer->country);

        $this->put('/firma/oferty/'.$offer->id, $this->offerPayload(['title' => 'Kierowca kat. C poprawione']))->assertRedirect();
        $this->get('/firma/oferty/'.$offer->id.'/podglad')->assertOk()->assertSee('poprawione');

        $this->post('/firma/oferty/'.$offer->id.'/publikuj')->assertSessionHasErrors('confirm');
        $this->post('/firma/oferty/'.$offer->id.'/publikuj', ['confirm' => '1'])->assertRedirect();
        $offer->refresh();
        $this->assertTrue($offer->isActive());
        $this->assertEquals(30, (int) round($offer->published_at->diffInDays($offer->expires_at)));

        $this->get('/firma/oferty/'.$offer->id.'/edytuj')->assertForbidden();
        $this->put('/firma/oferty/'.$offer->id, $this->offerPayload(['title' => 'Zmiana po publikacji']))->assertForbidden();
        $this->assertSame('Kierowca kat. C poprawione', $offer->fresh()->title);
    }

    public function test_company_cannot_touch_other_company_offer(): void
    {
        $draft = $this->offer($this->company(), false);
        $this->actingAs($this->company())->get('/firma/oferty/'.$draft->id.'/edytuj')->assertForbidden();
        $this->get('/firma/oferty/'.$draft->id.'/kandydaci')->assertForbidden();
    }

    public function test_admin_can_edit_and_delete_published_offer(): void
    {
        $o = $this->offer($this->company());
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'haslo1234']);
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($admin)->put('/admin/oferty/'.$o->id, $this->offerPayload(['title' => 'Poprawione przez admina']))->assertRedirect();
        $this->assertSame('Poprawione przez admina', $o->fresh()->title);
        $this->delete('/admin/oferty/'.$o->id)->assertRedirect();
        $this->assertNull(JobOffer::find($o->id));
    }

    public function test_driver_applies_once_and_company_sees_phone(): void
    {
        $c = $this->company();
        $o = $this->offer($c);
        $d = $this->driver();

        $this->actingAs($d)->post('/oferty/'.$o->id.'/aplikuj', ['message' => 'Chętnie'])->assertRedirect();
        $this->post('/oferty/'.$o->id.'/aplikuj')->assertRedirect();
        $this->assertSame(1, $o->applications()->count());

        $this->actingAs($c)->get('/firma/oferty/'.$o->id.'/kandydaci')->assertOk()->assertSee($d->phone);
    }

    public function test_unverified_driver_cannot_apply(): void
    {
        $o = $this->offer($this->company());
        $this->actingAs($this->driver(false))->post('/oferty/'.$o->id.'/aplikuj')->assertRedirect(route('verify.show'));
        $this->assertSame(0, $o->applications()->count());
    }

    public function test_driver_database_hides_phone_until_invitation_accepted(): void
    {
        $c = $this->company();
        $d = $this->driver();
        $hidden = $this->driver(true, false);
        $hidden->driverProfile->update(['first_name' => 'Ukryty']);

        $this->actingAs($c)->get('/firma/kierowcy')->assertOk()->assertSee('Jan')->assertDontSee($d->phone)->assertDontSee('Ukryty');
        $this->get('/firma/kierowcy?uprawnienia[]=D')->assertOk()->assertDontSee('Kowalski');

        $this->post('/firma/kierowcy/'.$d->id.'/zapros', ['message' => 'Mamy trasę'])->assertRedirect();
        $inv = ContactInvitation::first();
        $this->assertNull($inv->accepted_at);

        $this->actingAs($this->driver())->post('/kierowca/zaproszenia/'.$inv->id)->assertForbidden();
        $this->actingAs($d)->post('/kierowca/zaproszenia/'.$inv->id)->assertRedirect();
        $this->assertNotNull($inv->fresh()->accepted_at);
        $this->actingAs($c)->get('/firma/kierowcy')->assertSee($d->phone);
    }

    public function test_driver_cannot_open_company_or_admin_panels(): void
    {
        $this->actingAs($this->driver());
        $this->get('/firma/oferty')->assertForbidden();
        $this->get('/admin')->assertForbidden();
    }

    public function test_blocked_user_is_logged_out(): void
    {
        $c = $this->company();
        $c->forceFill(['blocked_at' => now()])->save();
        $this->actingAs($c)->get('/firma/oferty')->assertRedirect();
        $this->assertGuest();
    }

    public function test_language_switch_changes_texts(): void
    {
        $this->post('/jezyk', ['locale' => 'en'])->assertRedirect();
        $this->get('/')->assertOk()->assertSee('lang="en"', false);
        $this->post('/jezyk', ['locale' => 'xx'])->assertSessionHasErrors('locale');
    }

    public function test_without_sms_gateway_registration_skips_verification(): void
    {
        config(['services.smsapi.required' => false, 'services.smsapi.token' => null]);
        $this->post('/rejestracja', [
            'role' => 'driver', 'first_name' => 'Ewa', 'last_name' => 'Bez', 'experience' => '1-3',
            'vehicles' => ['bus'], 'licences' => ['B'], 'phone' => '+48 501 000 111', 'locale' => 'pl',
            'email' => 'ewa@example.com', 'password' => 'haslo1234', 'password_confirmation' => 'haslo1234', 'terms' => '1',
        ])->assertRedirect(route('driver.profile'));
        $this->get('/kierowca')->assertOk();
        $this->get('/sprzedaj-pojazd')->assertOk();
        $this->assertNull(\App\Models\User::where('email', 'ewa@example.com')->value('phone_verified_at'));
    }

    public function test_offer_has_google_jobs_data_and_sitemap_lists_only_active(): void
    {
        $c = $this->company();
        $o = $this->offer($c);
        $old = $this->offer($c);
        $old->forceFill(['expires_at' => now()->subDay()])->save();

        $html = $this->get('/oferty/'.$o->id)->assertOk()->assertSee('<meta name="description"', false)->getContent();
        preg_match('#<script type="application/ld\+json">(.+?)</script>#s', $html, $m);
        $ld = json_decode($m[1], true);
        $this->assertSame('JobPosting', $ld['@type']);
        $this->assertSame('Kierowca C+E chłodnia', $ld['title']);
        $this->assertSame('Firma', $ld['hiringOrganization']['name']);
        $this->assertSame('PL', $ld['jobLocation']['address']['addressCountry']);
        $this->assertEquals(9000, $ld['baseSalary']['value']['minValue']);
        $this->assertSame('MONTH', $ld['baseSalary']['value']['unitText']);
        $this->assertSame('FULL_TIME', $ld['employmentType']);
        $this->assertNotEmpty($ld['validThrough']);

        $xml = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->getContent();
        $this->assertStringContainsString(route('offers.show', $o), $xml);
        $this->assertStringNotContainsString('/oferty/'.$old->id.'<', $xml);
        $this->assertStringContainsString(route('pause'), $xml);
    }
}
