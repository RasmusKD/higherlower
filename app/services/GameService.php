<?php
require_once __DIR__ . '/../utils/TranslationCache.php';

class GameService
{
    private $apiKey;
    // Søgeord der kan bruges til at finde forskellige typer historiske begivenheder
    private $searchTerms = [
        'invention', 'war', 'discovery', 'revolution', 'independence',
        'treaty', 'battle', 'assassination', 'coronation', 'disaster',
        'space', 'scientific', 'political', 'cultural', 'sports', 'programming',
        'computer', 'internet', 'president', 'music', 'art', 'film',
        'technology', 'medicine', 'religion', 'aircraft', 'democracy',
        'exploration', 'olympics', 'pandemic', 'communication'
    ];

    // Bruges til at undgå gentagelser af begivenheder i samme spilsession
    private $usedEventIds = [];

    public function __construct()
    {
        // Hent API-nøgle fra miljøvariable
        $this->apiKey = $_ENV['API_NINJAS_KEY'];
    }

    /**
     * Henter en tilfældig historisk begivenhed
     *
     * @param int|null $excludeYear År der skal undgås (typisk det nuværende års begivenhed)
     * @return array Formateret begivenhedsinformation
     */
    public function getRandomHistoricalEvent($excludeYear = null)
    {
        // Undgå uendelig rekursion ved at begrænse antallet af forsøg
        static $attempts = 0;
        if ($attempts > 10) {
            $attempts = 0;
            throw new \Exception("Kunne ikke hente en gyldig historisk begivenhed efter flere forsøg");
        }
        $attempts++;

        // Prøv med tilfældigt søgeord for variation (1/3 chance)
        if (rand(0, 2) === 0 && !empty($this->searchTerms)) {
            $randomTerm = $this->searchTerms[array_rand($this->searchTerms)];
            $events = $this->fetchHistoricalEvents(null, $randomTerm);

            if (!empty($events) && !isset($events['error'])) {
                $event = $this->selectValidEvent($events, $excludeYear);
                if ($event) {
                    $attempts = 0;
                    return $this->formatEvent($event);
                }
            }
        }

        // Prøv med et tilfældigt år mellem 1700 og 2021
        $minYear = 1700;
        $maxYear = 2021;

        do {
            $year = rand($minYear, $maxYear);
        } while ($excludeYear !== null && $year === $excludeYear);

        $events = $this->fetchHistoricalEvents($year);

        if (empty($events) || isset($events['error'])) {
            error_log("API Fejl for år $year: " . json_encode($events));
            return $this->getRandomHistoricalEvent($excludeYear);
        }

        $event = $this->selectValidEvent($events, $excludeYear);

        if (!$event) {
            return $this->getRandomHistoricalEvent($excludeYear);
        }

        // Succes - nulstil forsøgstæller
        $attempts = 0;

        return $this->formatEvent($event);
    }

    /**
     * Vælger en gyldig begivenhed fra en liste af begivenheder
     *
     * @param array $events Liste af begivenheder
     * @param int|null $excludeYear År der skal undgås
     * @return array|null En gyldig begivenhed eller null hvis ingen findes
     */
    private function selectValidEvent($events, $excludeYear = null)
    {
        // Bland begivenhederne for at få variation
        shuffle($events);

        foreach ($events as $event) {
            // Spring over hvis året matcher det ekskluderede år
            if ($excludeYear !== null && (int)$event['year'] === (int)$excludeYear) {
                continue;
            }

            // Undgå gentagelser i samme spilsession
            $eventId = md5($event['event'] . $event['year']);
            if (in_array($eventId, $this->usedEventIds)) {
                continue;
            }

            // Undgå begivenheder hvor årstallet er i titlen
            if ($this->titleContainsYear($event['event'])) {
                continue;
            }

            // Tilføj til brugte begivenheder og begræns listen til 100 elementer
            $this->usedEventIds[] = $eventId;
            if (count($this->usedEventIds) > 100) {
                array_shift($this->usedEventIds);
            }

            return $event;
        }

        return null;
    }

