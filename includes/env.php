<?php
/**
 * Minimal .env loader — avoids needing an extra composer package just for this.
 * Loads KEY=VALUE lines from .env into getenv()/$_ENV.
 */
function load_env(string $path): void
{
    static $loaded = false;
    if ($loaded) return;

    if (!file_exists($path)) {
        // Fall back silently; real server env vars (e.g. set via EC2/systemd) still work.
        $loaded = true;
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (!str_contains($line, '=')) continue;

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        // strip optional surrounding quotes
        $value = trim($value, "\"'");

        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
    $loaded = true;
}

load_env(__DIR__ . '/../.env');

function env(string $key, $default = null)
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}
