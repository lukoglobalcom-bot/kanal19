<x-legal-page title="Polityka prywatności" :updated="$updated">
    <h2>1. Administrator danych</h2>
    <p>Administratorem Twoich danych osobowych jest <x-operator :op="$op" />. We wszystkich sprawach dotyczących danych osobowych możesz pisać na ten adres e-mail.</p>

    <h2>2. Jakie dane przetwarzamy i po co</h2>
    <ul>
        <li><strong>Konto</strong> – e-mail, hasło (zaszyfrowane), numer telefonu, język, rola (kierowca, firma, reklamodawca). Cel: założenie i prowadzenie konta, kontakt, bezpieczeństwo. Podstawa: umowa (art. 6 ust. 1 lit. b RODO).</li>
        <li><strong>Profil kierowcy</strong> – imię, nazwisko, doświadczenie, uprawnienia, rodzaje pojazdów, informacja, czy chcesz być w bazie kierowców. Firmy widzą tylko imię, pierwszą literę nazwiska i kwalifikacje; telefon – dopiero gdy przyjmiesz zaproszenie albo sam zaaplikujesz. Podstawa: umowa.</li>
        <li><strong>Dane firmy</strong> – nazwa, NIP, rodzaj działalności, treść ogłoszeń. Podstawa: umowa.</li>
        <li><strong>Ogłoszenia, zgłoszenia na mapie, wiadomości CB</strong> – treść, miejsce zgłoszenia, czas. Są widoczne dla innych użytkowników przez ograniczony czas (np. wiadomości CB 3 godziny, korek 2 godziny). Podstawa: umowa.</li>
        <li><strong>Lokalizacja</strong> – pobieramy ją tylko wtedy, gdy na to pozwolisz w przeglądarce, i tylko do konkretnej funkcji:
            <ul>
                <li>zgłoszenie na mapie i wezwanie SOS – zapisujemy miejsce zgłoszenia; przy SOS inni kierowcy widzą miejsce i mogą zobaczyć Twój numer telefonu, aby się skontaktować,</li>
                <li>tryb jazdy – pozycja jest używana w Twoim telefonie do ostrzeżeń; na serwer trafia jedynie przybliżona informacja potrzebna do ustalenia kraju (do zgłoszeń i kanału CB),</li>
                <li>powiadomienia SOS w pobliżu – zapisujemy ostatnią znaną pozycję (aktualizowaną najczęściej co 10 minut w trybie jazdy), aby wysłać Ci wezwanie od kierowcy w wybranym promieniu; pozycji starszej niż 24 godziny nie używamy.</li>
            </ul>
            Podstawa: umowa, a w zakresie powiadomień – Twoja zgoda (art. 6 ust. 1 lit. a RODO), którą możesz wycofać, wyłączając powiadomienia.</li>
        <li><strong>CB głosowe</strong> – głos jest przesyłany na żywo do osób na kanale i <strong>nie jest nagrywany</strong>. Pozostałe osoby widzą Twój podpis (imię i pierwsza litera nazwiska).</li>
        <li><strong>Planer trasy i wyszukiwarka adresów</strong> – wpisane adresy i punkty trasy są przekazywane do serwera tras i wyszukiwarki adresów wyłącznie w celu wyznaczenia trasy; nie są przypisywane do Twojego konta.</li>
        <li><strong>Dane techniczne</strong> – adres IP, typ przeglądarki, dzienniki serwera. Cel: bezpieczeństwo i ochrona przed nadużyciami. Podstawa: prawnie uzasadniony interes (art. 6 ust. 1 lit. f RODO).</li>
    </ul>

    <h2>3. Komu przekazujemy dane</h2>
    <ul>
        <li>dostawcy hostingu, na którego serwerach działa Portal,</li>
        <li>dostawcy serwera tras i CB głosowego (gdy funkcje są uruchomione),</li>
        <li>operatorowi bramki SMS – w zakresie numeru telefonu, gdy wysyłamy kod weryfikacyjny,</li>
        <li>dostawcom usług powiadomień push wskazanym przez Twoją przeglądarkę lub telefon (np. Google, Apple, Mozilla) – w zakresie zaszyfrowanej treści powiadomienia,</li>
        <li>OpenStreetMap Foundation (kafelki mapy) i usłudze Nominatim (wyszukiwarka adresów) – Twoja przeglądarka pobiera mapę bezpośrednio z ich serwerów, więc poznają one Twój adres IP,</li>
        <li>innym użytkownikom – w zakresie treści, które sam publikujesz,</li>
        <li>organom publicznym – tylko gdy wymaga tego prawo.</li>
    </ul>
    <p>Niektórzy z tych dostawców (np. usługi powiadomień) mogą przetwarzać dane poza Europejskim Obszarem Gospodarczym. Odbywa się to na podstawie decyzji Komisji Europejskiej stwierdzającej odpowiedni poziom ochrony lub standardowych klauzul umownych.</p>

    <h2>4. Jak długo przechowujemy dane</h2>
    <ul>
        <li>dane konta – do czasu usunięcia konta,</li>
        <li>ogłoszenia – 30 dni widoczności, następnie do usunięcia konta (archiwum dla Ciebie),</li>
        <li>zgłoszenia na mapie i wiadomości CB – są ukrywane automatycznie po krótkim czasie i usuwane razem z kontem,</li>
        <li>pozycja do powiadomień – do wyłączenia powiadomień lub usunięcia konta,</li>
        <li>dzienniki techniczne – do 12 miesięcy.</li>
    </ul>

    <h2>5. Twoje prawa</h2>
    <p>Masz prawo do dostępu do danych, ich sprostowania, usunięcia, ograniczenia przetwarzania, przenoszenia, sprzeciwu wobec przetwarzania opartego na uzasadnionym interesie oraz do wycofania zgody w dowolnym momencie. Konto i wszystkie dane możesz usunąć samodzielnie w zakładce <a href="{{ route('account') }}" class="text-k19-red">Moje konto</a>. Masz też prawo wnieść skargę do Prezesa Urzędu Ochrony Danych Osobowych (ul. Stawki 2, 00-193 Warszawa).</p>

    <h2>6. Pliki cookies i pamięć przeglądarki</h2>
    <p>Używamy wyłącznie niezbędnych plików cookies: sesji (logowanie), ochrony formularzy (CSRF) i wybranego języka. W pamięci przeglądarki zapisujemy ustawienia wygodne dla Ciebie (np. ostatni pojazd w planerze, kanał CB, dźwięk ostrzeżeń). Nie używamy cookies reklamowych ani śledzących i nie profilujemy użytkowników.</p>

    <h2>7. Zmiany</h2>
    <p>O istotnych zmianach Polityki prywatności poinformujemy w Portalu. Aktualna wersja jest zawsze dostępna pod adresem kanal19.pl/polityka-prywatnosci.</p>
</x-legal-page>
