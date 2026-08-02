<?php

declare(strict_types=1);

namespace Bene\DateTime\Tests;

use Bene\DateTime\Seconds as S;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(S::class)]
class SecondsTest extends TestCase
{
    /** @return array{int, int} */
    public static function dataConsts(): array
    {
        return [
            [1, S::SECOND],
            [60, S::MINUTE],
            [3600, S::HOUR],
            [86400, S::DAY],
        ];
    }

    /** @return array{bool, int|float, array<string, mixed>} */
    public static function dataFromUnits(): array
    {
        return [
            [true, 0, []],
            [true, 0, ['days' => 0, 'hours' => 0, 'minutes' => 0, 'seconds' => 0, 'microseconds' => 0]],
            [true, 90061, ['days' => 1, 'hours' => 1, 'minutes' => 1, 'seconds' => 1, 'microseconds' => 0]],
            [false, 90061.1, ['days' => 1, 'hours' => 1, 'minutes' => 1, 'seconds' => 1.1, 'microseconds' => 0]],
            [false, 90061.000001, ['days' => 1, 'hours' => 1, 'minutes' => 1, 'seconds' => 1, 'microseconds' => 1]],
            [false, 90061.100001, ['days' => 1, 'hours' => 1, 'minutes' => 1, 'seconds' => 1.1, 'microseconds' => 1]],

            [false, 1.000_001, ['seconds' => 1.000_001]],
            [false, 1.000_001, ['seconds' => 1.000_001_1]],
            [false, 1.000_002, ['seconds' => 1.000_001_5]],

            [false, 0.000_001, ['microseconds' => 1]],
            [true, 1, ['microseconds' => 1_000_000]],
        ];
    }

