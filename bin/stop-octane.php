<?php

if (PHP_OS_FAMILY === 'Windows') {
    exec('taskkill /IM rr.exe /F 2>nul', $output, $exitCode);
} else {
    exec('pkill -f "rr serve" 2>/dev/null', $output, $exitCode);
}

echo 'RoadRunner stopped (if it was running).'.PHP_EOL;
