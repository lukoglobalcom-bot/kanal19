<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SmsSender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SmsGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_without_token_no_http_request_is_made(): void
    {
        Http::fake();
        $u = User::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'haslo1234', 'phone' => '+48500100200']);
        $this->assertTrue(app(SmsSender::class)->sendCode($u));
        Http::assertNothingSent();
    }

    public function test_with_token_sms_goes_to_smsapi(): void
    {
        config(['services.smsapi.token' => 'test-token', 'services.smsapi.sender' => 'Kanal19']);
        Http::fake(['api.smsapi.pl/*' => Http::response(['count' => 1, 'list' => []])]);
        $u = User::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'haslo1234', 'phone' => '+48500100200']);

        $this->assertTrue(app(SmsSender::class)->sendCode($u));

        Http::assertSent(fn (Request $r) => $r->url() === 'https://api.smsapi.pl/sms.do'
            && $r->hasHeader('Authorization', 'Bearer test-token')
            && $r['to'] === '48500100200'
            && $r['from'] === 'Kanal19'
            && preg_match('/\d{6}/', $r['message']));
    }
}
