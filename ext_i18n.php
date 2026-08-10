<?php
declare(strict_types=1);

function forum_ext_current_language(): string
{
    $language = forum_ext_setting('forum_language');
    return array_key_exists($language, forum_ext_languages()) ? $language : 'en';
}

function forum_ext_language_defaults(string $language): array
{
    $defaults = [
        'pl' => [
            'settings' => [
                'brand_tagline' => 'samodzielne forum dla Twojej społeczności',
                'home_intro_title' => 'ForumForgeCMS',
                'home_intro_text' => 'Lekka przestrzeń do rozmowy, wymiany wiedzy i budowania społeczności wokół dowolnego tematu. ForumForgeCMS daje prosty start, czytelne działy i spokojne miejsce na dyskusje, które z czasem może urosnąć razem z użytkownikami.',
            ],
            'sections' => [
                'start' => ['Start i organizacja', 'Ogłoszenia, zasady, pierwsze pytania i sprawy techniczne związane z działaniem forum.'],
                'community' => ['Społeczność i rozmowy', 'Dyskusje, pomysły, prezentacje projektów i luźniejsze tematy budujące życie forum.'],
            ],
            'categories' => [
                'announcements' => ['Ogłoszenia i aktualności', 'Ważne informacje od administracji, zmiany w forum, komunikaty techniczne i zapowiedzi kolejnych usprawnień.'],
                'first-steps' => ['Pierwsze kroki', 'Dział dla nowych użytkowników: przedstaw się, zapytaj jak zacząć i spokojnie poznaj zasady działania forum.'],
                'support' => ['Pomoc i pytania techniczne', 'Problemy z kontem, ustawieniami forum, działaniem strony i inne pytania organizacyjno-techniczne.'],
                'rules' => ['Regulamin i zasady', 'Najważniejsze zasady korzystania z forum, dobre praktyki dyskusji i informacje porządkowe dla społeczności.'],
                'ideas' => ['Pomysły i sugestie', 'Miejsce na propozycje zmian, nowe funkcje, usprawnienia działów i uwagi dotyczące rozwoju forum.'],
                'news' => ['Nowości na forum', 'Krótki przegląd zmian, nowych możliwości i rzeczy, które warto zauważyć po kolejnych aktualizacjach.'],
                'general' => ['Rozmowy ogólne', 'Swobodne dyskusje społeczności, pomysły, pytania bez sztywnej kategorii i codzienne rozmowy użytkowników.'],
                'projects' => ['Projekty i inspiracje', 'Pokaż co budujesz, opisz swój projekt, poproś o opinię albo zainspiruj innych ciekawym rozwiązaniem.'],
                'offtopic' => ['Off-topic', 'Luźniejsze tematy, rozmowy poboczne i wszystko to, co buduje klimat forum, ale nie pasuje do pozostałych działów.'],
            ],
            'topics' => [
                'welcome' => ['Witamy w ForumForgeCMS', "To jest pierwszy komunikat administracyjny na forum.\n\nForumForgeCMS zostało uruchomione i jest gotowe do konfiguracji. Administrator może teraz dopasować kategorie, działy, opis strony, regulamin oraz ustawienia rejestracji do swojej społeczności."],
                'announcements-howto' => ['Jak korzystać z ogłoszeń', "Ten dział służy do publikowania ważnych informacji od administracji.\n\nWarto umieszczać tutaj komunikaty o zmianach w forum, planowanych pracach technicznych, nowych funkcjach oraz zasadach, które powinni znać wszyscy użytkownicy."],
                'after-install' => ['Pierwsze rzeczy po instalacji', "Po pierwszym uruchomieniu forum zalecamy wykonać kilka kroków:\n\n1. Zmień hasło administratora.\n2. Uzupełnij adres e-mail konta administratora.\n3. Przejrzyj domyślne kategorie i działy.\n4. Dopasuj opis forum do swojej społeczności.\n5. Sprawdź, czy katalog forum-data jest niedostępny z przeglądarki."],
                'announcement-rules' => ['Zasady publikowania komunikatów', "Komunikaty administracyjne powinny być krótkie, jasne i konkretne.\n\nJeśli informacja dotyczy wszystkich użytkowników, warto ją przypiąć. Jeśli jest tymczasowa, można ją później odpiąć albo przenieść do odpowiedniego działu."],
                'new-users' => ['Przewodnik dla nowych użytkowników', "Witaj na forum.\n\nJeśli jesteś tu pierwszy raz, zacznij od założenia konta, przeczytania regulaminu i krótkiego rozejrzenia się po działach. Gdy zadajesz pytanie, opisz dokładnie sytuację, dodaj ważne szczegóły i wybierz dział, który najlepiej pasuje do tematu."],
            ],
        ],
        'en' => [
            'settings' => [
                'brand_tagline' => 'an independent forum for your community',
                'home_intro_title' => 'ForumForgeCMS',
                'home_intro_text' => 'A lightweight space for conversation, knowledge sharing and building a community around any topic. ForumForgeCMS gives you a simple start, clear forums and a calm place for discussions that can grow together with your users.',
            ],
            'sections' => [
                'start' => ['Start and organization', 'Announcements, rules, first questions and technical matters related to the forum.'],
                'community' => ['Community and discussions', 'Discussions, ideas, project showcases and lighter topics that build forum life.'],
            ],
            'categories' => [
                'announcements' => ['Announcements and updates', 'Important administration notes, forum changes, technical messages and upcoming improvements.'],
                'first-steps' => ['First steps', 'A place for new users: introduce yourself, ask how to start and learn the forum rules.'],
                'support' => ['Help and technical questions', 'Account issues, forum settings, site operation and other organizational or technical questions.'],
                'rules' => ['Rules and guidelines', 'The most important forum rules, discussion practices and community information.'],
                'ideas' => ['Ideas and suggestions', 'A place for change proposals, new features, forum improvements and development feedback.'],
                'news' => ['Forum news', 'A short overview of changes, new possibilities and things worth noticing after updates.'],
                'general' => ['General discussion', 'Open community discussions, ideas, uncategorized questions and everyday user conversations.'],
                'projects' => ['Projects and inspiration', 'Show what you are building, describe your project, ask for feedback or inspire others.'],
                'offtopic' => ['Off-topic', 'Lighter topics, side conversations and everything that builds forum atmosphere but fits nowhere else.'],
            ],
            'topics' => [
                'welcome' => ['Welcome to ForumForgeCMS', "This is the first administrative announcement on the forum.\n\nForumForgeCMS has been launched and is ready for configuration. The administrator can now adjust categories, forums, site description, rules and registration settings for the community."],
                'announcements-howto' => ['How to use announcements', "This forum is used to publish important information from the administration.\n\nIt is worth placing announcements here about forum changes, planned technical work, new features and rules that all users should know."],
                'after-install' => ['First things after installation', "After the first forum launch, we recommend a few steps:\n\n1. Change the administrator password.\n2. Add the administrator account email address.\n3. Review the default categories and forums.\n4. Adjust the forum description to your community.\n5. Check that the forum-data directory is not accessible from the browser."],
                'announcement-rules' => ['Rules for publishing announcements', "Administrative announcements should be short, clear and specific.\n\nIf the information concerns all users, it is worth pinning it. If it is temporary, it can later be unpinned or moved to the proper forum."],
                'new-users' => ['Guide for new users', "Welcome to the forum.\n\nIf this is your first time here, start by creating an account, reading the rules and taking a quick look around the forums. When asking a question, describe the situation clearly, add important details and choose the forum that best fits the topic."],
            ],
        ],
        'de' => [
            'settings' => [
                'brand_tagline' => 'ein eigenständiges Forum für deine Gemeinschaft',
                'home_intro_title' => 'ForumForgeCMS',
                'home_intro_text' => 'Ein leichter Raum für Gespräche, Wissensaustausch und den Aufbau einer Gemeinschaft rund um jedes Thema. ForumForgeCMS bietet einen einfachen Start, klare Forenbereiche und einen ruhigen Ort für Diskussionen, der mit den Benutzern wachsen kann.',
            ],
            'sections' => [
                'start' => ['Start und Organisation', 'Ankündigungen, Regeln, erste Fragen und technische Themen rund um den Betrieb des Forums.'],
                'community' => ['Gemeinschaft und Gespräche', 'Diskussionen, Ideen, Projektvorstellungen und lockere Themen, die das Forenleben gestalten.'],
            ],
            'categories' => [
                'announcements' => ['Ankündigungen und Neuigkeiten', 'Wichtige Informationen der Administration, Änderungen im Forum, technische Hinweise und kommende Verbesserungen.'],
                'first-steps' => ['Erste Schritte', 'Bereich für neue Benutzer: Stelle dich vor, frage nach dem Einstieg und lerne in Ruhe die Forenregeln kennen.'],
                'support' => ['Hilfe und technische Fragen', 'Probleme mit Konto, Foreneinstellungen, Seitenbetrieb und andere organisatorische oder technische Fragen.'],
                'rules' => ['Regeln und Richtlinien', 'Die wichtigsten Regeln für das Forum, gute Diskussionspraxis und organisatorische Hinweise für die Gemeinschaft.'],
                'ideas' => ['Ideen und Vorschläge', 'Ein Ort für Änderungsvorschläge, neue Funktionen, Verbesserungen und Hinweise zur Weiterentwicklung des Forums.'],
                'news' => ['Neuigkeiten im Forum', 'Ein kurzer Überblick über Änderungen, neue Möglichkeiten und Dinge, die nach Updates wichtig sind.'],
                'general' => ['Allgemeine Gespräche', 'Freie Diskussionen der Gemeinschaft, Ideen, Fragen ohne feste Kategorie und alltägliche Gespräche.'],
                'projects' => ['Projekte und Inspiration', 'Zeige, was du baust, beschreibe dein Projekt, bitte um Feedback oder inspiriere andere.'],
                'offtopic' => ['Off-topic', 'Lockere Themen, Nebengespräche und alles, was die Atmosphäre des Forums prägt, aber in keinen anderen Bereich passt.'],
            ],
            'topics' => [
                'welcome' => ['Willkommen bei ForumForgeCMS', "Dies ist die erste administrative Ankündigung im Forum.\n\nForumForgeCMS wurde gestartet und ist bereit zur Konfiguration. Der Administrator kann nun Kategorien, Forenbereiche, Seitenbeschreibung, Regeln und Registrierungseinstellungen an die Gemeinschaft anpassen."],
                'announcements-howto' => ['So nutzt man Ankündigungen', "Dieser Bereich dient zur Veröffentlichung wichtiger Informationen der Administration.\n\nHier sollten Hinweise zu Änderungen im Forum, geplanten technischen Arbeiten, neuen Funktionen und Regeln veröffentlicht werden, die alle Benutzer kennen sollten."],
                'after-install' => ['Erste Schritte nach der Installation', "Nach dem ersten Start des Forums empfehlen wir einige Schritte:\n\n1. Ändere das Administratorpasswort.\n2. Ergänze die E-Mail-Adresse des Administratorkontos.\n3. Prüfe die Standardkategorien und Forenbereiche.\n4. Passe die Forenbeschreibung an deine Gemeinschaft an.\n5. Prüfe, ob das Verzeichnis forum-data im Browser nicht erreichbar ist."],
                'announcement-rules' => ['Regeln für Ankündigungen', "Administrative Ankündigungen sollten kurz, klar und konkret sein.\n\nWenn die Information alle Benutzer betrifft, sollte sie angeheftet werden. Ist sie nur vorübergehend, kann sie später gelöst oder in den passenden Bereich verschoben werden."],
                'new-users' => ['Leitfaden für neue Benutzer', "Willkommen im Forum.\n\nWenn du zum ersten Mal hier bist, erstelle ein Konto, lies die Regeln und sieh dich kurz in den Forenbereichen um. Wenn du eine Frage stellst, beschreibe die Situation genau, füge wichtige Details hinzu und wähle den passenden Bereich."],
            ],
        ],
    ];

    return $defaults[$language] ?? $defaults['en'];
}

