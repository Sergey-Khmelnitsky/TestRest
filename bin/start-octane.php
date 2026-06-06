<?php

use Symfony\Component\Process\Process;

require __DIR__.'/../vendor/autoload.php';

$binary = PHP_OS_FAMILY === 'Windows'
    ? __DIR__.'/../rr.exe'
    : __DIR__.'/../rr';

if (! is_file($binary)) {
    fwrite(STDERR, 'RoadRunner binary not found. Run: php artisan octane:install --server=roadrunner'.PHP_EOL);
    exit(1);
}

$process = new Process(
    [$binary, 'serve', '-c', __DIR__.'/../.rr.yaml'],
    dirname(__DIR__),
    [
        'APP_ENV' => getenv('APP_ENV') ?: 'local',
        'APP_BASE_PATH' => dirname(__DIR__),
        'LARAVEL_OCTANE' => '1',
    ],
);

$process->setTimeout(null);
$process->start(function ($type, $buffer) {
    echo $buffer;
});

$process->wait();

exit($process->getExitCode() ?? 1);
