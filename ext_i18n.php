<?php
declare(strict_types=1);

function forum_ext_current_language(): string
{
    $language = forum_ext_setting('forum_language');
    $language = array_key_exists($language, forum_ext_languages()) ? $language : 'en';
    $GLOBALS['forum_i18n_language'] = $language;
    return $language;
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

function forum_dictionary(string $language): array
{
    static $catalogs = [];
    $language = in_array($language, ['pl', 'en', 'de'], true) ? $language : 'en';
    return $catalogs[$language] ??= require __DIR__ . '/locales/' . $language . '.php';
}

function forum_t(string $key, array $parameters = [], ?string $language = null): string
{
    // No database access here: errors during first-run setup must also be translatable.
    $language = $language ?? ($GLOBALS['forum_ext_settings_cache']['forum_language'] ?? $GLOBALS['forum_i18n_language'] ?? 'en');
    $catalog = forum_dictionary($language);
    $text = $catalog[$key] ?? forum_dictionary('en')[$key] ?? $key;
    $replacements = [];
    foreach ($parameters as $name => $value) {
        $replacements['{' . $name . '}'] = (string) $value;
    }
    return strtr($text, $replacements);
}