function forum_ext_known_default_values(string $group, string $key, int $index): array
{
    $values = [];
    foreach (['pl', 'en', 'de'] as $language) {
        $defaults = forum_ext_language_defaults($language);
        if (isset($defaults[$group][$key][$index])) {
            $values[] = $defaults[$group][$key][$index];
        }
    }

    return array_values(array_unique($values));
}

function forum_ext_apply_language_defaults(string $language): void
{
    $defaults = forum_ext_language_defaults($language);
    $pdo = forum_db();

    foreach ($defaults['settings'] as $key => $value) {
        $current = forum_ext_setting($key);
        $known = [];
        foreach (['pl', 'en', 'de'] as $knownLanguage) {
            $knownDefaults = forum_ext_language_defaults($knownLanguage);
            $known[] = $knownDefaults['settings'][$key] ?? '';
        }
        if (in_array($current, $known, true)) {
            forum_ext_store_setting($pdo, $key, $value);
        }
    }

    $sectionSlugs = array_keys($defaults['sections']);
    $sectionRows = $pdo->query('SELECT id, name, description FROM forum_sections ORDER BY position ASC, id ASC')->fetchAll();
    $updateSection = $pdo->prepare('UPDATE forum_sections SET name = :name, description = :description WHERE id = :id');
    foreach ($sectionRows as $index => $row) {
        $slug = $sectionSlugs[$index] ?? null;
        if ($slug === null) {
            continue;
        }
        [$name, $description] = $defaults['sections'][$slug];
        if (in_array((string) $row['name'], forum_ext_known_default_values('sections', $slug, 0), true)) {
            $updateSection->execute([':name' => $name, ':description' => $description, ':id' => (int) $row['id']]);
        }
    }

    $categorySlugs = array_keys($defaults['categories']);
    $categoryRows = $pdo->query('SELECT id, name, description FROM categories ORDER BY position ASC, id ASC')->fetchAll();
    $updateCategory = $pdo->prepare('UPDATE categories SET name = :name, description = :description WHERE id = :id');
    foreach ($categoryRows as $index => $row) {
        $slug = $categorySlugs[$index] ?? null;
        if ($slug === null) {
            continue;
        }
        [$name, $description] = $defaults['categories'][$slug];
        if (in_array((string) $row['name'], forum_ext_known_default_values('categories', $slug, 0), true)) {
            $updateCategory->execute([':name' => $name, ':description' => $description, ':id' => (int) $row['id']]);
        }
    }

    $topicRows = $pdo->query(
        'SELECT t.id, t.title, p.id AS post_id, p.body
         FROM topics t
         LEFT JOIN posts p ON p.id = (
             SELECT p2.id FROM posts p2 WHERE p2.topic_id = t.id ORDER BY p2.created_at ASC, p2.id ASC LIMIT 1
         )
         ORDER BY t.id ASC'
    )->fetchAll();
    $updateTopic = $pdo->prepare('UPDATE topics SET title = :title WHERE id = :id');
    $updatePost = $pdo->prepare('UPDATE posts SET body = :body WHERE id = :id');
    foreach ($topicRows as $row) {
        foreach ($defaults['topics'] as $slug => [$title, $body]) {
            if (!in_array((string) $row['title'], forum_ext_known_default_values('topics', $slug, 0), true)) {
                continue;
            }
            $updateTopic->execute([':title' => $title, ':id' => (int) $row['id']]);
            if ((int) ($row['post_id'] ?? 0) > 0 && in_array((string) ($row['body'] ?? ''), forum_ext_known_default_values('topics', $slug, 1), true)) {
                $updatePost->execute([':body' => $body, ':id' => (int) $row['post_id']]);
            }
            break;
        }
    }
}

