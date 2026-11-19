<?php

namespace Tests\Feature;

use App\Models\MapReport;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    private function driver(): User
    {
        $u = User::create(['name' => 'Jan', 'email' => fake()->unique()->safeEmail(), 'password' => 'haslo1234', 'phone' => '+48500000001']);
        $u->forceFill(['phone_verified_at' => now()])->save();

        return $u;
    }

    private function admin(): User
    {
        $a = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'haslo1234']);
        $a->forceFill(['role' => 'admin'])->save();

        return $a;
    }

    public function test_legal_pages_are_public_and_linked(): void
    {
        $this->get('/regulamin')->assertOk()->assertSee('Funkcja SOS nie zastępuje numeru alarmowego')->assertSee('admin@kanal19.pl');
        $this->get('/polityka-prywatnosci')->assertOk()->assertSee('nie jest nagrywany', false);
        $this->get('/')->assertSee(route('terms'))->assertSee(route('privacy'));
        $this->get('/rejestracja')->assertSee(route('terms'));

        $this->withSession(['locale' => 'de'])->get('/regulamin')->assertOk()->assertSee('Terms of Service')->assertSee('binding version is the Polish one');
    }

    public function test_admin_fills_operator_data_shown_in_documents(): void
    {
        $this->actingAs($this->admin())->get('/admin')->assertSee('Uzupełnij dane operatora');
        $this->actingAs($this->driver())->post('/admin/operator', ['operator_name' => 'X'])->assertForbidden();

        $this->actingAs(User::where('role', 'admin')->first())->post('/admin/operator', [
            'operator_name' => 'Kanał 19 sp. z o.o.', 'operator_address' => 'ul. Drogowa 19, 00-019 Warszawa',
            'operator_nip' => '5260250995', 'operator_email' => 'kontakt@kanal19.pl',
        ])->assertRedirect();
        $this->assertTrue(Setting::operatorComplete());
        $this->get('/admin')->assertDontSee('Uzupełnij dane operatora');
        $this->get('/polityka-prywatnosci')->assertSee('Kanał 19 sp. z o.o.')->assertSee('NIP 5260250995')->assertSee('kontakt@kanal19.pl');
    }

    public function test_user_deletes_account_with_password(): void
    {
        $u = $this->driver();
        $r = new MapReport(['kind' => 'road', 'lat' => 52, 'lng' => 21, 'country' => 'PL', 'data' => ['problem' => 'jam']]);
        $r->user_id = $u->id;
        $r->expires_at = now()->addHour();
        $r->save();

        $this->actingAs($u)->get('/konto')->assertOk()->assertSee('Usuń konto');
        $this->delete('/konto', ['password' => 'zle-haslo', 'confirm' => '1'])->assertSessionHasErrors('password');
        $this->delete('/konto', ['password' => 'haslo1234'])->assertSessionHasErrors('confirm');
        $this->assertNotNull(User::find($u->id));

        $this->delete('/konto', ['password' => 'haslo1234', 'confirm' => '1'])->assertRedirect(route('home'));
        $this->assertGuest();
        $this->assertNull(User::find($u->id));
        $this->assertSame(0, MapReport::count());
    }

    public function test_admin_account_cannot_be_self_deleted(): void
    {
        $this->actingAs($this->admin())->get('/konto')->assertOk()->assertDontSee('Usuń konto na zawsze');
        $this->delete('/konto', ['password' => 'haslo1234', 'confirm' => '1'])->assertForbidden();
    }
}
