=== Inpost Paczkomaty ===
Contributors: rimosfafora
Donate link:
Tags: inpost, paczkomaty
Requires at least: 5.3
Tested up to: 6.4.1
Requires PHP: 7.4
Stable tag: 1.0.34
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
 
Umożliwia dodanie Paczkomaty Inpost jako forma dostawy produktów. Zawiera mapkę gdzie można wybrać paczkomat w którym chce się odebrać przesyłkę.

== Description ==

Wtyczka umożliwia dodanie Paczkomatów Inpost jako forma dostawy w Woocoommerce. Zawiera mapkę gdzie można wybrać paczkomat w którym chce się odebrać przesyłkę. Wskazany paczkomat jest dodawany do zamówienia w panelu. Wtyczka jest bardzo prosta i intuicyjna dla każdego użytkownika.


== Screenshots ==

1. screenshot-1.png
2. screenshot-2.png
3. screenshot-3.png
4. screenshot-4.png

== Frequently Asked Questions ==

= Jak dodać paczkomaty =

Po aktywowaniu wtyczki należy przejść do woocommerce -> ustawienia -> wysyłka-> kraj oraz dodać nową formę dostawy typu "paczkomaty inpost".

= Jak ustawić wybrany paczkomat jako adres wysyłki =

W menu głównym panelu administracyjnego należy przejść do zakładki "Inpost paczkomaty". Następnie zaznaczyć opcję na "Tak", przy polu "Zapisz wybrany paczkomat jako adres wysyłki".

= Jak dodać darmową przesyłkę =

Należy dwa razy dodać paczkomaty jako formę wysyłki. Pierwsza forma np. koszt 20zł i "Pokaż tę metodę wysyłki, gdy... maksymalna wartość 60zł" oraz druga forma koszt 0zł i "Pokaż tę metodę wysyłki, gdy... minimalna wartość 60zł". Kwoty 20zł i 60zł to są wartości przykładowe.

= Czy będzie rozwinięcie dla kurierów =

Tak, w przyszłości planuje rozbudować wtyczkę.

= U mnie nie działa / znalazłem błąd, co zrobić? =

Na początku należy chwilowo wyłączyć wszystkie wtyczki poza Woocoommerce i Inpost Paczkomaty a następnie sprawdzić czy działa, jeżeli nadal występuje problem to proszę sprawdzić na innym szablonie.

= Jak ustawić maksymalną wagę produktów dla wysyłki inpost paczkomat? =

Należy wejść do panelu administratora -> woocommerce -> inpost paczkomaty i ustawić pole "Limit wagowy" na "tak" oraz pole "Maksymalna waga". Następnie należy wybrać co ma się dziać gdy waga zostanie przekroczona. Jeżeli zostanie wybrana opcja "ukryj metodę wysyłki" to wysyłka zostanie po prostu ukryta. Natomiast gdy wybierz się "podziel na mniejsze paczki" to koszty wysyłki zostaną odpowienio pomnożone.

= Jak ustawić maksymalne wymiary produktów dla wysyłki inpost paczkomat? =

Należy wejść do panelu administratora -> woocommerce -> inpost paczkomaty i ustawić pole "Limit wymiarów" na "tak" oraz pola "Maksymalna szerokość", "maksymalna długość", "maksymalna wysokość". Gdy któryś z przedmiotów w koszyku przekroczy wymiary to forma wysyłki inpost paczkomaty zostanie ukryta.

= Jak ustawić logo w koszyku dla inpost paczkomaty =

Należy wejść do panelu administratora -> woocommerce -> inpost paczkomaty i ustawić pole "Pokaż logo w koszyku i kasie" na "tak" oraz wybrać odpowiednie logo z biblioteki mediów. Maksymalna szerokość loga jaka się bedzie wyswietlać to 150px.

= Pojawia mi się forma dostawy, lecz nie pojawia się przycisk "wybierz paczkomat", co mogę zrobić? =

Od jakiegoś czasu Woocommerce testuje bloki gutenbergowe w koszykach i kasach. Aby ustawić klasyczny koszyk należy przejść w panelu admina do: Woocommerce->inpost paczkomaty a następnie na dole nacisnąć przycisk przywróć przy "Przywróć klasyczny widok koszyka i kasy". Nadpisze to obecne ustawienia koszyka oraz checkoutu dlatego zaleca się wcześniej zrobienia kopii zapasowej.

== Changelog ==

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