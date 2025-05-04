<?php
require_once __DIR__ . '/../models/Score.php';
require_once __DIR__ . '/../services/GameService.php';

class GameController
{
    private $gameService;
    private $scoreModel;

    /**
     * Konstruktør for GameController
     * Initialiserer nødvendige modeller og tjenester
     */
    public function __construct()
    {
        $pdo = require __DIR__ . '/../../db.php';
        $this->scoreModel = new Score($pdo);
        $this->gameService = new GameService();
    }

    /**
     * Viser spilsiden
     * Kontrollerer session og initialiserer spilstand hvis nødvendigt
     */
    public function index()
    {
        $this->checkSession();

        // Hvis spilstand ikke er initialiseret, start et nyt spil
        if (!isset($_SESSION['current_event']) || !isset($_SESSION['next_event'])) {
            $this->initializeGame();
        }

        require_once __DIR__ . '/../views/game.php';
    }

    /**
     * Starter et nyt spil
     * Nulstiller spilstand og henter nye begivenheder
     */
    public function start()
    {
        $this->checkSession();
        $this->initializeGame();

        header('Location: /game');
        exit;
    }

    /**
     * Behandler et gæt fra spilleren
     * Håndterer AJAX-kald fra frontend
     */
    public function guess()
    {
        $this->checkSession();

        $guess = $_POST['guess'] ?? null;

        if (!$guess || !in_array($guess, ['before', 'after'])) {
            $this->respondWithError('Ugyldigt gæt');
        }

        $currentEvent = $_SESSION['current_event'];
        $nextEvent = $_SESSION['next_event'];

        $isCorrect = $this->checkGuess($guess, $currentEvent, $nextEvent);

        if ($isCorrect) {
            $_SESSION['score']++;

            try {
                // Sæt nuværende begivenhed til den næste begivenhed
                $_SESSION['current_event'] = $nextEvent;
                // Hent en ny næste begivenhed, ekskluder det nuværende år
                $_SESSION['next_event'] = $this->gameService->getRandomHistoricalEvent($_SESSION['current_event']['year']);

                $this->respondWithSuccess(true, $_SESSION['score'], $_SESSION['current_event'], $_SESSION['next_event']);
            } catch (\Exception $e) {
                error_log("API Fejl under spil: " . $e->getMessage());
                $this->respondWithError('Der opstod en fejl. Prøv igen.');
            }
        } else {
            // Spil slut, gem score
            $finalScore = $_SESSION['score'];
            $this->scoreModel->saveScore($_SESSION['user_id'], $finalScore);

            $this->respondWithSuccess(false, $finalScore, $currentEvent, $nextEvent,
                "Spillet er slut! Din score: $finalScore");
        }
    }

    /**
     * Viser topscorerlisten
     */
    public function leaderboard()
    {
        $this->checkSession();
        require_once __DIR__ . '/../views/leaderboard.php';
    }

    /**
     * Kontrollerer om brugeren er logget ind, omdirigerer hvis ikke
     */
    private function checkSession()
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: /');
            exit;
        }
    }

    /**
     * Initialiserer et nyt spil
     * Nulstiller score og henter nye begivenheder
     */
    private function initializeGame()
    {
        try {
            // Nulstil spilstand
            $_SESSION['score'] = 0;
            $_SESSION['current_event'] = $this->gameService->getRandomHistoricalEvent();
            $_SESSION['next_event'] = $this->gameService->getRandomHistoricalEvent($_SESSION['current_event']['year']);
        } catch (\Exception $e) {
            // Log fejlen
            error_log("Spil initialisering fejl: " . $e->getMessage());

            // Sæt fallback begivenheder hvis API fejler
            $_SESSION['score'] = 0;
            $_SESSION['current_event'] = [
                'id' => 'fallback1',
                'title' => 'Anden Verdenskrig slutter',
                'year' => 1945,
                'description' => 'Denne begivenhed fandt sted den maj 8, 1945.'
            ];
            $_SESSION['next_event'] = [
                'id' => 'fallback2',
                'title' => 'Berlinmuren falder',
                'year' => 1989,
                'description' => 'Denne begivenhed fandt sted den november 9, 1989.'
            ];
        }
    }

    /**
     * Kontrollerer om et gæt er korrekt
     *
     * @param string $guess 'before' eller 'after'
     * @param array $currentEvent Nuværende begivenhed
     * @param array $nextEvent Næste begivenhed
     * @return bool True hvis gættet er korrekt
     */
    private function checkGuess($guess, $currentEvent, $nextEvent)
    {
        if ($guess === 'before' && $nextEvent['year'] < $currentEvent['year']) {
            return true;
        } elseif ($guess === 'after' && $nextEvent['year'] > $currentEvent['year']) {
            return true;
        }
        return false;
    }

    /**
     * Sender et success-svar til frontend
     */
    private function respondWithSuccess($correct, $score, $currentEvent, $nextEvent, $message = '')
    {
        echo json_encode([
            'correct' => $correct,
            'score' => $score,
            'currentEvent' => $currentEvent,
            'nextEvent' => $nextEvent,
            'message' => $message
        ]);
        exit;
    }

    /**
     * Sender et fejl-svar til frontend
     */
    private function respondWithError($message)
    {
        http_response_code(400);
        echo json_encode(['error' => $message]);
        exit;
    }
}
