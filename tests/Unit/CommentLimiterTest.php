<?php

namespace Tests\Unit;

use App\Services\CommentLimiter;
use ReflectionClass;
use Tests\TestCase;

class CommentLimiterTest extends TestCase
{
    public function test_allows_up_to_three_posts_within_window(): void
    {
        $limiter = new CommentLimiter;

        $this->assertTrue($limiter->canPost(1));
        $this->assertTrue($limiter->canPost(1));
        $this->assertTrue($limiter->canPost(1));
        $this->assertFalse($limiter->canPost(1));
    }

    public function test_limits_are_independent_per_user(): void
    {
        $limiter = new CommentLimiter;

        $this->assertTrue($limiter->canPost(1));
        $this->assertTrue($limiter->canPost(1));
        $this->assertTrue($limiter->canPost(1));
        $this->assertFalse($limiter->canPost(1));

        $this->assertTrue($limiter->canPost(2));
    }

    public function test_allows_post_after_window_expires(): void
    {
        $limiter = new CommentLimiter;

        $this->assertTrue($limiter->canPost(1));
        $this->assertTrue($limiter->canPost(1));
        $this->assertTrue($limiter->canPost(1));
        $this->assertFalse($limiter->canPost(1));

        sleep(11);

        $this->assertTrue($limiter->canPost(1));
    }

    public function test_does_not_record_on_rejection(): void
    {
        $limiter = new CommentLimiter;

        $this->assertTrue($limiter->canPost(1));
        $this->assertTrue($limiter->canPost(1));
        $this->assertTrue($limiter->canPost(1));
        $this->assertFalse($limiter->canPost(1));
        $this->assertFalse($limiter->canPost(1));

        sleep(11);

        $this->assertTrue($limiter->canPost(1));
        $this->assertTrue($limiter->canPost(1));
        $this->assertTrue($limiter->canPost(1));
        $this->assertFalse($limiter->canPost(1));
    }

    public function test_global_cleanup_removes_stale_users(): void
    {
        $limiter = new CommentLimiter;
        $reflection = new ReflectionClass($limiter);

        $timestampsProperty = $reflection->getProperty('timestampsByUser');
        $timestampsProperty->setAccessible(true);
        $timestampsProperty->setValue(null, [
            100 => [microtime(true) - 20],
            200 => [microtime(true) - 5],
        ]);

        $lastCleanupProperty = $reflection->getProperty('lastGlobalCleanupAt');
        $lastCleanupProperty->setAccessible(true);
        $lastCleanupProperty->setValue(null, microtime(true) - 61);

        $this->assertTrue($limiter->canPost(999));

        $timestampsByUser = $timestampsProperty->getValue(null);

        $this->assertArrayNotHasKey(100, $timestampsByUser);
        $this->assertArrayHasKey(200, $timestampsByUser);
        $this->assertArrayHasKey(999, $timestampsByUser);
    }

    public function test_get_recent_post_count_reflects_sliding_window(): void
    {
        $limiter = new CommentLimiter;

        $this->assertSame(0, $limiter->getRecentPostCount(1));

        $limiter->canPost(1);
        $limiter->canPost(1);

        $this->assertSame(2, $limiter->getRecentPostCount(1));
    }
}
