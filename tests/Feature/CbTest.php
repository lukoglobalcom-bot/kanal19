<?php

namespace Tests\Feature;

use App\Models\CbMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CbTest extends TestCase
{
    use RefreshDatabase;

    private function driver(string $first = 'Marek'): User
    {
        $u = User::create(['name' => $first, 'email' => fake()->unique()->safeEmail(), 'password' => 'haslo1234', 'phone' => '+48500000001']);
        $u->forceFill(['phone_verified_at' => now()])->save();
        $u->driverProfile()->create(['first_name' => $first, 'last_name' => 'Nowak', 'experience' => '5-10', 'vehicles' => ['firanka'], 'licences' => ['C+E'], 'in_database' => true]);

        return $u;
    }

    public function test_page_is_public_and_guest_cannot_post(): void
    {
        $this->get('/cb')->assertOk()->assertSee('CB');
        $this->postJson('/cb/wiadomosci', ['channel' => 'PL', 'body' => 'Test'])->assertUnauthorized();
    }

    public function test_driver_posts_and_others_see_short_signature(): void
    {
        $d = $this->driver();
        $this->actingAs($d)->postJson('/cb/wiadomosci', ['channel' => 'PL', 'road' => 'a2', 'body' => 'Korek przed Koninem'])->assertOk();
        $this->postJson('/cb/wiadomosci', ['channel' => 'XX', 'body' => 'Zły kanał'])->assertUnprocessable();
        $this->postJson('/cb/wiadomosci', ['channel' => 'PL', 'road' => '<b>', 'body' => 'Zła droga'])->assertUnprocessable();

        auth()->logout();
        $json = $this->getJson('/cb/wiadomosci?kanal=PL')->assertOk()->json();
        $this->assertCount(1, $json);
        $this->assertSame('Marek N.', $json[0]['who']);
        $this->assertSame('A2', $json[0]['road']);
        $this->assertFalse($json[0]['mine']);
        $this->assertCount(0, $this->getJson('/cb/wiadomosci?kanal=DE')->json());
        $this->assertCount(0, $this->getJson('/cb/wiadomosci?kanal=PL&droga=S8')->json());
        $this->assertCount(1, $this->getJson('/cb/wiadomosci?kanal=PL&droga=a2')->json());
    }

    public function test_messages_expire_after_three_hours(): void
    {
        $this->actingAs($this->driver())->postJson('/cb/wiadomosci', ['channel' => 'EU', 'body' => 'Stara wiadomość'])->assertOk();
        $this->travel(3)->hours();
        $this->travel(1)->minutes();
        $this->assertCount(0, $this->getJson('/cb/wiadomosci?kanal=EU')->json());
    }

    public function test_three_reports_hide_message_and_author_cannot_report_self(): void
    {
        $author = $this->driver();
        $this->actingAs($author)->postJson('/cb/wiadomosci', ['channel' => 'PL', 'body' => 'Spam spam'])->assertOk();
        $m = CbMessage::first();
        $this->postJson('/cb/wiadomosci/'.$m->id.'/zglos')->assertUnprocessable();

        foreach (['Ala', 'Ola', 'Ela'] as $i => $name) {
            $this->actingAs($this->driver($name))->postJson('/cb/wiadomosci/'.$m->id.'/zglos')->assertOk();
            $this->postJson('/cb/wiadomosci/'.$m->id.'/zglos')->assertOk(); // drugi raz tej samej osoby się nie liczy
            $this->assertSame($i === 2, $m->fresh()->hidden);
        }
        $this->assertCount(0, $this->getJson('/cb/wiadomosci?kanal=PL')->json());
    }

    public function test_only_author_or_admin_can_delete(): void
    {
        $author = $this->driver();
        $this->actingAs($author)->postJson('/cb/wiadomosci', ['channel' => 'PL', 'body' => 'Do usunięcia'])->assertOk();
        $m = CbMessage::first();
        $this->actingAs($this->driver('Obcy'))->deleteJson('/cb/wiadomosci/'.$m->id)->assertForbidden();

        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'haslo1234']);
        $admin->forceFill(['role' => 'admin'])->save();
        $this->actingAs($admin)->deleteJson('/cb/wiadomosci/'.$m->id)->assertOk();
        $this->assertSame(0, CbMessage::count());
    }

    public function test_voice_token_only_when_configured_and_signed(): void
    {
        $d = $this->driver();
        config(['services.livekit' => ['url' => null, 'key' => null, 'secret' => null]]);
        $this->actingAs($d)->get('/cb')->assertDontSee('k19-voice-ptt', false);
        $this->postJson('/cb/glos', ['channel' => 'PL'])->assertStatus(503);

        config(['services.livekit' => ['url' => 'wss://cb.test', 'key' => 'APIk19', 'secret' => 'sekret']]);
        $this->get('/cb')->assertSee('k19-voice-ptt', false);
        $res = $this->postJson('/cb/glos', ['channel' => 'DE'])->assertOk()->assertJsonPath('url', 'wss://cb.test');
        [$h, $b, $sig] = explode('.', $res->json('token'));
        $this->assertSame(rtrim(strtr(base64_encode(hash_hmac('sha256', "$h.$b", 'sekret', true)), '+/', '-_'), '='), $sig);
        $claims = json_decode(base64_decode(strtr($b, '-_', '+/')), true);
        $this->assertSame('APIk19', $claims['iss']);
        $this->assertSame('cb-de', $claims['video']['room']);
        $this->assertSame('Marek N.', $claims['name']);
        $this->postJson('/cb/glos', ['channel' => 'XX'])->assertUnprocessable();

        auth()->logout();
        $this->postJson('/cb/glos', ['channel' => 'PL'])->assertUnauthorized();
    }
}