    /** @return array{bool, int|float, mixed} */
    public static function dataFrom(): array
    {
        // 90_000 sec = 25 hour = 1 day 1 hour
        return [
            [true, 0, null],
            [true, 0, false],
            [true, 0, ''],
            [true, 0, ' '],

            // int
            [true, -1, -1],
            [true, 0, 0],
            [true, 1, 1],
            [true, 2, 2],

            // float
            [false, 0.0, 0.0],
            [false, 1.0, 1.0],
            [false, 1.1, 1.1],

            // array
            [true, 0, []],
            [true, 2, [1, 'PT1S']],

            // number as string
            [true, -1, '-1'],
            [true, 0, '0'],
            [true, 1, '1'],
            [true, 2, '2'],

            [false, -1.1, '-1.1'],
            [false, 0.0, '0.0'],
            [false, 1.0, '1.0'],
            [false, 1.1, '1.1'],
            [false, 123_456_789.123_457, '123456789.123456789'],

            [false, -1.1, ' -1.1 '],
            [true, -1, ' -1 '],
            [true, 0, ' 0 '],
            [true, 1, ' 1 '],
            [false, 1.1, ' 1.1 '],

            // DateTimeInterval
            [true, 1, 'PT1S'],
            [true, 1, ' PT1S '],

            // ISO 8601
            [true, 1, '00:00:01'],
            [true, 1, ' 00:00:01 '],
            [true, 61, '00:01:01'],
            [true, 3661, '01:01:01'],
            [true, 3660, '01:01'],
            [false, 3661.123_457, '01:01:01.123456789'],

            // Normalization
            [true, 3600, '1h'],
            [true, 3600, '1 h'],
            [true, 3600, '1h '],
            [true, 3600, ' 1h'],
            [true, 3600, ' 1h '],
            [true, 3600, ' 1 h '],
            [true, -3600, '-1h'],

            [true, 3600 * 12, '12h'],
            [true, 3600 * 12 + 60 * 12, '12h12m'],

            [true, 90061, '1d 1h 1m 1s'],
            [true, 90061, '1d 1hr 1m 1s'],
            [true, 90061, '1d 1hrs 1m 1s'],
            [true, 90061, '1 d 1 h 1 m 1 s'],
            [true, 90061, '1 d 1 hr 1 m 1 s'],
            [true, 90061, '1 d 1 hrs 1 m 1 s'],
            [true, 90061, '1 d1 h1 m1 s'],
            [true, 90061, '1 d1 hr1 m1 s'],
            [true, 90061, '1 d1 hrs1 m1 s'],
            [true, 90061, '1d1h1m1s'],
            [true, 90061, '1d1hr1m1s'],
            [true, 90061, '1d1hrs1m1s'],
            [true, 90059, '1d1h1m-1s'],
            [true, -90061, '-1d-1h-1m-1s'],
            [false, 90061.001, '1d1h1m1s1ms'],
            [false, 90061.000_001, '1d1h1m1s1µs'],
            [false, 90061.000_001, '1d1h1m1s1us'],

            // DateTimeInterval::createFromDateString
            [true, 0, '0 day 0 hour 0 minute 0 second 0 microsecond'],

            [true, 90061, '1 day 1 hour 1 minute 1 second'], // normal
            [true, 90061, '1 DaY 1 HoUr 1 MiNuTe 1 SeCoNd'], // case insensitive
            [true, 90061, '1 days 1 hours 1 minutes 1 seconds'], // plurals
            [true, 90061 * 2, '2 day 2 hour 2 minute 2 second'], // singulars
            [true, 90061, '  1days  1hours  1minutes  1secs  '], // spaces
            [true, 90061, '1days1hours1minutes1secs'], // spaces
            [true, 90059, '1days1hours1minutes-1secs'], // spaces
            [true, 90061, '1 day 1 hour 1 min 1 sec'], // short
            [true, 90061, '1 day 1 hour 1 mins 1 secs'], // short

            [false, 90061.001, '1 day 1 hour 1 minute 1 second 1 millisecond'], // normal
            [false, 90061.001, '1 days 1 hours 1 minutes 1 seconds 1 milliseconds'], // plural
            [false, 90061.001, '1 day 1 hour 1 min 1 sec 1 ms'], // short
            [false, 90061.001, '1 day 1 hour 1 min 1 sec 1 msec'], // short
            [false, 90061.001, '1 day 1 hour 1 min 1 sec 1 msecs'], // short

            [false, 90061.000_001, '1 day 1 hour 1 minute 1 second 1 microsecond'], // normal
            [false, 90061.000_001, '1 days 1 hours 1 minutes 1 seconds 1 microseconds'], // plural
            [false, 90061.000_001, '1 day 1 hour 1 min 1 sec 1 us'], // short
            [false, 90061.000_001, '1 day 1 hour 1 min 1 sec 1 usec'], // short
            [false, 90061.000_001, '1 day 1 hour 1 min 1 sec 1 usecs'], // short
            [false, 90061.000_001, '1 day 1 hour 1 min 1 sec 1 µs'], // short
            [false, 90061.000_001, '1 day 1 hour 1 min 1 sec 1 µsec'], // short
            [false, 90061.000_001, '1 day 1 hour 1 mins 1 secs 1 µsecs'], // short

            [true, 90061, '1 second 1 minute 1 hour 1 day'], // unsorted
            [true, 90061, '1 minute 1 hour 1 day 1 second'], // unsorted

            [true, 3601, '1 hour 1 second'], // some parts
            [true, 3601, '1 second 1 hour'], // some parts, unsorted

            [true, 86400, '1 day'],
            [true, 3600, '1 hour'],
            [true, 90000, '25 hours'],
            [true, 60, '1 minute'],
            [true, 3660, '61 minutes'],
            [true, 1, '1 second'],
            [true, 61, '61 second'],
            [false, 0.001, '1 millisecond'],
            [true, 1, '1000 millisecond'],
            [false, 0.000_001, '1 microsecond'],
            [true, 1, '1000000 microsecond'],

            [true, -60, '-1 minute'],
        ];
    }

    /** @return array{mixed} */
    public static function dataFromException(): array
    {
        return [
            ['01:01:'],
            ['01:'],
            ['1 usecond'],
            ['1 useconds'],
            ['P1S'], // any invalid DateInterval constructor format
        ];
    }

