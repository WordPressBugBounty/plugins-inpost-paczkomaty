=== Inpost Paczkomaty ===
Contributors: rimosfafora
Donate link: https://suppi.pl/damian-ziarnik
Tags: inpost, paczkomaty, woocommerce, wysyłka, dostawa
Requires at least: 5.3
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.40
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Dodaj Paczkomaty InPost jako formę dostawy w WooCommerce – wygodna mapka wyboru w koszyku i kasie, limity wagi/wymiarów.

== Description ==

**Inpost Paczkomaty** to lekka, w pełni spolszczona wtyczka, która dodaje Paczkomaty InPost jako formę dostawy w sklepie WooCommerce. Klient wybiera paczkomat na wygodnej mapce (oficjalny GeoWidget InPost) bezpośrednio w koszyku lub w kasie, a Ty od razu widzisz wybrany punkt odbioru w panelu zamówienia, w mailu z potwierdzeniem i na stronie z podziękowaniem za zakup.

Wtyczka działa od razu po aktywacji – wystarczy dodać nową metodę wysyłki w ustawieniach WooCommerce, bez konfigurowania kluczy API czy dodatkowych usług zewnętrznych.

= Najważniejsze funkcje =

* Mapka wyboru Paczkomatu (oficjalny GeoWidget InPost) w koszyku i podczas składania zamówienia.
* Automatyczna obsługa zarówno **klasycznego koszyka/checkoutu** (shortcode `[woocommerce_cart]` / `[woocommerce_checkout]`), jak i **nowego blokowego checkoutu WooCommerce Blocks** – wtyczka sama wykrywa, którego trybu używa Twój sklep.
* Zapisywanie wybranego paczkomatu w zamówieniu, w mailu z potwierdzeniem oraz w panelu administratora przy zamówieniu.
* Opcjonalne ustawienie wybranego paczkomatu jako adresu wysyłki zamówienia.
* Limity wagi i wymiarów paczki – możliwość ukrycia metody wysyłki lub automatycznego podziału zamówienia na kilka paczek po przekroczeniu limitu.
* Konfigurowalne progi cenowe (minimalna/maksymalna wartość zamówienia), np. darmowa dostawa od określonej kwoty koszyka.
* Możliwość dodania własnego logo InPost w koszyku i kasie.
* Gotowe pola meta zamówienia (m.in. `_paczkomat_id`, `delivery_point_name`, `delivery_point_city`) ułatwiające integrację z systemami zewnętrznymi.
* Pełna obsługa HPOS (High-Performance Order Storage) w nowszych wersjach WooCommerce.
* Wtyczka jest w pełni przetłumaczona na język polski i gotowa do tłumaczenia na inne języki (Text Domain: inpost-paczkomaty).

= Dla kogo jest ta wtyczka =

Dla każdego sklepu WooCommerce, który chce zaoferować klientom wygodny odbiór przesyłek w Paczkomatach InPost, bez konieczności wdrażania rozbudowanych integracji kurierskich.

= Bezpieczeństwo i jakość kodu =

Kod wtyczki jest regularnie przeglądany pod kątem bezpieczeństwa – weryfikacja nonce na endpointach AJAX, kontrola uprawnień, escapowanie danych wyjściowych oraz sanitizacja zapisywanych ustawień. Szczegóły poszczególnych poprawek znajdziesz w sekcji Changelog.

= Podoba Ci się wtyczka? =

