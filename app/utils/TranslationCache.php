<?php
class TranslationCache
{
    private static $cacheFile;
    private static $cache = [];
    private static $modified = false;

    /**
     * Initialiserer cache systemet
     * Opretter cache-mappe hvis ikke eksisterer og indlæser eksisterende cache
     */
    public static function init()
    {
        self::$cacheFile = __DIR__ . '/../../cache/translations.json';

        // Opret cache-mappen hvis den ikke findes
        if (!file_exists(dirname(self::$cacheFile))) {
            mkdir(dirname(self::$cacheFile), 0755, true);
        }

        // Indlæs cache fra fil
        if (file_exists(self::$cacheFile)) {
            $content = file_get_contents(self::$cacheFile);
            if ($content) {
                self::$cache = json_decode($content, true) ?: [];
            }
        }

        // Registrer nedlukningsfunktion til at gemme cache
        register_shutdown_function([self::class, 'save']);
    }

    /**
     * Henter en oversættelse fra cachen
     *
     * @param string $text Originaltekst
     * @return string|null Oversat tekst eller null hvis ikke i cache
     */
    public static function get($text)
    {
        $key = md5($text);
        return self::$cache[$key]['translation'] ?? null;
    }

    /**
     * Gemmer en oversættelse i cachen
     *
     * @param string $text Originaltekst
     * @param string $translation Oversat tekst
     */
    public static function set($text, $translation)
    {
        $key = md5($text);
        self::$cache[$key] = [
            'original' => $text,
            'translation' => $translation,
            'timestamp' => time()
        ];
        self::$modified = true;

        // Gem med det samme for at undgå tab af oversættelser hvis scriptet crasher
        self::save();
    }

    /**
     * Gemmer cache til fil
     */
    public static function save()
    {
        if (self::$modified && !empty(self::$cache)) {
            file_put_contents(self::$cacheFile, json_encode(self::$cache, JSON_PRETTY_PRINT));
            self::$modified = false;
        }
    }
}

// Initialiser cache
TranslationCache::init();
