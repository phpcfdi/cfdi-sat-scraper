<?php

declare(strict_types=1);

// report all errors
error_reporting(-1);

// require composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// load dotenv file
call_user_func(new class() {
    public function __invoke(): void
    {
        $environmentFile = __DIR__ . '/.env';
        if (! $this->environmentFileExists($environmentFile)) {
            return;
        }
        $dotDevUsePutenv = true;
        (new Symfony\Component\Dotenv\Dotenv($dotDevUsePutenv))->load($environmentFile);
    }

    public function environmentFileExists(string $environmentFile): bool
    {
        return file_exists($environmentFile) && ! is_dir($environmentFile) && is_readable($environmentFile);
    }
});
