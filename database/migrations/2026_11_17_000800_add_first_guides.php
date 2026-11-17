<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Pierwsze poradniki (stan prawny: listopad 2026). Dodawane raz, autor = pierwszy administrator.
     * Administrator może je potem poprawiać w panelu „Aktualności”.
     */
    public function up(): void
    {
        $admin = DB::table('users')->where('role', 'admin')->orderBy('id')->value('id');
        if (! $admin || DB::table('posts')->where('title', 'like', 'Mandat drogowy:%')->exists()) {
            return;
        }

        $now = now();
        DB::table('posts')->insert([
            [
                'user_id' => $admin, 'category' => 'mandaty', 'locale' => 'pl',
                'title' => 'Mandat drogowy: możesz go nie przyjąć, a przyjęty – tylko w wyjątkowych sprawach uchylić',
                'excerpt' => 'Kiedy odmówić przyjęcia mandatu, co dzieje się potem i jak w ciągu 7 dni złożyć wniosek o uchylenie prawomocnego mandatu.',
                'body' => <<<'TXT'
1. Możesz odmówić przyjęcia mandatu
Policjant lub inspektor, który proponuje mandat karny, powinien poinformować Cię, że możesz go nie przyjąć. Jeśli odmówisz, sprawa nie kończy się na drodze: organ kieruje do sądu wniosek o ukaranie i to sąd decyduje o Twojej winie i karze. Sąd może orzec inną karę niż ta z mandatu – także wyższą – i obciążyć Cię kosztami postępowania. Odmowa ma sens, gdy masz dowody, że wykroczenia nie było (np. nagranie z kamerki, zapis tachografu, świadków).

2. Przyjęty mandat jest prawomocny
Gdy przyjmiesz mandat (podpiszesz go), staje się prawomocny. Od prawomocnego mandatu nie ma zwykłego odwołania. Można go jedynie uchylić w sytuacjach wymienionych w art. 101 Kodeksu postępowania w sprawach o wykroczenia.

3. Kiedy sąd uchyla prawomocny mandat (art. 101 k.p.w.)
– grzywnę nałożono za czyn, który nie jest czynem zabronionym,
– ukarany popełnił czyn przed ukończeniem 17 lat,
– zachodzą okoliczności z art. 15–17 Kodeksu wykroczeń (np. obrona konieczna, stan wyższej konieczności, niepoczytalność),
– mandat nałożono w sytuacji, w której przepisy nie pozwalały na ukaranie mandatem (art. 96 k.p.w.).
Sam fakt, że uważasz karę za za wysoką albo żałujesz przyjęcia mandatu, nie jest podstawą do uchylenia.

4. Termin i gdzie złożyć wniosek
Wniosek składasz do sądu rejonowego, na którego obszarze nałożono grzywnę, najpóźniej w ciągu 7 dni od uprawomocnienia się mandatu. Termin jest krótki – licz go od dnia przyjęcia mandatu.

5. Co napisać we wniosku
– Twoje imię, nazwisko i adres,
– serię i numer mandatu, datę, miejsce i organ, który go nałożył,
– wskazanie podstawy z art. 101 k.p.w. i uzasadnienie (co się wydarzyło i dlaczego czyn nie był wykroczeniem),
– dowody (zdjęcia, nagrania, wydruki z tachografu, dane świadków),
– prośbę o zwrot zapłaconej grzywny, jeśli już ją zapłaciłeś.
Jeśli sąd uchyli mandat, zwraca zapłaconą kwotę.

6. Co z innymi sytuacjami?
Rzecznik Praw Obywatelskich zwracał uwagę, że brakuje możliwości uchylenia mandatu w części sytuacji. 23 czerwca 2026 r. Trybunał Konstytucyjny umorzył to postępowanie, uznając, że chodzi o zaniechanie ustawodawcy – przepisy się nie zmieniły.

7. Mandaty za granicą
Za granicą obowiązują przepisy danego kraju – terminy, kwoty i sposób odwołania są inne. Przed zapłatą lub odmową sprawdź pouczenie na dokumencie.

To jest poradnik, nie porada prawna. W trudnej sprawie skonsultuj się z prawnikiem.
TXT,
                'source_url' => 'https://arslege.pl/kodeks-postepowania-w-sprawach-o-wykroczenia/k16/a3788/',
                'published_at' => $now, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'user_id' => $admin, 'category' => 'itd', 'locale' => 'pl',
                'title' => 'Kontrola ITD: co sprawdzają „krokodyle” i co musisz mieć w kabinie',
                'excerpt' => 'Dokumenty, tachograf i czas pracy (56 dni wstecz), stan techniczny, masa i naciski, ładunek, ADR i pakiet mobilności – lista przed kontrolą.',
                'body' => <<<'TXT'
Inspektorzy Inspekcji Transportu Drogowego kontrolują pojazdy ciężarowe, autobusy i busy. Kontrola może dotyczyć kierowcy, pojazdu, ładunku i firmy. Oto, co sprawdzają najczęściej.

1. Dokumenty kierowcy i pojazdu
– prawo jazdy właściwej kategorii i wpis kwalifikacji zawodowej (kod 95),
– karta kierowcy,
– dowód rejestracyjny i potwierdzenie OC,
– wypis z licencji lub zezwolenia na wykonywanie transportu drogowego (albo zaświadczenie na potrzeby własne),
– dokumenty przewozowe, np. list przewozowy CMR,
– zezwolenia, jeśli są wymagane (np. na przejazd pojazdu nienormatywnego).

2. Tachograf i czas pracy
Musisz okazać dane z bieżącego dnia i 56 poprzednich dni (z karty kierowcy, wydruków lub wykresówek). Inspektor sprawdza m.in.:
– dzienny czas jazdy: do 9 h, dwa razy w tygodniu do 10 h,
– tygodniowy czas jazdy: do 56 h, a w dwóch kolejnych tygodniach łącznie do 90 h,
– przerwę 45 min po 4,5 h jazdy (można ją podzielić na 15 + 30 min),
– odpoczynek dobowy 11 h (skrócony 9 h),
– odpoczynek tygodniowy 45 h (skrócony 24 h, z rekompensatą),
– wpisy manualne za okresy, gdy nie prowadziłeś pojazdu, oraz symbole krajów.
Tachograf musi działać i mieć ważną legalizację. Manipulacja tachografem (np. magnes, nieuprawnione urządzenia) to jedno z najpoważniejszych naruszeń.

3. Stan techniczny
Oświetlenie, hamulce, opony, układ kierowniczy, wycieki, mocowanie naczepy oraz wyposażenie obowiązkowe (gaśnica, trójkąt ostrzegawczy). Przy poważnych usterkach inspektor może zatrzymać dowód rejestracyjny.

4. Masa, naciski i wymiary
Pojazd może zostać zważony. Inspektor sprawdza dopuszczalną masę całkowitą, naciski osi i wymiary. Przekroczenie bez zezwolenia oznacza karę dla przewoźnika, a czasem także dla załadowcy.

5. Zabezpieczenie ładunku
Pasy, belki, maty antypoślizgowe – ładunek nie może się przemieszczać. Źle zabezpieczony ładunek to częsty powód kary i zakazu dalszej jazdy do czasu poprawienia.

6. Towary niebezpieczne (ADR)
Zaświadczenie ADR kierowcy, oznakowanie pojazdu (tablice ostrzegawcze), instrukcje pisemne i wyposażenie awaryjne wymagane dla przewożonych towarów.

7. Przewozy międzynarodowe – pakiet mobilności
– kabotaż: do 3 przewozów w ciągu 7 dni od rozładunku przewozu międzynarodowego, potem 4 dni przerwy w kabotażu w tym kraju,
– pojazd powinien wracać do bazy przewoźnika co najmniej raz na 8 tygodni,
– przy delegowaniu kierowca powinien mieć deklarację delegowania.

Jak przygotować się do kontroli
– trzymaj dokumenty w jednym miejscu w kabinie,
– przed trasą sprawdź kartę kierowcy i wpisy manualne,
– zrób obchód pojazdu: światła, opony, mocowanie ładunku,
– podczas kontroli bądź spokojny i współpracuj; masz prawo do protokołu i pouczenia.

To jest poradnik, nie porada prawna. Szczegółowe zasady mogą się zmieniać – sprawdzaj komunikaty Głównego Inspektoratu Transportu Drogowego.
TXT,
                'source_url' => 'https://www.gov.pl/web/gitd',
                'published_at' => $now, 'created_at' => $now, 'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('posts')->where('title', 'like', 'Mandat drogowy:%')->orWhere('title', 'like', 'Kontrola ITD:%')->delete();
    }
};
