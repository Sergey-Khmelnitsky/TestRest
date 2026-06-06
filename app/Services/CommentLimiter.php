<?php

namespace App\Services;

class CommentLimiter
{
    private const WINDOW_SECONDS = 10;

    private const MAX_POSTS = 3;

    private const GLOBAL_CLEANUP_INTERVAL_SECONDS = 60;

    /** @var array<int, list<float>> */
    private static array $timestampsByUser = [];

    private static float $lastGlobalCleanupAt = 0.0;

    public function canPost(int $userId): bool
    {
        $now = microtime(true);

        $this->maybeRunGlobalCleanup($now);

        $timestamps = $this->getRecentTimestamps($userId, $now);

        if (count($timestamps) >= self::MAX_POSTS) {
            self::$timestampsByUser[$userId] = $timestamps;

            return false;
        }

        $timestamps[] = $now;
        self::$timestampsByUser[$userId] = $timestamps;

        return true;
    }

    public function getRecentPostCount(int $userId): int
    {
        $now = microtime(true);

        return count($this->getRecentTimestamps($userId, $now));
    }

    public static function resetState(): void
    {
        self::$timestampsByUser = [];
        self::$lastGlobalCleanupAt = 0.0;
    }

    /**
     * @return list<float>
     */
    private function getRecentTimestamps(int $userId, float $now): array
    {
        $windowStart = $now - self::WINDOW_SECONDS;
        $timestamps = self::$timestampsByUser[$userId] ?? [];

        return array_values(array_filter(
            $timestamps,
            static fn (float $timestamp): bool => $timestamp >= $windowStart
        ));
    }

    private function maybeRunGlobalCleanup(float $now): void
    {
        if ($now - self::$lastGlobalCleanupAt < self::GLOBAL_CLEANUP_INTERVAL_SECONDS) {
            return;
        }

        $this->runGlobalCleanup($now);
        self::$lastGlobalCleanupAt = $now;
    }

    private function runGlobalCleanup(float $now): void
    {
        $windowStart = $now - self::WINDOW_SECONDS;

        foreach (self::$timestampsByUser as $userId => $timestamps) {
            $timestamps = array_values(array_filter(
                $timestamps,
                static fn (float $timestamp): bool => $timestamp >= $windowStart
            ));

            if ($timestamps === []) {
                unset(self::$timestampsByUser[$userId]);
            } else {
                self::$timestampsByUser[$userId] = $timestamps;
            }
        }
    }
}