Jeśli Inpost Paczkomaty pomogła Ci zaoszczędzić czas w Twoim sklepie, będę ogromnie wdzięczny za zostawienie oceny ⭐⭐⭐⭐⭐ w zakładce [Reviews](https://wordpress.org/support/plugin/inpost-paczkomaty/reviews/) – to najprostszy sposób, żeby wtyczka trafiała do kolejnych właścicieli sklepów WooCommerce. Potrzebujesz dodatkowej funkcji, integracji z innym systemem albo pomocy z wdrożeniem u siebie? Zajrzyj do sekcji **Wsparcie** poniżej – realizuję również płatne zlecenia indywidualne.

== Screenshots ==

1. screenshot-1.png
2. screenshot-2.png
3. screenshot-3.png
4. screenshot-4.png

== Frequently Asked Questions ==

= Jak dodać Paczkomaty jako formę dostawy? =

Po aktywowaniu wtyczki przejdź do WooCommerce -> Ustawienia -> Wysyłka -> wybierz odpowiednią strefę wysyłki i dodaj nową metodę dostawy typu "Inpost Paczkomaty".

= Nie widzę przycisku "Wybierz paczkomat" w koszyku/kasie – co mogę zrobić? =

Wtyczka automatycznie wykrywa, czy Twój sklep korzysta z klasycznego koszyka/checkoutu (shortcode) czy z nowego, blokowego WooCommerce Blocks – nie trzeba nic przełączać ręcznie. Sprawdź w panelu WooCommerce -> Inpost Paczkomaty pole "Tryb checkout (wykryty automatycznie)":

* Jeśli widnieje status "⚠️ Wykryto konflikt" (koszyk i checkout korzystają z różnych trybów), wybierz ręcznie odpowiedni tryb w tym samym panelu lub ujednolić obie strony (najlepiej obie na blokach albo obie na shortcode).
* Jeśli mimo poprawnie wykrytego trybu przycisk nadal się nie pojawia, upewnij się, że wybrana jest metoda dostawy "Inpost Paczkomaty", a następnie wyłącz chwilowo inne wtyczki (patrz pytanie poniżej).

= Używam klasycznego koszyka/checkoutu opartego na shortcode – czy muszę coś ustawiać? =

Nie. Wtyczka wykrywa to automatycznie na podstawie zawartości stron koszyka i checkoutu. Jeśli wcześniej zamieniłeś/aś te strony na bloki i chcesz wrócić do wersji shortcode, w panelu WooCommerce -> Inpost Paczkomaty znajdziesz przycisk "Przywróć" przy opcji "Przywróć klasyczny widok koszyka i kasy" (zalecana wcześniejsza kopia zapasowa).

= Używam nowego, blokowego koszyka/checkoutu WooCommerce – czy muszę coś ustawiać? =

Nie, selektor paczkomatu pojawi się automatycznie w sekcji wysyłki bloku koszyka/checkoutu po wybraniu formy dostawy Inpost Paczkomaty – nie trzeba nic dodawać ręcznie w edytorze strony.

= Jak ustawić wybrany paczkomat jako adres wysyłki zamówienia? =

W panelu administracyjnym przejdź do WooCommerce -> Inpost Paczkomaty i ustaw na "Tak" pole "Zapisz wybrany paczkomat jako adres wysyłki".

= Jak ustawić darmową dostawę od określonej kwoty zamówienia? =

Dodaj dwie osobne metody wysyłki "Inpost Paczkomaty" w tej samej strefie. Pierwszej ustaw np. koszt 20 zł i regułę "Pokaż tę metodę wysyłki, gdy... maksymalna wartość zamówienia 60 zł", a drugiej koszt 0 zł i regułę "...minimalna wartość zamówienia 60 zł". Kwoty 20 zł i 60 zł są tylko przykładem – dobierz je do swojego sklepu.

= Jak ustawić maksymalną wagę produktów dla wysyłki Paczkomatem? =

W panelu WooCommerce -> Inpost Paczkomaty ustaw pole "Limit wagowy" na "Tak" oraz podaj wartość w polu "Maksymalna waga (kg)". Następnie wybierz, co ma się dziać po przekroczeniu limitu: "Ukryj metodę wysyłki" po prostu ukryje wysyłkę Paczkomatem, a "Podziel na kilka paczek" automatycznie przeliczy i pomnoży koszt wysyłki proporcjonalnie do liczby paczek.

= Jak ustawić maksymalne wymiary paczki dla wysyłki Paczkomatem? =

W panelu WooCommerce -> Inpost Paczkomaty ustaw pole "Ograniczenie wymiarów" na "Tak" oraz uzupełnij pola "Maksymalna szerokość (cm)", "Maksymalna wysokość (cm)" i "Maksymalna długość (cm)". Gdy którykolwiek produkt w koszyku przekroczy podane wymiary, metoda wysyłki Inpost Paczkomaty zostanie automatycznie ukryta.

= Jak dodać logo InPost w koszyku i kasie? =

W panelu WooCommerce -> Inpost Paczkomaty ustaw pole "Pokaż logo w koszyku i kasie" na "Tak", a następnie wybierz logo z biblioteki mediów. Maksymalna szerokość wyświetlanego logo to 100 px.

= Czy wtyczka jest zgodna z najnowszym WordPressem i WooCommerce? =

Tak – wtyczka jest na bieżąco testowana z aktualnymi wersjami WordPressa i WooCommerce (patrz pole "Tested up to" na górze tej strony) oraz regularnie aktualizowana pod kątem bezpieczeństwa i zgodności (m.in. pełna obsługa HPOS).

= U mnie nie działa / znalazłem błąd – co zrobić? =

Wyłącz chwilowo wszystkie wtyczki poza WooCommerce i Inpost Paczkomaty, a następnie sprawdź, czy problem nadal występuje. Jeśli tak, spróbuj przełączyć się na domyślny motyw WordPress (np. Twenty Twenty-Four), aby wykluczyć konflikt z szablonem. Błąd nadal występuje? Napisz w zakładce [Support](https://wordpress.org/support/plugin/inpost-paczkomaty/) – chętnie pomogę.

= Czy planowane jest wsparcie dla kurierów InPost (nie tylko Paczkomatów)? =

Tak, rozwój wtyczki jest kontynuowany, a rozszerzenie o kolejne formy dostawy InPost jest na liście planów. Jeśli zależy Ci na konkretnej integracji szybciej, zobacz pytanie poniżej o zlecenia indywidualne.

= Potrzebuję dodatkowej funkcji, customizacji albo integracji z innym systemem – co mogę zrobić? =

Realizuję płatne wdrożenia i modyfikacje szyte na miarę – dodatkowe pola, integracje z zewnętrznymi systemami (np. kurierskimi, ERP, hurtowniami) czy zmiany wyglądu selektora paczkomatu. Opisz swoją potrzebę przez formularz kontaktowy na [grainsoft.pl](https://grainsoft.pl/#kontakt).

= Jak mogę wesprzeć rozwój wtyczki? =

Najprościej zostawiając ocenę ⭐⭐⭐⭐⭐ w zakładce [Reviews](https://wordpress.org/support/plugin/inpost-paczkomaty/reviews/) – to bezpośrednio pomaga innym właścicielom sklepów trafić na tę wtyczkę. Możesz też postawić mi [kawę ☕](https://suppi.pl/damian-ziarnik) albo zlecić płatne wsparcie/rozwój przez [grainsoft.pl](https://grainsoft.pl/#kontakt).

== Changelog ==

= 1.0.40 =
* Zgodność z WordPress 7.1 – przegląd wtyczki pod kątem zmian z tej wersji (iframowany edytor, client-side media processing, zmiany w @wordpress/components, trwały pasek narzędzi, aktualizacja jQuery UI do 1.14.2).
* Nowe: deklaracja kompatybilności z HPOS (custom_order_tables) oraz blokowym koszykiem/checkoutem (cart_checkout_blocks) – wcześniej WooCommerce oznaczał wtyczkę jako niekompatybilną i mógł blokować włączenie HPOS, mimo że wtyczka w pełni je obsługuje.
* Poprawka: wtyczka poprawnie wykrywa WooCommerce aktywowane sieciowo (network activated) na instalacjach multisite – wcześniej w takiej konfiguracji nie uruchamiała się w ogóle.
* Uzupełniono nagłówki wtyczki (Requires at least, Requires PHP, Requires Plugins, License) zgodnie z wymaganiami Plugin Check i mechanizmu zależności wtyczek z WordPress 6.5+.
* Poprawka: dodano brakującą domenę tłumaczenia (text domain) dla dwóch ciągów, które nigdy nie były tłumaczalne ("Paczkomat" w tabeli zamówienia oraz komunikat walidacji w checkoucie klasycznym).
* Poprawka: uzupełniono plik tłumaczenia pl_PL o 4 brakujące, wyświetlane dotąd po angielsku komunikaty (m.in. opisy pól "Ograniczenie wymiarów" i "Zapisz wybrany paczkomat jako adres wysyłki") oraz usunięto 8 nieużywanych wpisów po dawnej funkcji "legacy checkbox".
* Poprawka: naprawiono literówkę "Sukcess" -> "Sukces" oraz nieprzetłumaczone/zniekształcone frazy (brak polskich znaków) w pliku tłumaczenia.
* Zaktualizowano szablon tłumaczeń (.pot), który był nieaktualny od 2021 r. i nie zawierał ok. 20 nowszych ciągów – ułatwi to tworzenie tłumaczeń na inne języki.

= 1.0.39 =
* Bezpieczeństwo: dodano weryfikację nonce (check_ajax_referer) dla endpointów AJAX zapisujących i odczytujących wybrany paczkomat (set_paczkomat, get_paczkomat_session) w obu trybach koszyka – blokowym i klasycznym.
* Bezpieczeństwo: endpoint przywracający koszyk/checkout do wersji klasycznej (przycisk "Przywróć") wymaga teraz uprawnienia manage_options oraz prawidłowego nonce – wcześniej mógł zostać wywołany przez dowolnego zalogowanego użytkownika, również przez CSRF.
* Bezpieczeństwo: dane wybranego paczkomatu (nazwa, adres) są teraz każdorazowo escapowane (esc_html/esc_url) przy wyświetlaniu w koszyku/checkoucie klasycznym oraz w panelu zamówienia, co eliminuje potencjalny XSS.
* Bezpieczeństwo: usunięto budowanie DOM przez innerHTML z danymi widgetu InPost na rzecz bezpiecznego textContent w skrypcie modala paczkomatu.
* Bezpieczeństwo: dodano sanitize_callback dla opcji wtyczki (register_setting) – zapisywane są tylko rozpoznane pola, każde rzutowane na oczekiwany typ (yes/no, liczby, URL, enum).
* Poprawka: zabezpieczono przed błędem krytycznym w powiadomieniu admina, gdy get_current_screen() zwraca null.
* Poprawka: usunięto niespójny text domain (inpost_paczkomaty -> inpost-paczkomaty) w opisie ustawienia kosztu wysyłki.
* Potwierdzono zgodność z WordPress 7.0.2.
* Poprawka: naprawiono błędne wywołanie _e() zamiast __() w odpowiedzi JSON endpointu przywracania koszyka/checkoutu.
* Odświeżono opis wtyczki oraz sekcję FAQ w readme – usunięto nieaktualne informacje o dawnym checkboksie trybu koszyka, dodano opis wszystkich aktualnych funkcji.

= 1.0.38 =
* Poprawka detekcji konfliktu trybów – przypadek gdy koszyk jest blokowy a checkout klasyczny (lub odwrotnie) jest teraz prawidłowo wykrywany.
* W panelu admina przy wykrytym konflikcie pojawia się czerwony badge "⚠️ Conflict detected" z informacją które strony używają jakiego trybu.
* Dodano możliwość ręcznego wyboru trybu (block/classic) gdy wykryty jest konflikt – opcja zapisywana w ustawieniach i aktywna tylko przy konflikcie.

= 1.0.37 =
* Automatyczne wykrywanie trybu checkout (classic checkout / block checkout) na podstawie zawartości strony koszyka i checkoutu – bez potrzeby ręcznego ustawiania checkboxa.
* Usunięcie checkboxa "I want to use legacy (PHP) cart/checkout" – tryb jest teraz wybierany automatycznie.
* Nowy badge w panelu ustawień informujący o aktualnie wykrytym trybie: "Classic checkout (shortcode)" lub "Block checkout (WooCommerce Blocks)".
* Zmiana terminologii w kodzie: "legacy" → "classic checkout", "block mode" → "block checkout".
* Zmiana nazwy pliku includes/checkout-legacy.php na includes/checkout-classic.php.
* Uzupełnienie polskich tłumaczeń dla nowych ciągów (auto-detect, nazwy trybów).

= 1.0.36 =
* Poprawa powiadomienia w panelu administracyjnym – nowy układ z linkami do oceny, wsparcia projektu i płatnego wsparcia technicznego.
* Uzupełnienie i poprawienie polskich tłumaczeń dla wszystkich nowych ciągów dodanych w wersji 1.0.35 (tryb blokowy, ustawienia checkboxu, limit wagi).
* Dodanie sekcji Wsparcie w pliku readme.

= 1.0.35 =
* Dodanie obsługi nowego blokowego koszyka i checkoutu WooCommerce (WooCommerce Blocks).
* Nowy checkbox w ustawieniach: "I want to use legacy (PHP) cart/checkout" – pozwala wybrać tryb klasyczny (shortcode) lub blokowy.
* W trybie blokowym: selektor paczkomatu pojawia się automatycznie w sekcji wysyłki bloku checkout/cart po wybraniu formy dostawy InPost Paczkomaty.
* Integracja z WooCommerce Blocks API (IntegrationInterface, ExperimentalOrderShippingPackages) – skrypt ładowany przez wp_enqueue_scripts jako fallback dla starszych wersji WC Blocks.
* Zapis danych paczkomatu do zamówienia przez hook Store API (woocommerce_store_api_checkout_order_processed) z fallbackiem na woocommerce_checkout_order_created.
* Przełączenie z add_meta_data na update_meta_data – poprawna obsługa HPOS (High-Performance Order Storage).
* Przywrócenie wybranego paczkomatu po odświeżeniu strony (AJAX get_paczkomat_session odczytuje dane z sesji PHP).
* Podział kodu na osobne pliki: includes/checkout-legacy.php (tryb klasyczny) i includes/checkout-blocks.php (tryb blokowy).
* Walidacja brakującego paczkomatu w trybie blokowym przez RouteException (Store API).

= 1.0.34 =
* Poprawa ustawień dotyczących limitów wymiarów oraz wagi.

= 1.0.33 =
* Dodanie przycisku w ustawieniach który zamienia koszyk i checkout na shortcode'y "[wooommerce_checkout]" oraz "[woocommerce_cart]".

= 1.0.32 =
* Naprawa błędu z duplikującymi się meta danymi przez co integracje otrzymywały podwójne dane

= 1.0.31 =
* Dodanie komunikatu w panelu w ustawieniach

= 1.0.30 =
* Naprawa błędu z pojawiającymi się warningami o braku elementu w tablicy

= 1.0.29 =
* Naprawa błędu przez który nie dało się dodać zdjęcia do produktu
* Naprawa tłumaczeń

= 1.0.28 =
* Dodanie możliwości ustawienia loga w koszyku i checkoucie
* Dodanie możliwości ustawienia limitu dla wagi produktów w koszyku
* dodanie możliwości ustawiania limitu dla wymiarów produktu

= 1.0.27 =
* Poprawka przekazywania danych o paczkomacie do panelu
* Naprawa błędu z przekazywaniem danych do baselinka w niektórych przypadkach


= 1.0.26 =
* Wsparcie dla php 8.2
* Wsparcie dla wordpress 6.4.1


= 1.0.25 =
* Poprawa integracji z baselinkerem

= 1.0.24 =
* Drobne poprawki

= 1.0.23 =
* Przeniesienie zakładki z ustawieniami do podmenu woocommerce
* Poprawa tłumaczeń

= 1.0.23 =
* Dodanie meta_data aby zamówienia integrowały się z baselinkerem

= 1.0.22 =
* Dodanie zakładki z konfiguracją
* Dodanie możliwości ustawienia aby dane wybranego paczkomaty były zaczytywane jako "adres wysyłki".

= 1.0.21 =
* Poprawa walidacji w przypadku gdy została wybrana wysyłka paczkomatem ale nie został wybrany paczkomat.
* Poprawa czytelności kodu

= 1.0.20 =
* Poprawienie błędu w przypadku produktów wirutalnych


= 1.0.19 =
* Zabezpieczenie przed pojawianiem się błędów w przypadku produktów wirtualnych 


= 1.0.18 =
* Poprawa "notice" o przekazywaniu zmiennej przez referencje 
* Nie wyświetlanie informacji o paczkomacie gdy w ostatnim kroku koszyka została zmieniona forma dostawy
* Przetestowanie wtyczki na wordpressie 6.0.1 oraz PHP 7.4

= 1.0.17 =
* Dodanie checkboxa z "Zastosuj regułę minimalnego zamówienia sprzed użycia kuponu zniżkowego"

= 1.0.16 =
* Rozwiązanie błędu który był generowany w podglądzie customowej wiadomości

= 1.0.15 =
* Dodanie możliwości pokazywanie przesyłki w zakresie "od-do"

= 1.0.14 =
* Rozwiązanie problemu aby powiadomienia braly informacje o przesyłce na podstawie id przesyłki a nie nazwie

= 1.0.13 =
* Rozwiązanie problemu z pojawiającą się informacją o  paczkomacie w mailu gdy wybrana inna forma wysyłki

= 1.0.12 =
* Hotfix zwiazany z wysyłką po wersji 1.0.11.

= 1.0.11 =
* Modyfikacja powiadomień (email i strona podziękowania za zamówienie). Pokazywanie paczkomatu w tabelce.

= 1.0.10 =
* Dodanie akcji aby mozna było wyciągnąć dane paczkomatu do customowego powiadomienia mailowego

= 1.0.9 =
* Modyfikacja langów
* Poprawa Domain dla langów
* Dodanie możliwości przetłumaczenia dla innych języków
* Naprawa skryptu w panelu przy wyborze minimalnej/maksymalnej kwoty

= 1.0.8 =
* Dodanie modyfikatorów wysyłki tak aby można było ustawić do wybranej kwoty oraz od wybranej kwoty.

= 1.0.7 =
* poprawa wczytywania skryptu JavaScript w podstronach koszyka.

= 1.0.6 =
* dodanie klas "btn" oraz "button" aby przycisk w koszyku wykorzystywał style szablonu.

= 1.0.5 =
* Dodanie pola z samym numerem paczkomatu do panelu (potrzebne do intergracji z baselinkerem).

= 1.0.4 =
* Nie pokazywanie "wybrany paczkomat" gdy wybrana została inna forma wysyłki.
* Dodanie informacji o wybranym paczkomacie w potwierdzeniu mailowym do nowego zamówienia.
* Zablokowanie możliwości zakupu kiedy nie wybrano paczkomatu.
* Wywoływanie skryptów Inpostu (JS i CSS) tylko na stronie Koszyka i Kasy.

= 1.0.3 =
* Usunięcie przypadkowych znaków.
* Modyfikacja przesyłania zmiennych pomiędzy etapami.
* Usunięcie problemu z nieprawidłowymi przypisaniami paczkomatów.

= 1.0.2 =
* Modyfikacja przetwarzania danych w obrębie pluginu.
* Zamiana odwołań do plików.
* Zmiana permalinków.


= 1.0.1 =
* Dodanie ustawień na modalu.
* Dodanie ustawienia kosztów wysyłki według klas wysyłkowych.


= 1.0.0 =
* Dodanie możliwości wyboru paczkomatów.
* Mapka do paczkomatów w koszyku oraz checkoucie.
* Zapisywanie do panelu.
* Zapamiętywanie ostatniego wybranego paczkomatu.


== Support ==

Wtyczka jest darmowa i rozwijana w moim wolnym czasie. Jeśli pomogła Ci zaoszczędzić czas lub zarobić pieniądze w Twoim sklepie, poniżej znajdziesz kilka sposobów, jak możesz się odwdzięczyć i pomóc jej dalej się rozwijać:

* [Zostaw ocenę ⭐⭐⭐⭐⭐ na WordPress.org](https://wordpress.org/support/plugin/inpost-paczkomaty/reviews/) – to zajmuje minutę, a realnie pomaga innym właścicielom sklepów znaleźć tę wtyczkę.
* [Postaw mi kawę ☕](https://suppi.pl/damian-ziarnik) – wspiera dalszy, darmowy rozwój wtyczki.
* [Zleć płatne wsparcie techniczne lub customową funkcję 🛠️](https://grainsoft.pl/#kontakt) – pomagam z wdrożeniem, konfiguracją oraz rozwijam wtyczkę pod indywidualne potrzeby (dodatkowe integracje, zmiany w działaniu, dedykowane funkcje).

Masz pytanie techniczne? Najpierw sprawdź sekcję FAQ powyżej, a jeśli nie znajdziesz odpowiedzi – zapraszam do zakładki [Support](https://wordpress.org/support/plugin/inpost-paczkomaty/).

Dziękuję za korzystanie z wtyczki!