function forum_ext_translation_map(string $language): array
{
    $commonEn = [
        'Polski' => 'Polish',
        'Angielski' => 'English',
        'Niemiecki' => 'German',
        'Język forum' => 'Forum language',
        'Ustawienia podstawowe' => 'Basic settings',
        'Kopia zapasowa i przywracanie forum' => 'Forum backup and restore',
        'Kopia danych' => 'Data backup',
        'Pobieranie' => 'Download',
        'Pobierz kopię zapasową' => 'Download backup',
        'Pobierz backup ZIP' => 'Download ZIP backup',
        'Przywracanie' => 'Restore',
        'Przywróć forum z kopii' => 'Restore forum from backup',
        'Przywróć backup' => 'Restore backup',
        'Plik kopii zapasowej ZIP' => 'Backup ZIP file',
        'Pobierz komplet danych forum jako ZIP: bazę SQLite, avatary użytkowników oraz własne logo forum. Taki plik możesz później wgrać na innym hostingu i przywrócić forum z panelu administratora.' => 'Download all forum data as a ZIP file: the SQLite database, user avatars and custom forum logo. You can later upload this file on another host and restore the forum from the administrator panel.',
        'Archiwum zawiera' => 'The archive contains',
        'katalog' => 'directory',
        'oraz katalog' => 'and directory',
        'Przywracanie zastąpi aktualną bazę danych, avatary i logo zawartością przesłanej kopii. Po tej operacji odśwież stronę i zaloguj się danymi z przywróconej bazy.' => 'Restoring replaces the current database, avatars and logo with the uploaded backup contents. After this operation, refresh the page and log in with the data from the restored database.',
        'Kopia zapasowa została przywrócona. Forum korzysta teraz z danych z przesłanego archiwum.' => 'The backup has been restored. The forum now uses data from the uploaded archive.',
        'Podstawowe' => 'Basic',
        'Ustawienia' => 'Settings',
        'Administrator' => 'Administrator',
        'Najaktywniejsi' => 'Most active',
        'Moderacja' => 'Moderation',
        'Użytkownicy' => 'Users',
        'Użytkownik' => 'User',
        'Zarządzanie ustawieniami, działami i aktywnością forum.' => 'Manage forum settings, forums and activity.',
        'Zarządzanie ustawieniami, działami, użytkownikami i aktywnością forum.' => 'Manage forum settings, forums, users and activity.',
        'Zgłoszenia postów i narzędzia porządkowe forum.' => 'Post reports and forum moderation tools.',
        'Podsumowanie forum' => 'Forum summary',
        'Zapisz ustawienia podstawowe' => 'Save basic settings',
        'Widok forum i rejestracja' => 'Forum view and registration',
        'Panel administratora' => 'Administrator panel',
        'Panel admina' => 'Admin panel',
        'Panel moderatora' => 'Moderator panel',
        'Szukaj' => 'Search',
        'Wyszukiwarka forum' => 'Forum search',
        'Szukaj tematów i postów opublikowanych na forum.' => 'Search topics and posts published on the forum.',
        'Wpisz szukaną frazę, aby znaleźć tematy i posty na forum.' => 'Enter a phrase to find topics and posts on the forum.',
        'Szukana fraza' => 'Search phrase',
        'Wpisz minimum 2 znaki' => 'Enter at least 2 characters',
        'Wpisz frazę, aby rozpocząć wyszukiwanie.' => 'Enter a phrase to start searching.',
        'Wyniki obejmują tytuły tematów oraz treść postów.' => 'Results include topic titles and post contents.',
        'Fraza jest za krótka.' => 'The phrase is too short.',
        'Wpisz co najmniej 2 znaki, aby przeszukać forum.' => 'Enter at least 2 characters to search the forum.',
        'Wyniki' => 'Results',
        'Wyniki wyszukiwania' => 'Search results',
        'Znaleziono' => 'Found',
        'wyników dla:' => 'results for:',
        'Brak wyników.' => 'No results.',
        'Spróbuj użyć krótszej albo innej frazy.' => 'Try a shorter or different phrase.',
        'Post' => 'Post',
        'Temat' => 'Topic',
        'Tytuł tematu pasuje do szukanej frazy.' => 'The topic title matches the search phrase.',
        'Zgłoszenia postów' => 'Post reports',
        'Zarządzaj użytkownikami i moderatorami' => 'Manage users and moderators',
        'Użytkownicy z największą aktywnością' => 'Most active users',
        'Zarządzaj kategoriami' => 'Manage categories',
        'Dodaj kategorię' => 'Add category',
        'Zarządzaj działami' => 'Manage forums',
        'Dodaj dział forum' => 'Add forum',
        'Nazwa forum' => 'Forum name',
        'Krótki opis forum' => 'Short forum description',
        'Własne logo forum' => 'Custom forum logo',
        'Styl graficzny forum' => 'Forum visual style',
        'Tytuł forum' => 'Forum title',
        'Opis forum' => 'Forum description',
        'Pozwól użytkownikom zakładać nowe konta' => 'Allow users to create new accounts',
        'Zapisz ustawienia' => 'Save settings',
        'Nazwa kategorii' => 'Category name',
        'Opis kategorii' => 'Category description',
        'Nazwa działu' => 'Forum name',
        'Opis działu' => 'Forum description',
        'Kategorie' => 'Categories',
        'Kategoria' => 'Category',
        'Działy' => 'Forums',
        'Nowa kategoria' => 'New category',
        'Nowy dział' => 'New forum',
        'Dodaj dział' => 'Add forum',
        'Zapisz kategorię' => 'Save category',
        'Zapisz dział' => 'Save forum',
        'Ustawienia forum zostały zapisane.' => 'Forum settings have been saved.',
        'Nowa kategoria forum została dodana.' => 'A new forum category has been added.',
        'Nowy dział został dodany.' => 'A new forum has been added.',
        'Społeczność' => 'Community',
        'Forum' => 'Forum',
        'Menu' => 'Menu',
        'Zalogowany jako' => 'Logged in as',
        'Moje konto' => 'My account',
        'Ustawienia profilu, avatar, hasło i statystyki aktywności.' => 'Profile settings, avatar, password and activity statistics.',
        'Wiadomości' => 'Messages',
        'Wyloguj' => 'Log out',
        'Dołącz do dyskusji' => 'Join the discussion',
        'Załóż konto, aby pisać posty, wysyłać prywatne wiadomości i budować swój profil na forum.' => 'Create an account to write posts, send private messages and build your forum profile.',
        'Załóż konto' => 'Create account',
        'Zaloguj się' => 'Log in',
        'Reset hasła' => 'Password reset',
        'użytkowników' => 'users',
        'tematów' => 'topics',
        'postów' => 'posts',
        'lajków' => 'likes',
        'wiadomości prywatnych' => 'private messages',
        'Najnowsze' => 'Latest',
        'Ostatnio aktywne tematy' => 'Recently active topics',
        'Aktywni użytkownicy' => 'Active users',
        'Kto najczęściej pomaga' => 'Who helps most often',
        'autor:' => 'author:',
        'Avatar' => 'Avatar',
        'Nowy avatar' => 'New avatar',
        'Wyślij avatar' => 'Upload avatar',
        'Usuń avatar' => 'Remove avatar',
        'Hasło' => 'Password',
        'Aktualne hasło' => 'Current password',
        'Nowe hasło' => 'New password',
        'Zmień hasło' => 'Change password',
        'Moje tematy' => 'My topics',
        'Moje posty' => 'My posts',
        'Ostatnio założone' => 'Recently created',
        'Ostatnie odpowiedzi' => 'Latest replies',
        'Ostatnie tematy' => 'Latest topics',
        'Ostatnie posty' => 'Latest posts',
        'otrzymanych lajków' => 'received likes',
        'danych lajków' => 'given likes',
        'na forum od' => 'member since',
        'Napisz wiadomość' => 'Write message',
        'Ostatnie logowanie:' => 'Last login:',
        'jeszcze brak' => 'not yet',
        'Nie ma aktywnych zgłoszeń do sprawdzenia.' => 'There are no active reports to review.',
        'Zgłosił:' => 'Reported by:',
        'Autor posta:' => 'Post author:',
        'Przejdź do posta' => 'Go to post',
        'Zamknij spór' => 'Close report',
        'Powód:' => 'Reason:',
        'Kliknij nazwę użytkownika, żeby otworzyć jego kartę edycji, zmienić rolę albo wykonać operacje administracyjne.' => 'Click a user name to open their edit card, change their role or perform administrative actions.',
        'Administratorzy i moderatorzy' => 'Administrators and moderators',
        'Zwykli użytkownicy' => 'Regular users',
        'Nie ma jeszcze zwykłych użytkowników.' => 'There are no regular users yet.',
        'Wróć do listy' => 'Back to list',
        'Profil publiczny' => 'Public profile',
        'Nazwa użytkownika' => 'Username',
        'Rola' => 'Role',
        'zostaw puste, jeśli bez zmian' => 'leave empty to keep unchanged',
        'To główne konto administratora forum. Możesz zmienić jego e-mail i hasło, ale nie nazwę ani rolę.' => 'This is the main forum administrator account. You can change its email and password, but not its name or role.',
        'Moderator może przypinać, zamykać i usuwać tematy oraz edytować posty podczas moderacji.' => 'A moderator can pin, close and delete topics, and edit posts during moderation.',
        'Zapisz użytkownika' => 'Save user',
        'Usuń całą aktywność' => 'Delete all activity',
        'Ta operacja usuwa tematy, posty, lajki oraz prywatne wiadomości użytkownika. Samo konto zostaje na forum. Potwierdzenie przyjdzie na adres administratora.' => 'This operation deletes the user topics, posts, likes and private messages. The account itself remains on the forum. A confirmation will be sent to the administrator email address.',
        'Usuń użytkownika' => 'Delete user',
        'Ta operacja usuwa konto użytkownika oraz jego dane z bazy forum. Nie zostawia profilu ani historii prywatnych wiadomości tego konta.' => 'This operation deletes the user account and its data from the forum database. It leaves no profile or private message history for this account.',
        'W tej kategorii nie ma jeszcze działów.' => 'There are no forums in this category yet.',
        'Wyżej w kategorii' => 'Higher in category',
        'Niżej w kategorii' => 'Lower in category',
        'Zapisz zmiany' => 'Save changes',
        'Wyżej' => 'Higher',
        'Niżej' => 'Lower',
        'Stronę zbudował' => 'Site built by',
        'Menu panelu administratora' => 'Administrator panel menu',
        'Avatar użytkownika' => 'User avatar',
        'Wysłać wiadomość potwierdzającą usunięcie całej aktywności tego użytkownika?' => 'Send a confirmation message to delete all activity of this user?',
        'Na pewno całkowicie usunąć tego użytkownika, jego aktywność, wiadomości, lajki, tokeny i avatar? Tej operacji nie da się cofnąć.' => 'Are you sure you want to completely delete this user, their activity, messages, likes, tokens and avatar? This action cannot be undone.',
        'Przywracanie zastąpi aktualne dane forum. Kontynuować?' => 'Restoring will replace the current forum data. Continue?',
        'Konto zostało utworzone. Możesz się teraz zalogować.' => 'The account has been created. You can now log in.',
        'Zalogowano pomyślnie.' => 'Logged in successfully.',
        'Zostałeś wylogowany.' => 'You have been logged out.',
        'Hasło zostało zmienione.' => 'The password has been changed.',
        'Avatar został zaktualizowany.' => 'The avatar has been updated.',
        'Avatar został usunięty.' => 'The avatar has been removed.',
        'Temat został utworzony.' => 'The topic has been created.',
        'Odpowiedź została dodana.' => 'The reply has been added.',
        'Post został zaktualizowany.' => 'The post has been updated.',
        'Post został zgłoszony moderatorom.' => 'The post has been reported to moderators.',
        'Wiadomość została wysłana.' => 'The message has been sent.',
        'Jeśli konto istnieje, wysłaliśmy link do zmiany hasła na powiązany adres e-mail.' => 'If the account exists, we have sent a password change link to the related email address.',
        'Hasło zostało ustawione. Możesz się teraz zalogować.' => 'The password has been set. You can now log in.',
        'Kategoria forum została zaktualizowana.' => 'The forum category has been updated.',
        'Dział został zaktualizowany.' => 'The forum has been updated.',
        'Kolejność działów została zmieniona.' => 'The forum order has been changed.',
        'Dane użytkownika zostały zaktualizowane.' => 'User details have been updated.',
        'Wysłaliśmy wiadomość potwierdzającą na adres administratora. Dopiero po kliknięciu w link aktywność użytkownika zostanie usunięta.' => 'We have sent a confirmation message to the administrator email address. The user activity will be deleted only after clicking the link.',
        'Status tematu został zaktualizowany.' => 'The topic status has been updated.',
        'Temat został usunięty.' => 'The topic has been deleted.',
        'Zgłoszenie zostało zamknięte.' => 'The report has been closed.',
        'Zaloguj się, aby wykonać tę akcję.' => 'Log in to perform this action.',
        'Ta sekcja jest dostepna tylko dla administratora.' => 'This section is available only to the administrator.',
        'Ta sekcja jest dostepna tylko dla moderatora lub administratora.' => 'This section is available only to a moderator or administrator.',
        'Rejestracja nowych kont jest chwilowo wyłączona.' => 'Registration of new accounts is temporarily disabled.',
        'Nie znaleziono tematu.' => 'Topic not found.',
        'Ten temat jest zamknięty.' => 'This topic is closed.',
        'Nie znaleziono wskazanego postu.' => 'The selected post was not found.',
        'Nie znaleziono wiadomości.' => 'Message not found.',
        'Aby założyć konto, musisz zaakceptować regulamin strony i forum.' => 'To create an account, you must accept the site and forum rules.',
        'Nazwa użytkownika musi mieć 3-40 znaków i może zawierać litery, cyfry, kropki, myślniki oraz podkreślenia.' => 'The username must be 3-40 characters and may contain letters, numbers, dots, hyphens and underscores.',
        'Podaj poprawny adres e-mail.' => 'Enter a valid email address.',
        'Podaj poprawny adres e-mail użytkownika.' => 'Enter a valid user email address.',
        'Hasło musi mieć co najmniej 10 znaków.' => 'The password must be at least 10 characters long.',
        'Nie udało się zalogować. Sprawdź login i hasło.' => 'Login failed. Check your login and password.',
        'Aktualne hasło jest niepoprawne.' => 'The current password is incorrect.',
        'Nowe hasło musi mieć co najmniej 10 znaków.' => 'The new password must be at least 10 characters long.',
        'Nie znaleziono użytkownika do edycji.' => 'No user was found for editing.',
        'Wybrana rola użytkownika jest nieprawidłowa.' => 'The selected user role is invalid.',
        'Taki login albo adres e-mail jest juz zajety.' => 'This login or email address is already taken.',
        'Nowe hasło dla użytkownika musi mieć co najmniej 10 znaków.' => 'The new password for the user must be at least 10 characters long.',
        'Nazwa kategorii forum nie może być pusta.' => 'The forum category name cannot be empty.',
        'Nazwa działu nie może być pusta.' => 'The forum name cannot be empty.',
        'Najpierw przenieś lub usuń tematy z tego działu.' => 'First move or delete topics from this forum.',
        'Nieznana flaga tematu.' => 'Unknown topic flag.',
        'Sesja formularza wygasła. Odśwież stronę i spróbuj ponownie.' => 'The form session has expired. Refresh the page and try again.',
        'Wykonujesz te akcje zbyt szybko. Odczekaj chwilę i spróbuj ponownie.' => 'You are performing these actions too quickly. Wait a moment and try again.',
        'Logowanie' => 'Login',
        'Rejestracja' => 'Registration',
        'Login lub e-mail' => 'Login or email',
        'Wyślij link do resetu' => 'Send reset link',
        'Ustaw nowe hasło' => 'Set new password',
        'Nie pamiętasz hasła?' => 'Forgot your password?',
        'Przywróć dostęp do forum przez link wysłany e-mailem.' => 'Restore forum access using a link sent by email.',
        'Załóż konto na ForumForgeCMS.' => 'Create an account on ForumForgeCMS.',
        'Zaloguj sie do ForumForgeCMS.' => 'Log in to ForumForgeCMS.',
        'Ustaw nowe hasło do ForumForgeCMS.' => 'Set a new password for ForumForgeCMS.',
        'Dział:' => 'Forum:',
        'Autor:' => 'Author:',
        'Założono:' => 'Created:',
        'Wróć do działu' => 'Back to forum',
        'Wróć do listy działów' => 'Back to forum list',
        'Edytuj' => 'Edit',
        'Cytuj' => 'Quote',
        'Zgłoś' => 'Report',
        'Ostatnio edytowano przez:' => 'Last edited by:',
        'Start i organizacja' => 'Start and organization',
        'Ogłoszenia, zasady, pierwsze pytania i sprawy techniczne związane z działaniem forum.' => 'Announcements, rules, first questions and technical matters related to the forum.',
        'Społeczność i rozmowy' => 'Community and discussions',
        'Dyskusje, pomysły, prezentacje projektów i luźniejsze tematy budujące życie forum.' => 'Discussions, ideas, project showcases and lighter topics that build forum life.',
        'Ogłoszenia i aktualności' => 'Announcements and updates',
        'Ważne informacje od administracji, zmiany w forum, komunikaty techniczne i zapowiedzi kolejnych usprawnień.' => 'Important administration notes, forum changes, technical messages and upcoming improvements.',
        'Pierwsze kroki' => 'First steps',
        'Dział dla nowych użytkowników: przedstaw się, zapytaj jak zacząć i spokojnie poznaj zasady działania forum.' => 'A place for new users: introduce yourself, ask how to start and learn the forum rules.',
        'Pomoc i pytania techniczne' => 'Help and technical questions',
        'Regulamin i zasady' => 'Rules and guidelines',
        'Pomysły i sugestie' => 'Ideas and suggestions',
        'Nowości na forum' => 'Forum news',
        'Rozmowy ogólne' => 'General discussion',
        'Projekty i inspiracje' => 'Projects and inspiration',
        'Witamy w ForumForgeCMS' => 'Welcome to ForumForgeCMS',
        'Jak korzystać z ogłoszeń' => 'How to use announcements',
        'Pierwsze rzeczy po instalacji' => 'First things after installation',
        'Zasady publikowania komunikatów' => 'Rules for publishing announcements',
        'Przewodnik dla nowych użytkowników' => 'Guide for new users',
        'To jest pierwszy komunikat administracyjny na forum.' => 'This is the first administrative announcement on the forum.',
        'ForumForgeCMS zostało uruchomione i jest gotowe do konfiguracji. Administrator może teraz dopasować kategorie, działy, opis strony, regulamin oraz ustawienia rejestracji do swojej społeczności.' => 'ForumForgeCMS has been launched and is ready for configuration. The administrator can now adjust categories, forums, site description, rules and registration settings for the community.',
        'Ten dział służy do publikowania ważnych informacji od administracji.' => 'This forum is used to publish important information from the administration.',
        'Warto umieszczać tutaj komunikaty o zmianach w forum, planowanych pracach technicznych, nowych funkcjach oraz zasadach, które powinni znać wszyscy użytkownicy.' => 'It is worth placing announcements here about forum changes, planned technical work, new features and rules that all users should know.',
        'Po pierwszym uruchomieniu forum zalecamy wykonać kilka kroków:' => 'After the first forum launch, we recommend a few steps:',
        'Zmień hasło administratora.' => 'Change the administrator password.',
        'Uzupełnij adres e-mail konta administratora.' => 'Add the administrator account email address.',
        'Przejrzyj domyślne kategorie i działy.' => 'Review the default categories and forums.',
        'Dopasuj opis forum do swojej społeczności.' => 'Adjust the forum description to your community.',
        'Sprawdź, czy katalog forum-data jest niedostępny z przeglądarki.' => 'Check that the forum-data directory is not accessible from the browser.',
        'Komunikaty administracyjne powinny być krótkie, jasne i konkretne.' => 'Administrative announcements should be short, clear and specific.',
        'Jeśli informacja dotyczy wszystkich użytkowników, warto ją przypiąć. Jeśli jest tymczasowa, można ją później odpiąć albo przenieść do odpowiedniego działu.' => 'If the information concerns all users, it is worth pinning it. If it is temporary, it can later be unpinned or moved to the proper forum.',
        'Witaj na forum.' => 'Welcome to the forum.',
        'Jeśli jesteś tu pierwszy raz, zacznij od założenia konta, przeczytania regulaminu i krótkiego rozejrzenia się po działach. Gdy zadajesz pytanie, opisz dokładnie sytuację, dodaj ważne szczegóły i wybierz dział, który najlepiej pasuje do tematu.' => 'If this is your first time here, start by creating an account, reading the rules and taking a quick look around the forums. When asking a question, describe the situation clearly, add important details and choose the forum that best fits the topic.',
        'Lekka przestrzeń do rozmowy, wymiany wiedzy i budowania społeczności wokół dowolnego tematu. ForumForgeCMS daje prosty start, czytelne działy i spokojne miejsce na dyskusje, które z czasem może urosnąć razem z użytkownikami.' => 'A lightweight space for conversation, knowledge sharing and building a community around any topic. ForumForgeCMS gives you a simple start, clear sections and a calm place for discussions that can grow together with your users.',
        'samodzielne forum dla Twojej społeczności' => 'an independent forum for your community',
    ];

    $commonDe = [
        'Polski' => 'Polnisch',
        'Angielski' => 'Englisch',
        'Niemiecki' => 'Deutsch',
        'Język forum' => 'Sprache des Forums',
        'Ustawienia podstawowe' => 'Grundeinstellungen',
        'Kopia zapasowa i przywracanie forum' => 'Forumsicherung und Wiederherstellung',
        'Kopia danych' => 'Datensicherung',
        'Pobieranie' => 'Download',
        'Pobierz kopię zapasową' => 'Sicherung herunterladen',
        'Pobierz backup ZIP' => 'ZIP-Sicherung herunterladen',
        'Przywracanie' => 'Wiederherstellung',
        'Przywróć forum z kopii' => 'Forum aus Sicherung wiederherstellen',
        'Przywróć backup' => 'Sicherung wiederherstellen',
        'Plik kopii zapasowej ZIP' => 'ZIP-Sicherungsdatei',
        'Pobierz komplet danych forum jako ZIP: bazę SQLite, avatary użytkowników oraz własne logo forum. Taki plik możesz później wgrać na innym hostingu i przywrócić forum z panelu administratora.' => 'Lade alle Forendaten als ZIP-Datei herunter: die SQLite-Datenbank, Benutzeravatare und das eigene Forumlogo. Diese Datei kannst du später auf einem anderen Hosting hochladen und das Forum im Administrationsbereich wiederherstellen.',
        'Archiwum zawiera' => 'Das Archiv enthält',
        'katalog' => 'Verzeichnis',
        'oraz katalog' => 'und Verzeichnis',
        'Przywracanie zastąpi aktualną bazę danych, avatary i logo zawartością przesłanej kopii. Po tej operacji odśwież stronę i zaloguj się danymi z przywróconej bazy.' => 'Die Wiederherstellung ersetzt die aktuelle Datenbank, Avatare und das Logo durch den Inhalt der hochgeladenen Sicherung. Aktualisiere danach die Seite und melde dich mit den Daten aus der wiederhergestellten Datenbank an.',
        'Kopia zapasowa została przywrócona. Forum korzysta teraz z danych z przesłanego archiwum.' => 'Die Sicherung wurde wiederhergestellt. Das Forum verwendet jetzt die Daten aus dem hochgeladenen Archiv.',
        'Podstawowe' => 'Grundlagen',
        'Ustawienia' => 'Einstellungen',
        'Administrator' => 'Administrator',
        'Najaktywniejsi' => 'Aktivste',
        'Moderacja' => 'Moderation',
        'Użytkownicy' => 'Benutzer',
        'Użytkownik' => 'Benutzer',
        'Zarządzanie ustawieniami, działami i aktywnością forum.' => 'Verwalte Foreneinstellungen, Bereiche und Aktivität.',
        'Zarządzanie ustawieniami, działami, użytkownikami i aktywnością forum.' => 'Verwalte Foreneinstellungen, Bereiche, Benutzer und Aktivität.',
        'Zgłoszenia postów i narzędzia porządkowe forum.' => 'Beitragsmeldungen und Moderationswerkzeuge des Forums.',
        'Podsumowanie forum' => 'Forenübersicht',
        'Zapisz ustawienia podstawowe' => 'Grundeinstellungen speichern',
        'Widok forum i rejestracja' => 'Forumansicht und Registrierung',
        'Panel administratora' => 'Administrationsbereich',
        'Panel admina' => 'Adminbereich',
        'Panel moderatora' => 'Moderationsbereich',
        'Szukaj' => 'Suchen',
        'Wyszukiwarka forum' => 'Forumsuche',
        'Szukaj tematów i postów opublikowanych na forum.' => 'Suche nach Themen und Beiträgen im Forum.',
        'Wpisz szukaną frazę, aby znaleźć tematy i posty na forum.' => 'Gib einen Suchbegriff ein, um Themen und Beiträge im Forum zu finden.',
        'Szukana fraza' => 'Suchbegriff',
        'Wpisz minimum 2 znaki' => 'Gib mindestens 2 Zeichen ein',
        'Wpisz frazę, aby rozpocząć wyszukiwanie.' => 'Gib einen Suchbegriff ein, um die Suche zu starten.',
        'Wyniki obejmują tytuły tematów oraz treść postów.' => 'Die Ergebnisse umfassen Thementitel und Beitragsinhalte.',
        'Fraza jest za krótka.' => 'Der Suchbegriff ist zu kurz.',
        'Wpisz co najmniej 2 znaki, aby przeszukać forum.' => 'Gib mindestens 2 Zeichen ein, um das Forum zu durchsuchen.',
        'Wyniki' => 'Ergebnisse',
        'Wyniki wyszukiwania' => 'Suchergebnisse',
        'Znaleziono' => 'Gefunden',
        'wyników dla:' => 'Ergebnisse für:',
        'Brak wyników.' => 'Keine Ergebnisse.',
        'Spróbuj użyć krótszej albo innej frazy.' => 'Versuche einen kürzeren oder anderen Suchbegriff.',
        'Post' => 'Beitrag',
        'Temat' => 'Thema',
        'Tytuł tematu pasuje do szukanej frazy.' => 'Der Thementitel passt zum Suchbegriff.',
        'Zgłoszenia postów' => 'Beitragsmeldungen',
        'Zarządzaj użytkownikami i moderatorami' => 'Benutzer und Moderatoren verwalten',
        'Użytkownicy z największą aktywnością' => 'Benutzer mit der größten Aktivität',
        'Zarządzaj kategoriami' => 'Kategorien verwalten',
        'Dodaj kategorię' => 'Kategorie hinzufügen',
        'Zarządzaj działami' => 'Forenbereiche verwalten',
        'Dodaj dział forum' => 'Forenbereich hinzufügen',
        'Nazwa forum' => 'Forumname',
        'Krótki opis forum' => 'Kurze Forenbeschreibung',
        'Własne logo forum' => 'Eigenes Forumlogo',
        'Styl graficzny forum' => 'Grafischer Stil des Forums',
        'Tytuł forum' => 'Forumtitel',
        'Opis forum' => 'Forenbeschreibung',
        'Pozwól użytkownikom zakładać nowe konta' => 'Benutzern erlauben, neue Konten zu erstellen',
        'Zapisz ustawienia' => 'Einstellungen speichern',
        'Nazwa kategorii' => 'Kategoriename',
        'Opis kategorii' => 'Kategoriebeschreibung',
        'Nazwa działu' => 'Name des Forenbereichs',
        'Opis działu' => 'Beschreibung des Forenbereichs',
        'Kategorie' => 'Kategorien',
        'Kategoria' => 'Kategorie',
        'Działy' => 'Forenbereiche',
        'Nowa kategoria' => 'Neue Kategorie',
        'Nowy dział' => 'Neuer Forenbereich',
        'Dodaj dział' => 'Forenbereich hinzufügen',
        'Zapisz kategorię' => 'Kategorie speichern',
        'Zapisz dział' => 'Forenbereich speichern',
        'Ustawienia forum zostały zapisane.' => 'Die Foreneinstellungen wurden gespeichert.',
        'Nowa kategoria forum została dodana.' => 'Eine neue Forenkategorie wurde hinzugefügt.',
        'Nowy dział został dodany.' => 'Ein neuer Forenbereich wurde hinzugefügt.',
        'Społeczność' => 'Gemeinschaft',
        'Forum' => 'Forum',
        'Menu' => 'Menü',
        'Zalogowany jako' => 'Angemeldet als',
        'Moje konto' => 'Mein Konto',
        'Ustawienia profilu, avatar, hasło i statystyki aktywności.' => 'Profileinstellungen, Avatar, Passwort und Aktivitätsstatistiken.',
        'Wiadomości' => 'Nachrichten',
        'Wyloguj' => 'Abmelden',
        'Dołącz do dyskusji' => 'An der Diskussion teilnehmen',
        'Załóż konto, aby pisać posty, wysyłać prywatne wiadomości i budować swój profil na forum.' => 'Erstelle ein Konto, um Beiträge zu schreiben, private Nachrichten zu senden und dein Profil im Forum aufzubauen.',
        'Załóż konto' => 'Konto erstellen',
        'Zaloguj się' => 'Anmelden',
        'Reset hasła' => 'Passwort zurücksetzen',
        'użytkowników' => 'Benutzer',
        'tematów' => 'Themen',
        'postów' => 'Beiträge',
        'lajków' => 'Likes',
        'wiadomości prywatnych' => 'private Nachrichten',
        'Najnowsze' => 'Neueste',
        'Ostatnio aktywne tematy' => 'Zuletzt aktive Themen',
        'Aktywni użytkownicy' => 'Aktive Benutzer',
        'Kto najczęściej pomaga' => 'Wer hilft am häufigsten',
        'autor:' => 'Autor:',
        'Avatar' => 'Avatar',
        'Nowy avatar' => 'Neuer Avatar',
        'Wyślij avatar' => 'Avatar hochladen',
        'Usuń avatar' => 'Avatar entfernen',
        'Hasło' => 'Passwort',
        'Aktualne hasło' => 'Aktuelles Passwort',
        'Nowe hasło' => 'Neues Passwort',
        'Zmień hasło' => 'Passwort ändern',
        'Moje tematy' => 'Meine Themen',
        'Moje posty' => 'Meine Beiträge',
        'Ostatnio założone' => 'Zuletzt erstellt',
        'Ostatnie odpowiedzi' => 'Letzte Antworten',
        'Ostatnie tematy' => 'Letzte Themen',
        'Ostatnie posty' => 'Letzte Beiträge',
        'otrzymanych lajków' => 'erhaltene Likes',
        'danych lajków' => 'vergebene Likes',
        'na forum od' => 'Mitglied seit',
        'Napisz wiadomość' => 'Nachricht schreiben',
        'Ostatnie logowanie:' => 'Letzte Anmeldung:',
        'jeszcze brak' => 'noch keine',
        'Nie ma aktywnych zgłoszeń do sprawdzenia.' => 'Es gibt keine aktiven Meldungen zur Prüfung.',
        'Zgłosił:' => 'Gemeldet von:',
        'Autor posta:' => 'Beitragsautor:',
        'Przejdź do posta' => 'Zum Beitrag gehen',
        'Zamknij spór' => 'Meldung schließen',
        'Powód:' => 'Grund:',
        'Kliknij nazwę użytkownika, żeby otworzyć jego kartę edycji, zmienić rolę albo wykonać operacje administracyjne.' => 'Klicke auf einen Benutzernamen, um die Bearbeitungskarte zu öffnen, die Rolle zu ändern oder administrative Aktionen auszuführen.',
        'Administratorzy i moderatorzy' => 'Administratoren und Moderatoren',
        'Zwykli użytkownicy' => 'Normale Benutzer',
        'Nie ma jeszcze zwykłych użytkowników.' => 'Es gibt noch keine normalen Benutzer.',
        'Wróć do listy' => 'Zurück zur Liste',
        'Profil publiczny' => 'Öffentliches Profil',
        'Nazwa użytkownika' => 'Benutzername',
        'Rola' => 'Rolle',
        'zostaw puste, jeśli bez zmian' => 'leer lassen, wenn unverändert',
        'To główne konto administratora forum. Możesz zmienić jego e-mail i hasło, ale nie nazwę ani rolę.' => 'Dies ist das Hauptkonto des Forumadministrators. Du kannst E-Mail und Passwort ändern, aber nicht Namen oder Rolle.',
        'Moderator może przypinać, zamykać i usuwać tematy oraz edytować posty podczas moderacji.' => 'Ein Moderator kann Themen anheften, schließen und löschen sowie Beiträge während der Moderation bearbeiten.',
        'Zapisz użytkownika' => 'Benutzer speichern',
        'Usuń całą aktywność' => 'Gesamte Aktivität löschen',
        'Ta operacja usuwa tematy, posty, lajki oraz prywatne wiadomości użytkownika. Samo konto zostaje na forum. Potwierdzenie przyjdzie na adres administratora.' => 'Diese Aktion löscht Themen, Beiträge, Likes und private Nachrichten des Benutzers. Das Konto selbst bleibt im Forum. Eine Bestätigung wird an die Administratoradresse gesendet.',
        'Usuń użytkownika' => 'Benutzer löschen',
        'Ta operacja usuwa konto użytkownika oraz jego dane z bazy forum. Nie zostawia profilu ani historii prywatnych wiadomości tego konta.' => 'Diese Aktion löscht das Benutzerkonto und seine Daten aus der Forendatenbank. Es bleiben kein Profil und kein Verlauf privater Nachrichten dieses Kontos erhalten.',
        'W tej kategorii nie ma jeszcze działów.' => 'In dieser Kategorie gibt es noch keine Forenbereiche.',
        'Wyżej w kategorii' => 'In der Kategorie nach oben',
        'Niżej w kategorii' => 'In der Kategorie nach unten',
        'Zapisz zmiany' => 'Änderungen speichern',
        'Wyżej' => 'Nach oben',
        'Niżej' => 'Nach unten',
        'Stronę zbudował' => 'Seite erstellt von',
        'Menu panelu administratora' => 'Menü des Administrationsbereichs',
        'Avatar użytkownika' => 'Benutzeravatar',
        'Wysłać wiadomość potwierdzającą usunięcie całej aktywności tego użytkownika?' => 'Eine Bestätigungsnachricht zum Löschen der gesamten Aktivität dieses Benutzers senden?',
        'Na pewno całkowicie usunąć tego użytkownika, jego aktywność, wiadomości, lajki, tokeny i avatar? Tej operacji nie da się cofnąć.' => 'Diesen Benutzer, seine Aktivität, Nachrichten, Likes, Tokens und den Avatar wirklich vollständig löschen? Diese Aktion kann nicht rückgängig gemacht werden.',
        'Przywracanie zastąpi aktualne dane forum. Kontynuować?' => 'Die Wiederherstellung ersetzt die aktuellen Forendaten. Fortfahren?',
        'Konto zostało utworzone. Możesz się teraz zalogować.' => 'Das Konto wurde erstellt. Du kannst dich jetzt anmelden.',
        'Zalogowano pomyślnie.' => 'Erfolgreich angemeldet.',
        'Zostałeś wylogowany.' => 'Du wurdest abgemeldet.',
        'Hasło zostało zmienione.' => 'Das Passwort wurde geändert.',
        'Avatar został zaktualizowany.' => 'Der Avatar wurde aktualisiert.',
        'Avatar został usunięty.' => 'Der Avatar wurde entfernt.',
        'Temat został utworzony.' => 'Das Thema wurde erstellt.',
        'Odpowiedź została dodana.' => 'Die Antwort wurde hinzugefügt.',
        'Post został zaktualizowany.' => 'Der Beitrag wurde aktualisiert.',
        'Post został zgłoszony moderatorom.' => 'Der Beitrag wurde den Moderatoren gemeldet.',
        'Wiadomość została wysłana.' => 'Die Nachricht wurde gesendet.',
        'Jeśli konto istnieje, wysłaliśmy link do zmiany hasła na powiązany adres e-mail.' => 'Wenn das Konto existiert, haben wir einen Link zum Ändern des Passworts an die verknüpfte E-Mail-Adresse gesendet.',
        'Hasło zostało ustawione. Możesz się teraz zalogować.' => 'Das Passwort wurde gesetzt. Du kannst dich jetzt anmelden.',
        'Kategoria forum została zaktualizowana.' => 'Die Forenkategorie wurde aktualisiert.',
        'Dział został zaktualizowany.' => 'Der Forenbereich wurde aktualisiert.',
        'Kolejność działów została zmieniona.' => 'Die Reihenfolge der Forenbereiche wurde geändert.',
        'Dane użytkownika zostały zaktualizowane.' => 'Die Benutzerdaten wurden aktualisiert.',
        'Wysłaliśmy wiadomość potwierdzającą na adres administratora. Dopiero po kliknięciu w link aktywność użytkownika zostanie usunięta.' => 'Wir haben eine Bestätigungsnachricht an die Administratoradresse gesendet. Die Benutzeraktivität wird erst nach dem Klick auf den Link gelöscht.',
        'Status tematu został zaktualizowany.' => 'Der Themenstatus wurde aktualisiert.',
        'Temat został usunięty.' => 'Das Thema wurde gelöscht.',
        'Zgłoszenie zostało zamknięte.' => 'Die Meldung wurde geschlossen.',
        'Zaloguj się, aby wykonać tę akcję.' => 'Melde dich an, um diese Aktion auszuführen.',
        'Ta sekcja jest dostepna tylko dla administratora.' => 'Dieser Bereich ist nur für den Administrator verfügbar.',
        'Ta sekcja jest dostepna tylko dla moderatora lub administratora.' => 'Dieser Bereich ist nur für Moderatoren oder Administratoren verfügbar.',
        'Rejestracja nowych kont jest chwilowo wyłączona.' => 'Die Registrierung neuer Konten ist vorübergehend deaktiviert.',
        'Nie znaleziono tematu.' => 'Thema nicht gefunden.',
        'Ten temat jest zamknięty.' => 'Dieses Thema ist geschlossen.',
        'Nie znaleziono wskazanego postu.' => 'Der ausgewählte Beitrag wurde nicht gefunden.',
        'Nie znaleziono wiadomości.' => 'Nachricht nicht gefunden.',
        'Aby założyć konto, musisz zaakceptować regulamin strony i forum.' => 'Um ein Konto zu erstellen, musst du die Regeln der Seite und des Forums akzeptieren.',
        'Nazwa użytkownika musi mieć 3-40 znaków i może zawierać litery, cyfry, kropki, myślniki oraz podkreślenia.' => 'Der Benutzername muss 3-40 Zeichen lang sein und darf Buchstaben, Zahlen, Punkte, Bindestriche und Unterstriche enthalten.',
        'Podaj poprawny adres e-mail.' => 'Gib eine gültige E-Mail-Adresse ein.',
        'Podaj poprawny adres e-mail użytkownika.' => 'Gib eine gültige E-Mail-Adresse des Benutzers ein.',
        'Hasło musi mieć co najmniej 10 znaków.' => 'Das Passwort muss mindestens 10 Zeichen lang sein.',
        'Nie udało się zalogować. Sprawdź login i hasło.' => 'Anmeldung fehlgeschlagen. Prüfe Login und Passwort.',
        'Aktualne hasło jest niepoprawne.' => 'Das aktuelle Passwort ist falsch.',
        'Nowe hasło musi mieć co najmniej 10 znaków.' => 'Das neue Passwort muss mindestens 10 Zeichen lang sein.',
        'Nie znaleziono użytkownika do edycji.' => 'Es wurde kein Benutzer zur Bearbeitung gefunden.',
        'Wybrana rola użytkownika jest nieprawidłowa.' => 'Die ausgewählte Benutzerrolle ist ungültig.',
        'Taki login albo adres e-mail jest juz zajety.' => 'Dieser Login oder diese E-Mail-Adresse ist bereits vergeben.',
        'Nowe hasło dla użytkownika musi mieć co najmniej 10 znaków.' => 'Das neue Passwort für den Benutzer muss mindestens 10 Zeichen lang sein.',
        'Nazwa kategorii forum nie może być pusta.' => 'Der Name der Forenkategorie darf nicht leer sein.',
        'Nazwa działu nie może być pusta.' => 'Der Name des Forenbereichs darf nicht leer sein.',
        'Najpierw przenieś lub usuń tematy z tego działu.' => 'Verschiebe oder lösche zuerst die Themen aus diesem Bereich.',
        'Nieznana flaga tematu.' => 'Unbekannte Themenmarkierung.',
        'Sesja formularza wygasła. Odśwież stronę i spróbuj ponownie.' => 'Die Formularsitzung ist abgelaufen. Aktualisiere die Seite und versuche es erneut.',
        'Wykonujesz te akcje zbyt szybko. Odczekaj chwilę i spróbuj ponownie.' => 'Du führst diese Aktionen zu schnell aus. Warte einen Moment und versuche es erneut.',
        'Logowanie' => 'Anmeldung',
        'Rejestracja' => 'Registrierung',
        'Login lub e-mail' => 'Login oder E-Mail',
        'Wyślij link do resetu' => 'Reset-Link senden',
        'Ustaw nowe hasło' => 'Neues Passwort festlegen',
        'Nie pamiętasz hasła?' => 'Passwort vergessen?',
        'Przywróć dostęp do forum przez link wysłany e-mailem.' => 'Stelle den Zugang zum Forum über einen per E-Mail gesendeten Link wieder her.',
        'Załóż konto na ForumForgeCMS.' => 'Erstelle ein Konto bei ForumForgeCMS.',
        'Zaloguj sie do ForumForgeCMS.' => 'Melde dich bei ForumForgeCMS an.',
        'Ustaw nowe hasło do ForumForgeCMS.' => 'Lege ein neues Passwort für ForumForgeCMS fest.',
        'Dział:' => 'Bereich:',
        'Autor:' => 'Autor:',
        'Założono:' => 'Erstellt:',
        'Wróć do działu' => 'Zurück zum Bereich',
        'Wróć do listy działów' => 'Zurück zur Bereichsliste',
        'Edytuj' => 'Bearbeiten',
        'Cytuj' => 'Zitieren',
        'Zgłoś' => 'Melden',
        'Ostatnio edytowano przez:' => 'Zuletzt bearbeitet von:',
        'Start i organizacja' => 'Start und Organisation',
        'Ogłoszenia, zasady, pierwsze pytania i sprawy techniczne związane z działaniem forum.' => 'Ankündigungen, Regeln, erste Fragen und technische Themen rund um den Betrieb des Forums.',
        'Społeczność i rozmowy' => 'Gemeinschaft und Gespräche',
        'Dyskusje, pomysły, prezentacje projektów i luźniejsze tematy budujące życie forum.' => 'Diskussionen, Ideen, Projektvorstellungen und lockere Themen, die das Forenleben gestalten.',
        'Ogłoszenia i aktualności' => 'Ankündigungen und Neuigkeiten',
        'Ważne informacje od administracji, zmiany w forum, komunikaty techniczne i zapowiedzi kolejnych usprawnień.' => 'Wichtige Informationen der Administration, Änderungen im Forum, technische Hinweise und kommende Verbesserungen.',
        'Pierwsze kroki' => 'Erste Schritte',
        'Dział dla nowych użytkowników: przedstaw się, zapytaj jak zacząć i spokojnie poznaj zasady działania forum.' => 'Bereich für neue Benutzer: Stelle dich vor, frage nach dem Einstieg und lerne in Ruhe die Forenregeln kennen.',
        'Pomoc i pytania techniczne' => 'Hilfe und technische Fragen',
        'Regulamin i zasady' => 'Regeln und Richtlinien',
        'Pomysły i sugestie' => 'Ideen und Vorschläge',
        'Nowości na forum' => 'Neuigkeiten im Forum',
        'Rozmowy ogólne' => 'Allgemeine Gespräche',
        'Projekty i inspiracje' => 'Projekte und Inspiration',
        'Witamy w ForumForgeCMS' => 'Willkommen bei ForumForgeCMS',
        'Jak korzystać z ogłoszeń' => 'So nutzt man Ankündigungen',
        'Pierwsze rzeczy po instalacji' => 'Erste Schritte nach der Installation',
        'Zasady publikowania komunikatów' => 'Regeln für Ankündigungen',
        'Przewodnik dla nowych użytkowników' => 'Leitfaden für neue Benutzer',
        'To jest pierwszy komunikat administracyjny na forum.' => 'Dies ist die erste administrative Ankündigung im Forum.',
        'ForumForgeCMS zostało uruchomione i jest gotowe do konfiguracji. Administrator może teraz dopasować kategorie, działy, opis strony, regulamin oraz ustawienia rejestracji do swojej społeczności.' => 'ForumForgeCMS wurde gestartet und ist bereit zur Konfiguration. Der Administrator kann nun Kategorien, Forenbereiche, Seitenbeschreibung, Regeln und Registrierungseinstellungen an die Gemeinschaft anpassen.',
        'Ten dział służy do publikowania ważnych informacji od administracji.' => 'Dieser Bereich dient zur Veröffentlichung wichtiger Informationen der Administration.',
        'Warto umieszczać tutaj komunikaty o zmianach w forum, planowanych pracach technicznych, nowych funkcjach oraz zasadach, które powinni znać wszyscy użytkownicy.' => 'Hier sollten Hinweise zu Änderungen im Forum, geplanten technischen Arbeiten, neuen Funktionen und Regeln veröffentlicht werden, die alle Benutzer kennen sollten.',
        'Po pierwszym uruchomieniu forum zalecamy wykonać kilka kroków:' => 'Nach dem ersten Start des Forums empfehlen wir einige Schritte:',
        'Zmień hasło administratora.' => 'Ändere das Administratorpasswort.',
        'Uzupełnij adres e-mail konta administratora.' => 'Ergänze die E-Mail-Adresse des Administratorkontos.',
        'Przejrzyj domyślne kategorie i działy.' => 'Prüfe die Standardkategorien und Forenbereiche.',
        'Dopasuj opis forum do swojej społeczności.' => 'Passe die Forenbeschreibung an deine Gemeinschaft an.',
        'Sprawdź, czy katalog forum-data jest niedostępny z przeglądarki.' => 'Prüfe, ob das Verzeichnis forum-data im Browser nicht erreichbar ist.',
        'Komunikaty administracyjne powinny być krótkie, jasne i konkretne.' => 'Administrative Ankündigungen sollten kurz, klar und konkret sein.',
        'Jeśli informacja dotyczy wszystkich użytkowników, warto ją przypiąć. Jeśli jest tymczasowa, można ją później odpiąć albo przenieść do odpowiedniego działu.' => 'Wenn die Information alle Benutzer betrifft, sollte sie angeheftet werden. Ist sie nur vorübergehend, kann sie später gelöst oder in den passenden Bereich verschoben werden.',
        'Witaj na forum.' => 'Willkommen im Forum.',
        'Jeśli jesteś tu pierwszy raz, zacznij od założenia konta, przeczytania regulaminu i krótkiego rozejrzenia się po działach. Gdy zadajesz pytanie, opisz dokładnie sytuację, dodaj ważne szczegóły i wybierz dział, który najlepiej pasuje do tematu.' => 'Wenn du zum ersten Mal hier bist, erstelle ein Konto, lies die Regeln und sieh dich kurz in den Forenbereichen um. Wenn du eine Frage stellst, beschreibe die Situation genau, füge wichtige Details hinzu und wähle den passenden Bereich.',
        'Lekka przestrzeń do rozmowy, wymiany wiedzy i budowania społeczności wokół dowolnego tematu. ForumForgeCMS daje prosty start, czytelne działy i spokojne miejsce na dyskusje, które z czasem może urosnąć razem z użytkownikami.' => 'Ein leichter Raum für Gespräche, Wissensaustausch und den Aufbau einer Gemeinschaft rund um jedes Thema. ForumForgeCMS bietet einen einfachen Start, klare Bereiche und einen ruhigen Ort für Diskussionen, der mit den Benutzern wachsen kann.',
        'samodzielne forum dla Twojej społeczności' => 'ein eigenständiges Forum für deine Gemeinschaft',
    ];

    return $language === 'en' ? $commonEn : ($language === 'de' ? $commonDe : []);
}

