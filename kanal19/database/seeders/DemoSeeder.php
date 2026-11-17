<?php

namespace Database\Seeders;

use App\Models\JobOffer;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Przykładowe firmy, kierowcy i oferty do testów lokalnych. Hasło wszystkich kont: haslo1234 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $companies = [];
        foreach ([['Trans-Pol Logistics', '5260250995'], ['EuroCargo', '7792433421'], ['Baltic Bus', '5842735218'], ['CityTaxi Kraków', '6762392542']] as $i => [$name, $nip]) {
            $u = User::create(['name' => $name, 'email' => 'firma'.($i + 1).'@kanal19.test', 'password' => 'haslo1234', 'phone' => '+4860000000'.$i, 'locale' => 'pl']);
            $u->forceFill(['role' => 'company', 'phone_verified_at' => now()])->save();
            $u->companyProfile()->create(['name' => $name, 'nip' => $nip, 'business_type' => 'carrier']);
            $companies[] = $u;
        }

        $drivers = [
            ['Marek', 'Nowak', 'gt10', ['firanka', 'chlodnia'], ['C', 'C+E', 'Kod 95', 'ADR']],
            ['Ołena', 'Kowalczuk', '3-5', ['bus', 'solowka'], ['B', 'C', 'Kod 95']],
            ['Piotr', 'Wiśniewski', '5-10', ['autobus'], ['D', 'Kod 95']],
        ];
        foreach ($drivers as $i => [$first, $last, $exp, $veh, $lic]) {
            $u = User::create(['name' => "$first $last", 'email' => 'kierowca'.($i + 1).'@kanal19.test', 'password' => 'haslo1234', 'phone' => '+4850000000'.$i, 'locale' => $i === 1 ? 'uk' : 'pl']);
            $u->forceFill(['phone_verified_at' => now()])->save();
            $u->driverProfile()->create(['first_name' => $first, 'last_name' => $last, 'experience' => $exp, 'vehicles' => $veh, 'licences' => $lic, 'in_database' => true]);
        }

        $offers = [
            ['Kierowca C+E – chłodnia, trasy międzynarodowe', 'ce', 'Poznań', 'PL', 10500, 13000, 'PLN', 'uop', ['system'], 0],
            ['Kierowca C+E – firanka, kraj, codziennie w domu', 'ce', 'Wrocław', 'PL', 9000, 10500, 'PLN', 'uop', ['home_daily'], 1],
            ['Kierowca kat. C – dystrybucja lokalna', 'c', 'Warszawa', 'PL', 7800, 8800, 'PLN', 'uop', ['home_daily', 'weekends_off'], 0],
            ['Kierowca autobusu – komunikacja miejska', 'd', 'Gdańsk', 'PL', 7500, 8500, 'PLN', 'uop', ['weekends_off'], 2],
            ['Kierowca ADR – cysterna paliwowa', 'adr', 'Płock', 'PL', 11000, 14000, 'PLN', 'uop', ['home_daily'], 1],
            ['Kierowca C+E – Niemcy, system 3/1', 'abroad', 'Hamburg', 'DE', 2900, 3400, 'EUR', 'b2b', ['system'], 1],
            ['Kurier – samochód firmowy kat. B', 'kurier', 'Kraków', 'PL', 6000, 7200, 'PLN', 'zlecenie', ['weekends_off'], 3],
            ['Kierowca taxi – elastyczne godziny', 'taxi', 'Kraków', 'PL', 5000, 9000, 'PLN', 'b2b', [], 3],
            ['Operator HDS – budowa', 'bud', 'Łódź', 'PL', 8500, 10000, 'PLN', 'uop', ['home_daily'], 0],
            ['Kierowca kat. B – bez doświadczenia, przyuczenie', 'newbie', 'Lublin', 'PL', 5200, 6000, 'PLN', 'uop', ['home_daily', 'weekends_off'], 2],
        ];
        foreach ($offers as $i => [$title, $cat, $city, $country, $from, $to, $cur, $contract, $modes, $c]) {
            $offer = $companies[$c]->jobOffers()->create([
                'title' => $title, 'category' => $cat, 'city' => $city, 'country' => $country,
                'salary_from' => $from, 'salary_to' => $to, 'currency' => $cur, 'salary_type' => 'brutto',
                'contract' => $contract, 'work_modes' => $modes, 'no_cv' => $i % 2 === 0, 'language' => 'pl',
                'description' => "Szukamy kierowcy do stałej współpracy. Zapewniamy zadbany, nowy sprzęt, terminowe wypłaty i wsparcie spedycji 24/7.\n\nZgłoś się przez Kanał 19 – oddzwonimy w ciągu doby.",
                'requirements' => 'Aktualne uprawnienia, karta kierowcy, Kod 95. Mile widziane doświadczenie.',
            ]);
            $offer->publish();
            $offer->forceFill(['published_at' => now()->subHours($i * 7), 'expires_at' => now()->subHours($i * 7)->addDays(JobOffer::VISIBLE_DAYS)])->save();
        }
    }
}
