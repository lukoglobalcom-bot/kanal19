<?php

namespace Tests\Feature;

use App\Models\CompanyReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyReviewTest extends TestCase
{
    use RefreshDatabase;

    private function company(): User
    {
        $u = User::create(['name' => 'Firma', 'email' => fake()->unique()->safeEmail(), 'password' => 'haslo1234', 'phone' => '+48600000001']);
        $u->forceFill(['role' => 'company', 'phone_verified_at' => now()])->save();
        $u->companyProfile()->create(['name' => 'Trans-Pol', 'nip' => '5260250995', 'business_type' => 'carrier']);

        return $u;
    }

    private function driver(string $first = 'Marek'): User
    {
        $u = User::create(['name' => $first, 'email' => fake()->unique()->safeEmail(), 'password' => 'haslo1234', 'phone' => '+48500000001']);
        $u->forceFill(['phone_verified_at' => now()])->save();
        $u->driverProfile()->create(['first_name' => $first, 'last_name' => 'Nowak', 'experience' => '5-10', 'vehicles' => ['firanka'], 'licences' => ['C+E'], 'in_database' => true]);

        return $u;
    }

    private function payload(array $over = []): array
    {
        return array_merge(['rating' => 4, 'pay' => 5, 'vehicles' => 3, 'comment' => 'Płacą na czas, auta trochę stare, dyspozytor w porządku.', 'worked_there' => '1', 'rules' => '1'], $over);
    }

    public function test_driver_reviews_company_once_and_can_update(): void
    {
        $c = $this->company();
        $d = $this->driver();
        $this->get('/firmy/'.$c->id)->assertOk()->assertSee('Trans-Pol')->assertSee('Brak opinii');

        $this->actingAs($d)->post('/firmy/'.$c->id.'/opinia', $this->payload(['rules' => null]))->assertSessionHasErrors('rules');
        $this->post('/firmy/'.$c->id.'/opinia', $this->payload(['comment' => 'za krótko']))->assertSessionHasErrors('comment');
        $this->post('/firmy/'.$c->id.'/opinia', $this->payload())->assertRedirect();
        $this->post('/firmy/'.$c->id.'/opinia', $this->payload(['rating' => 2]))->assertRedirect();
        $this->assertSame(1, CompanyReview::count());
        $this->assertSame(2, CompanyReview::first()->rating);

        $this->actingAs($this->driver('Ola'))->post('/firmy/'.$c->id.'/opinia', $this->payload(['rating' => 5]))->assertRedirect();
        $this->assertSame(['avg' => 3.5, 'count' => 2], CompanyReview::summaryFor($c->id));
        $this->get('/firmy/'.$c->id)->assertSee('Marek N.')->assertSee('3,5');
    }

    public function test_only_drivers_review_and_company_page_only_for_companies(): void
    {
        $c = $this->company();
        $this->actingAs($this->company())->post('/firmy/'.$c->id.'/opinia', $this->payload())->assertForbidden();
        $this->get('/firmy/'.$this->driver()->id)->assertNotFound();
        $this->post('/firmy/999/opinia', $this->payload())->assertNotFound();
    }

    public function test_company_replies_reports_hide_and_admin_deletes(): void
    {
        $c = $this->company();
        $author = $this->driver();
        $this->actingAs($author)->post('/firmy/'.$c->id.'/opinia', $this->payload());
        $r = CompanyReview::first();

        $this->actingAs($this->company())->post('/opinie/'.$r->id.'/odpowiedz', ['reply' => 'Nie nasza sprawa'])->assertForbidden();
        $this->actingAs($c)->post('/opinie/'.$r->id.'/odpowiedz', ['reply' => 'Dziękujemy, wymieniamy flotę.'])->assertRedirect();
        $this->get('/firmy/'.$c->id)->assertSee('wymieniamy flotę');

        $this->actingAs($author)->post('/opinie/'.$r->id.'/zglos')->assertStatus(422);
        foreach (['A', 'B', 'C'] as $n) {
            $this->actingAs($this->driver($n))->post('/opinie/'.$r->id.'/zglos')->assertRedirect();
        }
        $this->assertTrue($r->fresh()->hidden);
        $this->assertNull(CompanyReview::summaryFor($c->id));

        $this->actingAs($this->driver('X'))->delete('/opinie/'.$r->id)->assertForbidden();
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'haslo1234']);
        $admin->forceFill(['role' => 'admin'])->save();
        $this->actingAs($admin)->delete('/opinie/'.$r->id)->assertRedirect();
        $this->assertSame(0, CompanyReview::count());
    }

    public function test_offer_links_to_company_page_with_rating_and_mobile_menu(): void
    {
        $c = $this->company();
        $o = $c->jobOffers()->create(['title' => 'Kierowca C+E', 'category' => 'ce', 'city' => 'Poznań', 'country' => 'PL', 'salary_from' => 9000, 'currency' => 'PLN', 'salary_type' => 'brutto', 'contract' => 'uop', 'description' => str_repeat('Opis oferty. ', 3), 'language' => 'pl']);
        $o->publish();
        $this->actingAs($this->driver())->post('/firmy/'.$c->id.'/opinia', $this->payload(['rating' => 5]));
        $this->get('/oferty/'.$o->id)->assertSee(route('companies.show', $c))->assertSee('★ 5,0 (1)')->assertSee('Menu aplikacji');
    }
}