function forum_ext_translate_html(string $html): string
{
    $map = forum_ext_translation_map(forum_ext_current_language());
    if ($map === []) {
        return $html;
    }

    uksort($map, static fn(string $a, string $b): int => strlen($b) <=> strlen($a));
    $parts = preg_split('/(<[^>]+>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
    if ($parts === false) {
        return strtr($html, $map);
    }

    $translated = '';
    $skipText = false;
    foreach ($parts as $part) {
        if ($part === '') {
            continue;
        }

        if ($part[0] === '<') {
            $tag = strtolower($part);
            if (preg_match('/^<\s*(textarea|script|style)\b/', $tag)) {
                $skipText = true;
            } elseif (preg_match('/^<\s*\/\s*(textarea|script|style)\s*>/', $tag)) {
                $skipText = false;
            }
            $part = forum_ext_translate_html_attributes($part, $map);
            $translated .= $part;
            continue;
        }

        $translated .= $skipText ? $part : strtr($part, $map);
    }

    return $translated;
}

function forum_ext_translate_html_attributes(string $tag, array $map): string
{
    return (string) preg_replace_callback(
        '/\b(aria-label|alt|placeholder|title|onsubmit)="([^"]*)"/u',
        static function (array $matches) use ($map): string {
            $value = html_entity_decode((string) $matches[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $translated = strtr($value, $map);

            return $matches[1] . '="' . htmlspecialchars($translated, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"';
        },
        $tag
    );
}
