# Kanał 19 – kanal19.pl

Portal pracy dla kierowców zawodowych. To jest **pierwszy etap** portalu:

- strona główna z ofertami, wyszukiwarką, 10 kategoriami i filtrami;
- konta: kierowca, firma, reklamodawca (+ administrator);
- potwierdzanie telefonu kodem SMS;
- oferty pracy: szkic → podgląd → publikacja, widoczne 30 dni, po publikacji bez edycji (edytuje tylko admin);
- aplikowanie jednym kliknięciem, lista kandydatów z telefonami;
- baza kierowców (za zgodą kierowcy), zaproszenia od firm, telefon widoczny dopiero po akceptacji;
- panel administratora: statystyki, blokowanie kont, edycja i usuwanie ofert;
- 25 języków (polski, wszystkie języki UE, ukraiński) – wybierane przy rejestracji i w nagłówku.

## Wgranie na hosting DirectAdmin (unixstorm)

Paczka `kanal19-hosting.zip` ma dwa foldery:

| Folder w paczce | Gdzie wgrać w DirectAdmin |
|---|---|
| `kanal19/` | `domains/kanal19.pl/kanal19/` (nowy folder obok `public_html`) |
| `public_html/` | zawartość do `domains/kanal19.pl/public_html/` |

1. **PHP 8.3 lub nowszy**: DirectAdmin → „Wybór wersji PHP” → dla kanal19.pl wybierz 8.3 / 8.4.
2. **Kopia zapasowa**: obecną zawartość `public_html` przenieś do folderu np. `public_html_stare`.
3. **Wgraj pliki**: Menedżer plików → „Prześlij” → wgraj `kanal19-hosting.zip` do `domains/kanal19.pl/` → kliknij na pliku „Rozpakuj”.
4. **Uprawnienia**: foldery `kanal19/storage`, `kanal19/bootstrap/cache` i `kanal19/database` muszą mieć prawa zapisu (755 wystarczy, gdy właścicielem jest Twój użytkownik).
5. **Plik `.env`** w `kanal19/` jest już przygotowany (baza SQLite, klucz aplikacji, język polski). Nie trzeba zakładać bazy MySQL.
6. **Konto administratora**: w DirectAdmin otwórz „Terminal” / połącz się przez SSH i wpisz:
   ```sh
   cd ~/domains/kanal19.pl/kanal19
   php artisan kanal19:admin twoj@email.pl
   ```
   Hasło pokaże się raz na ekranie – zapisz je.
7. Wejdź na **https://kanal19.pl** – strona powinna działać.

### SMS-y

Na razie kody SMS **nie są wysyłane**, tylko zapisywane w pliku `kanal19/storage/logs/laravel.log` (szukaj linii `[SMS do +48…]`). Żeby wysyłać prawdziwe SMS-y, załóż konto w bramce SMS (np. SMSAPI, SerwerSMS) i podmień metodę `deliver()` w `app/Services/SmsSender.php`.

### MySQL zamiast SQLite (opcjonalnie)

W `kanal19/.env` zmień:
```
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=nazwa_bazy
DB_USERNAME=uzytkownik
DB_PASSWORD=haslo
```
i uruchom `php artisan migrate --force`.

## Praca lokalna (dla programisty)

```sh
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite && php artisan migrate --seed
npm install && npm run build
php artisan serve
php artisan test
```

Konta przykładowe po `--seed` (hasło `haslo1234`): `firma1@kanal19.test`, `kierowca1@kanal19.test`.