    /** @return array{string, int|float, array<string, mixed>} */
    public static function dataFormat(): array
    {
        return [
            // FORMAT_FULL
            ['0 milliseconds', 0, S::FORMAT_FULL, S::PRECISION_MILLISECONDS],
            ['0 microseconds', 0, S::FORMAT_FULL, S::PRECISION_MICROSECONDS],
            ['0 seconds', 0, S::FORMAT_FULL],

            ['1 millisecond', 0.001, S::FORMAT_FULL, S::PRECISION_MILLISECONDS],
            ['1 microsecond', 0.000_001, S::FORMAT_FULL, S::PRECISION_MICROSECONDS],
            ['1 second', 1, S::FORMAT_FULL],
            ['1 minute', 60, S::FORMAT_FULL],
            ['1 hour', 3600, S::FORMAT_FULL],
            ['1 day', 86400, S::FORMAT_FULL],

            ['2 milliseconds', 0.001 * 2, S::FORMAT_FULL, S::PRECISION_MILLISECONDS],
            ['2 microseconds', 0.000_001 * 2, S::FORMAT_FULL, S::PRECISION_MICROSECONDS],
            ['2 seconds', 1 * 2, S::FORMAT_FULL],
            ['2 minutes', 60 * 2, S::FORMAT_FULL],
            ['2 hours', 3600 * 2, S::FORMAT_FULL],
            ['2 days', 86400 * 2, S::FORMAT_FULL],

            // FORMAT_SHORT
            ['0 ms', 0, S::FORMAT_SHORT, S::PRECISION_MILLISECONDS],
            ['0 µs', 0, S::FORMAT_SHORT, S::PRECISION_MICROSECONDS],
            ['0 sec', 0, S::FORMAT_SHORT],

            ['1 ms', 0.001, S::FORMAT_SHORT, S::PRECISION_MILLISECONDS],
            ['1 µs', 0.000_001, S::FORMAT_SHORT, S::PRECISION_MICROSECONDS],
            ['1 sec', 1, S::FORMAT_SHORT],
            ['1 min', 60, S::FORMAT_SHORT],
            ['1 hr', 3600, S::FORMAT_SHORT],
            ['1 day', 86400, S::FORMAT_SHORT],

            ['2 ms', 0.001 * 2, S::FORMAT_SHORT, S::PRECISION_MILLISECONDS],
            ['2 µs', 0.000_001 * 2, S::FORMAT_SHORT, S::PRECISION_MICROSECONDS],
            ['2 sec', 1 * 2, S::FORMAT_SHORT],
            ['2 min', 60 * 2, S::FORMAT_SHORT],
            ['2 hrs', 3600 * 2, S::FORMAT_SHORT],
            ['2 days', 86400 * 2, S::FORMAT_SHORT],

            // FORMAT_MINIMAL
            ['0ms', 0, S::FORMAT_MINIMAL, S::PRECISION_MILLISECONDS],
            ['0µs', 0, S::FORMAT_MINIMAL, S::PRECISION_MICROSECONDS],
            ['0s', 0, S::FORMAT_MINIMAL],

            ['1ms', 0.001, S::FORMAT_MINIMAL, S::PRECISION_MILLISECONDS],
            ['1µs', 0.000_001, S::FORMAT_MINIMAL, S::PRECISION_MICROSECONDS],
            ['1s', 1, S::FORMAT_MINIMAL],
            ['1m', 60, S::FORMAT_MINIMAL],
            ['1h', 3600, S::FORMAT_MINIMAL],
            ['1d', 86400, S::FORMAT_MINIMAL],

            ['2ms', 0.001 * 2, S::FORMAT_MINIMAL, S::PRECISION_MILLISECONDS],
            ['2µs', 0.000_001 * 2, S::FORMAT_MINIMAL, S::PRECISION_MICROSECONDS],
            ['2s', 1 * 2, S::FORMAT_MINIMAL],
            ['2m', 60 * 2, S::FORMAT_MINIMAL],
            ['2h', 3600 * 2, S::FORMAT_MINIMAL],
            ['2d', 86400 * 2, S::FORMAT_MINIMAL],

            // less than precision
            ['< 1 second', 0.1, S::FORMAT_FULL, S::PRECISION_SECONDS],
            ['< 1 sec', 0.1, S::FORMAT_SHORT, S::PRECISION_SECONDS],
            ['<1s', 0.1, S::FORMAT_MINIMAL, S::PRECISION_SECONDS],

            ['< 1 millisecond', 0.000_1, S::FORMAT_FULL, S::PRECISION_MILLISECONDS],
            ['< 1 ms', 0.000_1, S::FORMAT_SHORT, S::PRECISION_MILLISECONDS],
            ['<1ms', 0.000_1, S::FORMAT_MINIMAL, S::PRECISION_MILLISECONDS],

            ['< 1 microsecond', 0.000_000_1, S::FORMAT_FULL, S::PRECISION_MICROSECONDS],
            ['< 1 µs', 0.000_000_1, S::FORMAT_SHORT, S::PRECISION_MICROSECONDS],
            ['<1µs', 0.000_000_1, S::FORMAT_MINIMAL, S::PRECISION_MICROSECONDS],

            // rounding
            ['< 1 sec', 0.4, S::FORMAT_SHORT, S::PRECISION_SECONDS],
            ['< 1 sec', 0.5, S::FORMAT_SHORT, S::PRECISION_SECONDS],
            ['1 sec', 1.4, S::FORMAT_SHORT, S::PRECISION_SECONDS],
            ['2 sec', 1.5, S::FORMAT_SHORT, S::PRECISION_SECONDS],
            ['< 1 ms', 0.000_4, S::FORMAT_SHORT, S::PRECISION_MILLISECONDS],
            ['1 ms', 0.000_5, S::FORMAT_SHORT, S::PRECISION_MILLISECONDS],
            ['< 1 µs', 0.000_000_4, S::FORMAT_SHORT, S::PRECISION_MICROSECONDS],
            ['1 µs', 0.000_000_5, S::FORMAT_SHORT, S::PRECISION_MICROSECONDS],

            // max unit
            ['1d 1h 1m 1s', 90061, S::FORMAT_MINIMAL, S::PRECISION_SECONDS, S::DAY],
            ['25h 1m 1s', 90061, S::FORMAT_MINIMAL, S::PRECISION_SECONDS, S::HOUR],
            ['1,501m 1s', 90061, S::FORMAT_MINIMAL, S::PRECISION_SECONDS, S::MINUTE],
            ['90,061s', 90061, S::FORMAT_MINIMAL, S::PRECISION_SECONDS, S::SECOND],

            // FLAG_ASCII
            ['500,000 us', 0.5, S::FORMAT_SHORT, S::PRECISION_MICROSECONDS, S::DAY, S::FLAG_ASCII],
            ['500,000us', 0.5, S::FORMAT_MINIMAL, S::PRECISION_MICROSECONDS, S::DAY, S::FLAG_ASCII],

            // without FLAG_FOLLOWING_ZERO_UNITS
            ['1h 1s', 3601, S::FORMAT_MINIMAL],
            ['1d 1m', 86460, S::FORMAT_MINIMAL],

            // FLAG_FOLLOWING_ZERO_UNITS
            ['1h 0m 1s', 3601, S::FORMAT_MINIMAL, S::PRECISION_SECONDS, S::DAY, S::FLAG_FOLLOWING_ZERO_UNITS],
            ['1d 0h 1m 0s', 86460, S::FORMAT_MINIMAL, S::PRECISION_SECONDS, S::DAY, S::FLAG_FOLLOWING_ZERO_UNITS],
            [
                '1d 0h 1m 0s 0ms',
                86460,
                S::FORMAT_MINIMAL,
                S::PRECISION_MILLISECONDS,
                S::DAY,
                S::FLAG_FOLLOWING_ZERO_UNITS,
            ],
            [
                '1d 0h 1m 0s 0µs',
                86460,
                S::FORMAT_MINIMAL,
                S::PRECISION_MICROSECONDS,
                S::DAY,
                S::FLAG_FOLLOWING_ZERO_UNITS,
            ],

            // formatted number
            ['500,000 µs', 0.5, S::FORMAT_SHORT, S::PRECISION_MICROSECONDS, S::DAY],

            // minus
            ['minus 1d 1h 1m 1s', -90061, S::FORMAT_MINIMAL, S::PRECISION_SECONDS, S::DAY],
        ];
    }

