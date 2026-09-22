<?php

namespace App\Core;

class Lang
{
    private static string $locale  = 'en-US';
    private static array  $strings = [];
    private static array  $loaded  = [];

    // Maps official BCP 47 codes → lang folder names
    private static array $folderMap = [
        'en-US' => 'en',
        'de-CH' => 'de_CH',
    ];

    public static function boot(): void
    {
        $stored = $_SESSION['locale'] ?? 'en-US';
        // Migrate old internal codes to official codes
        if ($stored === 'en')    $stored = 'en-US';
        if ($stored === 'de_CH') $stored = 'de-CH';
        self::$locale = $stored;
        self::load(self::$locale);
    }

    public static function setLocale(string $locale): void
    {
        // Accept both official codes and legacy internal codes
        $map = ['en' => 'en-US', 'de_CH' => 'de-CH'];
        if (isset($map[$locale])) $locale = $map[$locale];

        if (!array_key_exists($locale, self::$folderMap)) $locale = 'en-US';

        $_SESSION['locale'] = $locale;
        self::$locale       = $locale;
        self::$strings      = [];
        self::$loaded       = [];
        self::load($locale);
    }

    public static function getLocale(): string { return self::$locale; }

    public static function get(string $key, array $replace = []): string
    {
        $text = self::$strings[$key] ?? $key;
        foreach ($replace as $placeholder => $value) {
            $text = str_replace(':' . $placeholder, $value, $text);
        }
        return $text;
    }

    private static function load(string $locale): void
    {
        if (isset(self::$loaded[$locale])) return;
        $folder = self::$folderMap[$locale] ?? 'en';
        $file   = ROOT_PATH . '/lang/' . $folder . '/app.php';
        if (file_exists($file)) {
            self::$strings = array_merge(self::$strings, require $file);
        }
        self::$loaded[$locale] = true;
    }

    public static function supportedLocales(): array
    {
        return [
            'en-US' => 'English',
            'de-CH' => 'Schweizerdeutsch',
        ];
    }
}
