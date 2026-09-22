<?php

namespace App\Core;

/**
 * ModuleManager — single source of truth for which modules are active.
 * Results are cached per request (static property) to avoid repeated DB hits.
 */
class ModuleManager
{
    private static ?array $all     = null;
    private static ?array $enabled = null;

    /** All registered modules (enabled and disabled). */
    public static function all(): array
    {
        if (self::$all === null) {
            try {
                $db        = Database::getInstance();
                self::$all = $db->query('SELECT * FROM modules ORDER BY sort_order')->fetchAll();
            } catch (\Throwable) {
                self::$all = [];
            }
        }
        return self::$all;
    }

    /** Names of currently enabled modules, e.g. ['invoicing', 'it_planning']. */
    public static function enabled(): array
    {
        if (self::$enabled === null) {
            self::$enabled = array_column(
                array_filter(self::all(), fn($m) => (bool) $m['enabled']),
                'name'
            );
        }
        return self::$enabled;
    }

    /** Full rows for enabled modules (used by sidebar). */
    public static function enabledModules(): array
    {
        return array_filter(self::all(), fn($m) => (bool) $m['enabled']);
    }

    public static function isEnabled(string $name): bool
    {
        return in_array($name, self::enabled(), true);
    }

    /** Toggle a module on or off. Busts the static cache. */
    public static function toggle(string $name, bool $enable): void
    {
        $db = Database::getInstance();
        $db->prepare('UPDATE modules SET enabled = ? WHERE name = ?')
           ->execute([(int) $enable, $name]);

        // Bust cache so next call reflects the change
        self::$all     = null;
        self::$enabled = null;
    }
}
