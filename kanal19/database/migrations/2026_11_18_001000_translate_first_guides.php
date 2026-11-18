<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Pierwsze poradniki (mandaty w Polsce, kontrola ITD) po angielsku, niemiecku i ukraińsku.
     * Dodawane raz na język, autor = pierwszy administrator.
     */
    public function up(): void
    {
        $admin = DB::table('users')->where('role', 'admin')->orderBy('id')->value('id');
        if (! $admin) {
            return;
        }
        $now = now();

        foreach ($this->guides() as $g) {
            if (DB::table('posts')->where('locale', $g['locale'])->where('title', $g['title'])->exists()) {
                continue;
            }
            DB::table('posts')->insert($g + [
                'user_id' => $admin, 'published_at' => $now, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        foreach ($this->guides() as $g) {
            DB::table('posts')->where('locale', $g['locale'])->where('title', $g['title'])->delete();
        }
    }

    private function guides(): array
    {
        $fineSrc = 'https://arslege.pl/kodeks-postepowania-w-sprawach-o-wykroczenia/k16/a3788/';
        $itdSrc = 'https://www.gov.pl/web/gitd';

        return [
            // ---------- EN ----------
            [
                'category' => 'mandaty', 'locale' => 'en', 'source_url' => $fineSrc,
                'title' => 'Traffic fine in Poland: you can refuse it, and once accepted it can be cancelled only in exceptional cases',
                'excerpt' => 'When to refuse a Polish on-the-spot fine (mandat), what happens next, and how to apply within 7 days to cancel a final fine.',
                'body' => <<<'TXT'
1. You can refuse the fine
A police officer or inspector offering you a fine (mandat karny) should tell you that you may refuse it. If you refuse, the matter goes to court: the authority files a request for punishment and the court decides on your guilt and the penalty. The court may impose a different penalty than the fine – including a higher one – and order you to pay the costs. Refusing makes sense when you have evidence that there was no offence (e.g. dashcam footage, tachograph records, witnesses).

2. An accepted fine is final
Once you accept the fine (sign it), it becomes final. There is no ordinary appeal against a final fine. It can only be cancelled in the situations listed in Article 101 of the Polish Code of Procedure for Petty Offences (k.p.w.).

3. When the court cancels a final fine (Art. 101 k.p.w.)
– the fine was imposed for an act that is not an offence,
– the person was under 17 when the act was committed,
– circumstances under Articles 15–17 of the Petty Offences Code apply (e.g. self-defence, necessity, insanity),
– the fine was imposed in a case where the law did not allow a fine to be issued (Art. 96 k.p.w.).
Thinking the fine is too high, or regretting that you accepted it, is not a reason to cancel it.

4. Deadline and where to apply
You file the application with the district court (sąd rejonowy) for the area where the fine was imposed, no later than 7 days after the fine became final. The deadline is short – count it from the day you accepted the fine.

5. What to include in the application
– your name, surname and address,
– the series and number of the fine, the date, place and the authority that issued it,
– the legal ground under Art. 101 k.p.w. and an explanation (what happened and why it was not an offence),
– evidence (photos, recordings, tachograph printouts, witness details),
– a request to refund the fine if you have already paid it.
If the court cancels the fine, the amount paid is refunded.

6. What about other situations?
The Polish Commissioner for Human Rights pointed out that in some situations there is no way to cancel a fine. On 23 June 2026 the Constitutional Tribunal discontinued these proceedings, finding that it was a matter of legislative omission – the law has not changed.

7. Fines in other countries
Abroad, the rules of that country apply – deadlines, amounts and the way to appeal are different. Before paying or refusing, check the instructions on the document.

This is a guide, not legal advice. In a difficult case, consult a lawyer.
TXT,
            ],
            [
                'category' => 'itd', 'locale' => 'en', 'source_url' => $itdSrc,
                'title' => 'ITD roadside check in Poland: what inspectors look at and what you must have in the cab',
                'excerpt' => 'Documents, tachograph and working time (56 days back), technical condition, weight and axle loads, cargo, ADR and the Mobility Package – a checklist before an inspection.',
                'body' => <<<'TXT'
Inspectors of the Polish Road Transport Inspection (ITD) check lorries, coaches and vans. A check may cover the driver, the vehicle, the cargo and the company. Here is what they check most often.

1. Driver and vehicle documents
– a driving licence of the right category and the professional qualification entry (code 95),
– driver card,
– registration certificate and proof of third-party insurance,
– a certified copy of the road transport licence or permit (or a certificate for own-account transport),
– transport documents, e.g. a CMR consignment note,
– permits where required (e.g. for an abnormal load).

2. Tachograph and working time
You must show records for the current day and the previous 56 days (from the driver card, printouts or chart sheets). Among other things, the inspector checks:
– daily driving time: up to 9 h, twice a week up to 10 h,
– weekly driving time: up to 56 h, and no more than 90 h in two consecutive weeks,
– a 45-minute break after 4.5 h of driving (can be split into 15 + 30 min),
– daily rest of 11 h (reduced: 9 h),
– weekly rest of 45 h (reduced: 24 h, with compensation),
– manual entries for periods when you were not driving, and country symbols.
The tachograph must work and have valid calibration. Tampering with the tachograph (e.g. a magnet or unauthorised devices) is one of the most serious infringements.

3. Technical condition
Lights, brakes, tyres, steering, leaks, trailer coupling and mandatory equipment (fire extinguisher, warning triangle). If there are serious defects, the inspector may retain the registration certificate.

4. Weight, axle loads and dimensions
The vehicle may be weighed. The inspector checks the maximum permissible weight, axle loads and dimensions. Exceeding them without a permit means a penalty for the carrier and sometimes also for the shipper.

5. Load securing
Straps, bars, anti-slip mats – the load must not move. Poorly secured cargo is a common reason for a penalty and a ban on continuing the journey until it is fixed.

6. Dangerous goods (ADR)
The driver's ADR certificate, vehicle marking (orange plates), instructions in writing and the emergency equipment required for the goods carried.

7. International transport – Mobility Package
– cabotage: up to 3 operations within 7 days of unloading an international delivery, followed by a 4-day cooling-off period in that country,
– the vehicle should return to the carrier's operating centre at least once every 8 weeks,
– for posted work the driver should have a posting declaration.

How to prepare for a check
– keep your documents in one place in the cab,
– before the trip, check your driver card and manual entries,
– walk around the vehicle: lights, tyres, load securing,
– during the check stay calm and cooperate; you have the right to a report and to be informed of your rights.

This is a guide, not legal advice. Detailed rules may change – follow the announcements of the Polish General Inspectorate of Road Transport (GITD).
TXT,
            ],

            // ---------- DE ----------
            [
                'category' => 'mandaty', 'locale' => 'de', 'source_url' => $fineSrc,
                'title' => 'Bußgeld in Polen: Sie können es ablehnen – angenommen, wird es nur in Ausnahmefällen aufgehoben',
                'excerpt' => 'Wann Sie ein polnisches Verwarnungsgeld (Mandat) ablehnen sollten, was danach passiert und wie Sie innerhalb von 7 Tagen die Aufhebung eines rechtskräftigen Mandats beantragen.',
                'body' => <<<'TXT'
1. Sie können das Mandat ablehnen
Ein Polizist oder Inspektor, der Ihnen ein Strafmandat (mandat karny) anbietet, muss Sie darauf hinweisen, dass Sie es ablehnen dürfen. Lehnen Sie ab, geht die Sache vor Gericht: Die Behörde stellt einen Antrag auf Bestrafung, und das Gericht entscheidet über Schuld und Strafe. Das Gericht kann eine andere Strafe als im Mandat verhängen – auch eine höhere – und Ihnen die Verfahrenskosten auferlegen. Eine Ablehnung ist sinnvoll, wenn Sie Beweise haben, dass keine Ordnungswidrigkeit vorlag (z. B. Dashcam-Aufnahmen, Tachographendaten, Zeugen).

2. Ein angenommenes Mandat ist rechtskräftig
Wenn Sie das Mandat annehmen (unterschreiben), wird es rechtskräftig. Gegen ein rechtskräftiges Mandat gibt es kein normales Rechtsmittel. Es kann nur in den Fällen aufgehoben werden, die in Art. 101 der polnischen Ordnungswidrigkeiten-Verfahrensordnung (k.p.w.) genannt sind.

3. Wann das Gericht ein rechtskräftiges Mandat aufhebt (Art. 101 k.p.w.)
– die Geldbuße wurde für eine Handlung verhängt, die nicht verboten ist,
– der Betroffene war bei der Tat jünger als 17 Jahre,
– es liegen Umstände nach Art. 15–17 des Ordnungswidrigkeitengesetzbuchs vor (z. B. Notwehr, Notstand, Schuldunfähigkeit),
– das Mandat wurde in einem Fall verhängt, in dem das Gesetz kein Mandat erlaubte (Art. 96 k.p.w.).
Dass Sie die Strafe für zu hoch halten oder die Annahme bereuen, ist kein Aufhebungsgrund.

4. Frist und zuständiges Gericht
Der Antrag wird beim Amtsgericht (sąd rejonowy) gestellt, in dessen Bezirk das Mandat verhängt wurde – spätestens 7 Tage nach Eintritt der Rechtskraft. Die Frist ist kurz: Zählen Sie ab dem Tag der Annahme.

5. Was in den Antrag gehört
– Vor- und Nachname sowie Anschrift,
– Serie und Nummer des Mandats, Datum, Ort und ausstellende Behörde,
– die Rechtsgrundlage aus Art. 101 k.p.w. und eine Begründung (was passiert ist und warum keine Ordnungswidrigkeit vorlag),
– Beweise (Fotos, Aufnahmen, Tachographenausdrucke, Zeugendaten),
– die Bitte um Erstattung, falls Sie schon bezahlt haben.
Hebt das Gericht das Mandat auf, wird der gezahlte Betrag erstattet.

6. Und andere Fälle?
Der polnische Bürgerrechtsbeauftragte wies darauf hin, dass es in manchen Fällen keine Möglichkeit zur Aufhebung gibt. Am 23. Juni 2026 stellte das Verfassungsgericht dieses Verfahren ein, da es sich um ein Versäumnis des Gesetzgebers handle – die Vorschriften haben sich nicht geändert.

7. Bußgelder im Ausland
Im Ausland gelten die Vorschriften des jeweiligen Landes – Fristen, Beträge und Rechtsmittel sind anders. Prüfen Sie vor Zahlung oder Ablehnung die Belehrung auf dem Dokument.

Dies ist ein Ratgeber, keine Rechtsberatung. In schwierigen Fällen wenden Sie sich an einen Anwalt.
TXT,
            ],
            [
                'category' => 'itd', 'locale' => 'de', 'source_url' => $itdSrc,
                'title' => 'ITD-Kontrolle in Polen: Was die Inspektoren prüfen und was Sie im Führerhaus haben müssen',
                'excerpt' => 'Dokumente, Tachograph und Lenkzeiten (56 Tage zurück), technischer Zustand, Gewicht und Achslasten, Ladung, ADR und Mobilitätspaket – Checkliste vor der Kontrolle.',
                'body' => <<<'TXT'
Inspektoren der polnischen Straßenverkehrsinspektion (ITD) kontrollieren Lkw, Busse und Transporter. Die Kontrolle kann Fahrer, Fahrzeug, Ladung und Unternehmen betreffen. Das wird am häufigsten geprüft.

1. Dokumente von Fahrer und Fahrzeug
– Führerschein der richtigen Klasse und Eintrag der Berufskraftfahrerqualifikation (Schlüsselzahl 95),
– Fahrerkarte,
– Zulassungsbescheinigung und Nachweis der Haftpflichtversicherung,
– beglaubigte Kopie der Gemeinschaftslizenz bzw. Genehmigung (oder Bescheinigung für den Werkverkehr),
– Beförderungspapiere, z. B. CMR-Frachtbrief,
– Genehmigungen, falls erforderlich (z. B. für Schwertransporte).

2. Tachograph und Arbeitszeit
Sie müssen die Daten des laufenden Tages und der vorangegangenen 56 Tage vorlegen (Fahrerkarte, Ausdrucke oder Schaublätter). Geprüft werden u. a.:
– tägliche Lenkzeit: bis 9 h, zweimal pro Woche bis 10 h,
– wöchentliche Lenkzeit: bis 56 h, in zwei aufeinanderfolgenden Wochen zusammen bis 90 h,
– 45 Minuten Pause nach 4,5 h Lenkzeit (teilbar in 15 + 30 min),
– tägliche Ruhezeit 11 h (verkürzt 9 h),
– wöchentliche Ruhezeit 45 h (verkürzt 24 h, mit Ausgleich),
– manuelle Einträge für Zeiten ohne Fahrzeugführung sowie Ländersymbole.
Der Tachograph muss funktionieren und gültig kalibriert sein. Manipulation (z. B. Magnet, unzulässige Geräte) gehört zu den schwersten Verstößen.

3. Technischer Zustand
Beleuchtung, Bremsen, Reifen, Lenkung, Undichtigkeiten, Kupplung des Aufliegers sowie Pflichtausrüstung (Feuerlöscher, Warndreieck). Bei schweren Mängeln kann der Inspektor die Zulassungsbescheinigung einbehalten.

4. Gewicht, Achslasten und Abmessungen
Das Fahrzeug kann gewogen werden. Geprüft werden zulässiges Gesamtgewicht, Achslasten und Abmessungen. Überschreitungen ohne Genehmigung bedeuten eine Strafe für den Unternehmer, manchmal auch für den Verlader.

5. Ladungssicherung
Gurte, Sperrbalken, Antirutschmatten – die Ladung darf sich nicht bewegen. Schlecht gesicherte Ladung ist ein häufiger Grund für Strafen und ein Weiterfahrverbot bis zur Korrektur.

6. Gefahrgut (ADR)
ADR-Schulungsbescheinigung des Fahrers, Kennzeichnung des Fahrzeugs (Warntafeln), schriftliche Weisungen und die für die Güter vorgeschriebene Notfallausrüstung.

7. Internationaler Verkehr – Mobilitätspaket
– Kabotage: bis zu 3 Beförderungen innerhalb von 7 Tagen nach der Entladung einer internationalen Beförderung, danach 4 Tage Sperrfrist in diesem Land,
– das Fahrzeug soll mindestens alle 8 Wochen zur Betriebsstätte des Unternehmers zurückkehren,
– bei Entsendung muss der Fahrer eine Entsendeerklärung mitführen.

So bereiten Sie sich vor
– Dokumente an einem Ort im Führerhaus aufbewahren,
– vor der Fahrt Fahrerkarte und manuelle Einträge prüfen,
– Rundgang ums Fahrzeug: Licht, Reifen, Ladungssicherung,
– bei der Kontrolle ruhig bleiben und kooperieren; Sie haben Anspruch auf ein Protokoll und eine Belehrung.

Dies ist ein Ratgeber, keine Rechtsberatung. Die Regeln können sich ändern – verfolgen Sie die Mitteilungen der polnischen Hauptinspektion des Straßenverkehrs (GITD).
TXT,
            ],

            // ---------- UK ----------
            [
                'category' => 'mandaty', 'locale' => 'uk', 'source_url' => $fineSrc,
                'title' => 'Штраф у Польщі: від нього можна відмовитися, а прийнятий скасують лише у виняткових випадках',
                'excerpt' => 'Коли відмовитися від польського штрафу (mandat), що буде далі і як протягом 7 днів подати заяву про скасування остаточного штрафу.',
                'body' => <<<'TXT'
1. Ви можете відмовитися від штрафу
Поліцейський або інспектор, який пропонує штраф (mandat karny), має повідомити, що ви можете його не прийняти. Якщо ви відмовитеся, справа піде до суду: орган подає клопотання про покарання, і суд вирішує щодо вашої вини та покарання. Суд може призначити інше покарання, ніж у штрафі, – зокрема вище – і покласти на вас судові витрати. Відмовлятися варто, коли маєте докази, що порушення не було (напр. запис відеореєстратора, дані тахографа, свідки).

2. Прийнятий штраф є остаточним
Коли ви приймаєте штраф (підписуєте його), він набирає законної сили. Звичайного оскарження остаточного штрафу немає. Його можна лише скасувати у випадках, перелічених у ст. 101 Кодексу процедури у справах про правопорушення Польщі (k.p.w.).

3. Коли суд скасовує остаточний штраф (ст. 101 k.p.w.)
– штраф накладено за дію, яка не є забороненою,
– особа вчинила дію до 17 років,
– є обставини зі ст. 15–17 Кодексу про правопорушення (напр. необхідна оборона, крайня необхідність, неосудність),
– штраф накладено у випадку, коли закон не дозволяв карати штрафом на місці (ст. 96 k.p.w.).
Те, що ви вважаєте штраф завеликим або шкодуєте, що прийняли його, не є підставою для скасування.

4. Строк і куди подавати
Заяву подають до районного суду (sąd rejonowy), на території якого накладено штраф, не пізніше ніж через 7 днів після набрання ним законної сили. Строк короткий – рахуйте його від дня прийняття штрафу.

5. Що написати в заяві
– ім'я, прізвище та адресу,
– серію і номер штрафу, дату, місце та орган, який його наклав,
– підставу зі ст. 101 k.p.w. та обґрунтування (що сталося і чому дія не була правопорушенням),
– докази (фото, записи, роздруківки з тахографа, дані свідків),
– прохання повернути сплачену суму, якщо ви вже заплатили.
Якщо суд скасує штраф, сплачені гроші повертаються.

6. А інші ситуації?
Уповноважений з прав людини Польщі звертав увагу, що в частині ситуацій немає можливості скасувати штраф. 23 червня 2026 р. Конституційний трибунал закрив це провадження, визнавши, що йдеться про бездіяльність законодавця, – норми не змінилися.

7. Штрафи за кордоном
За кордоном діють правила відповідної країни – строки, суми та спосіб оскарження інші. Перед оплатою чи відмовою перевірте роз'яснення на документі.

Це довідник, а не юридична консультація. У складній справі зверніться до юриста.
TXT,
            ],
            [
                'category' => 'itd', 'locale' => 'uk', 'source_url' => $itdSrc,
                'title' => 'Перевірка ITD у Польщі: що перевіряють інспектори і що мусить бути в кабіні',
                'excerpt' => 'Документи, тахограф і робочий час (56 днів назад), технічний стан, маса й навантаження на осі, вантаж, ADR і пакет мобільності – список перед перевіркою.',
                'body' => <<<'TXT'
Інспектори польської Інспекції автомобільного транспорту (ITD) перевіряють вантажівки, автобуси та бусики. Перевірка може стосуватися водія, транспортного засобу, вантажу та фірми. Ось що перевіряють найчастіше.

1. Документи водія і транспортного засобу
– посвідчення водія відповідної категорії та запис про професійну кваліфікацію (код 95),
– картка водія,
– свідоцтво про реєстрацію та підтвердження страховки OC,
– засвідчена копія ліцензії або дозволу на автомобільні перевезення (або посвідчення на перевезення для власних потреб),
– перевізні документи, напр. накладна CMR,
– дозволи, якщо потрібні (напр. на проїзд негабаритного транспорту).

2. Тахограф і робочий час
Ви маєте показати дані за поточний день і 56 попередніх днів (з картки водія, роздруківок або тахограм). Інспектор перевіряє, зокрема:
– щоденний час керування: до 9 год, двічі на тиждень до 10 год,
– тижневий час керування: до 56 год, а за два тижні поспіль разом до 90 год,
– перерву 45 хв після 4,5 год керування (можна розділити на 15 + 30 хв),
– щоденний відпочинок 11 год (скорочений 9 год),
– щотижневий відпочинок 45 год (скорочений 24 год, з компенсацією),
– ручні записи за періоди, коли ви не керували, та символи країн.
Тахограф має працювати й мати чинну калібровку. Маніпуляції з тахографом (напр. магніт, недозволені пристрої) – одне з найсерйозніших порушень.

3. Технічний стан
Освітлення, гальма, шини, рульове керування, протікання, зчеплення з напівпричепом та обов'язкове обладнання (вогнегасник, знак аварійної зупинки). При серйозних несправностях інспектор може затримати свідоцтво про реєстрацію.

4. Маса, навантаження на осі та габарити
Транспорт можуть зважити. Інспектор перевіряє допустиму повну масу, навантаження на осі та габарити. Перевищення без дозволу означає штраф для перевізника, а іноді й для вантажовідправника.

5. Кріплення вантажу
Паси, розпірки, протиковзкі килимки – вантаж не може переміщатися. Погано закріплений вантаж – часта причина штрафу та заборони їхати далі до виправлення.

6. Небезпечні вантажі (ADR)
Свідоцтво ADR водія, маркування транспорту (таблички), письмові інструкції та аварійне обладнання, потрібне для вантажу, що перевозиться.

7. Міжнародні перевезення – пакет мобільності
– каботаж: до 3 перевезень протягом 7 днів після розвантаження міжнародного перевезення, потім 4 дні перерви в каботажі в цій країні,
– транспорт має повертатися на базу перевізника щонайменше раз на 8 тижнів,
– при відрядженні (delegowanie) водій повинен мати декларацію про відрядження.

Як підготуватися до перевірки
– тримайте документи в одному місці в кабіні,
– перед рейсом перевірте картку водія та ручні записи,
– обійдіть транспорт: світло, шини, кріплення вантажу,
– під час перевірки будьте спокійні та співпрацюйте; ви маєте право на протокол і роз'яснення.

Це довідник, а не юридична консультація. Детальні правила можуть змінюватися – стежте за повідомленнями Головного інспекторату автомобільного транспорту Польщі (GITD).
TXT,
            ],
        ];
    }
};
