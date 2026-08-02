<?php

declare(strict_types=1);

namespace Bene\DateTime\Tests;

use Bene\DateTime\DateTime;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DateTime::class)]
class DateTimeTest extends DateTimeSharedTestCase
{
    protected function getDateTimeClass(): string
    {
        return DateTime::class;
    }
}
