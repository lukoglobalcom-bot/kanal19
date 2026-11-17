# Kanał 19 – kanal19.pl

Portal pracy dla kierowców zawodowych. Laravel 13, PHP 8.3+, Blade, Tailwind v4 (Vite), SQLite lub MySQL.

## Zasady projektu (nie łamać)

- **Role**: `driver`, `company`, `advertiser`, `admin`. Pole `role` nie jest w `$fillable` – ustawiamy je przez `forceFill`. Admina zakłada tylko komenda `php artisan kanal19:admin email`.
- **Oferty pracy**: firma zapisuje szkic → podgląd → publikacja z potwierdzeniem. Po publikacji oferta jest widoczna 30 dni (`JobOffer::VISIBLE_DAYS`) i **firma nie może jej edytować**. Edytuje i usuwa tylko admin (`JobOfferPolicy`).
- **Telefon**: każde konto potwierdza numer kodem SMS (`App\Services\SmsSender`, 6 cyfr, 10 min, 3 próby, 60 s między wysyłkami). Na razie kod trafia do `storage/logs/laravel.log` – podmień `deliver()` na bramkę SMS.
- **Baza kierowców**: firma widzi imię, pierwszą literę nazwiska, staż, pojazdy i uprawnienia. Telefon dopiero, gdy kierowca przyjmie zaproszenie albo sam zaaplikuje.
- **Języki**: 25 języków (`App\Support\Languages::ALL`) – polski plus języki UE i ukraiński. Klucze tłumaczeń to polskie zdania, pliki `lang/{kod}.json`. Każdy nowy tekst w widoku piszemy jako `__('Polskie zdanie')` i dopisujemy tłumaczenia do wszystkich 24 plików.
- **Wygląd**: kolory i klasy `k19-*` w `resources/css/app.css` (czerwony #C8221A, grafit #16181D, papier #F4F2EE, fonty Barlow Condensed / Barlow). Zakładki w nagłówku wyglądają jak tablice drogowe (`k19-sign`).
- Adresy URL po polsku (`/oferty`, `/firma/oferty`, `/kierowca`, `/logowanie`).

## Komendy

```sh
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed      # lokalnie wgrywa dane przykładowe (hasło: haslo1234)
npm install && npm run build
php artisan test
php artisan kanal19:admin twoj@email.pl
```

## Kolejne etapy (jeszcze nie zbudowane)

Kampanie reklamodawców (reklama + artykuł w każdym wybranym języku, podgląd, akceptacja bez edycji), giełda pojazdów „Sprzedaj pojazd”, aktualności, przepisy i mandaty, mapa objazdów, planer trasy, aplikacja mobilna z SOS, parkingami, cenami paliw i radiem CB.