    #[DataProvider('dataConsts')]
    public function testConsts(int $expected, int $seconds): void
    {
        $this->assertSame($expected, $seconds);
    }

    /** @param array<string, mixed> $args */
    #[DataProvider('dataFromUnits')]
    public function testFromUnits(bool $isInt, int|float $expected, array $args): void
    {
        $seconds = S::fromUnits(...$args);

        if ($isInt) {
            self::assertIsInt($seconds);
        } else {
            self::assertIsFloat($seconds);
        }

        $this->assertSame($expected, $seconds);
    }

    #[DataProvider('dataFrom')]
    public function testFrom(bool $isInt, int|float $expected, mixed $from): void
    {
        $seconds = S::from($from);

        if ($isInt) {
            self::assertIsInt($seconds);
        } else {
            self::assertIsFloat($seconds);
        }

        $this->assertSame($expected, $seconds);
    }

    #[DataProvider('dataFromException')]
    public function testFromException(mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        S::from($value);
    }

    #[DataProvider('dataFormat')]
    public function testFormat(
        string $expected,
        int|float $second,
        int $format,
        int $precision = S::PRECISION_AUTO,
        int $maxUnit = S::DAY,
        int $flags = 0,
    ): void {
        $this->assertSame($expected, S::format($second, $format, $precision, $maxUnit, $flags));
    }
}
