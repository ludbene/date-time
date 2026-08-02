<?php

declare(strict_types=1);

namespace Bene\DateTime\Tests;

use Bene\DateTime\DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DateTimeImmutable::class)]
class DateTimeImmutableTest extends DateTimeSharedTestCase
{
    protected function getDateTimeClass(): string
    {
        return DateTimeImmutable::class;
    }
}