    /**
     * Tjekker om en begivenhedstittel indeholder et årstal
     *
     * @param string $title Begivenhedstitlen
     * @return bool True hvis titlen indeholder et årstal
     */
    private function titleContainsYear($title)
    {
        return preg_match('/\b(1\d{3}|20[0-9]{2})\b/', $title) === 1;
    }

    /**
     * Formaterer en begivenhed til brug i spillet
     *
     * @param array $event Rå begivenhedsdata
     * @return array Formateret begivenhed med dansk oversættelse
     */
    private function formatEvent($event)
    {
        // Oversæt begivenhedstitlen
        $translatedTitle = $this->translateText($event['event']);

        return [
            'id' => md5($event['event'] . $event['year']),
            'title' => $translatedTitle,
            'year' => $event['year'],
            'description' => sprintf(
                "Denne begivenhed fandt sted den %s %d, %d.",
                $this->getMonthName($event['month'] ?? 1),
                $event['day'] ?? 1,
                $event['year']
            )
        ];
    }

    /**
     * Oversætter tekst fra engelsk til dansk
     *
     * @param string $text Tekst der skal oversættes
     * @return string Oversat tekst eller originaltekst hvis oversættelse fejler
     */
    private function translateText($text)
    {
        // Tjek cache først
        $cached = TranslationCache::get($text);
        if ($cached !== null) {
            return $cached;
        }

        // Udfør oversættelse med fejlhåndtering
        try {
            // Oversæt med MyMemory API
            $translatedText = $this->translateWithApi($text);

            // Hvis vi har en oversættelse, gem den i cachen
            if ($translatedText && $translatedText !== $text) {
                TranslationCache::set($text, $translatedText);
                return $translatedText;
            }

            // Hvis oversættelse fejler, returner originalteksten
            return $text;

        } catch (\Exception $e) {
            error_log("Oversættelsesfejl: " . $e->getMessage());
            // Returner originaltekst hvis oversættelse fejler
            return $text;
        }
    }

    /**
     * Oversætter tekst ved hjælp af MyMemory API
     *
     * @param string $text Tekst der skal oversættes
     * @return string|null Oversat tekst eller null ved fejl
     */
    private function translateWithApi($text)
    {
        // Bruger MyMemory Translation API (gratis)
        $url = "https://api.mymemory.translated.net/get";
        $params = [
            'q' => $text,
            'langpair' => 'en|da'
        ];

        $url .= '?' . http_build_query($params);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (isset($data['responseData']['translatedText'])) {
                return html_entity_decode($data['responseData']['translatedText']);
            }
        }

        return null;
    }

    /**
     * Henter historiske begivenheder fra API Ninjas
     *
     * @param int|null $year Specifikt år at hente begivenheder for
     * @param string|null $text Tekst at søge efter i begivenheder
     * @return array Begivenheder hentet fra API
     */
    private function fetchHistoricalEvents($year = null, $text = null)
    {
        $url = 'https://api.api-ninjas.com/v1/historicalevents';
        $params = [];

        if ($year) {
            $params['year'] = $year;
        }

        if ($text) {
            $params['text'] = $text;
        }

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Api-Key: ' . $this->apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("API Fejl: Kode $httpCode, Fejl: $error, Svar: $response");
            return ['error' => 'API-anmodning mislykkedes med kode ' . $httpCode . ' - ' . $error];
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON Afkodningsfejl: " . json_last_error_msg() . ", Svar: $response");
            return ['error' => 'Kunne ikke forstå API-svaret'];
        }

        return $decoded;
    }

    /**
     * Konverterer månedsnummer til dansk månedsnavn
     *
     * @param int $month Månedsnummer (1-12)
     * @return string Dansk månedsnavn
     */
    private function getMonthName($month)
    {
        $months = [
            1 => 'januar', 2 => 'februar', 3 => 'marts', 4 => 'april',
            5 => 'maj', 6 => 'juni', 7 => 'juli', 8 => 'august',
            9 => 'september', 10 => 'oktober', 11 => 'november', 12 => 'december'
        ];

        return $months[$month] ?? 'januar';
    }
}
