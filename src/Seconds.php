<?php

declare(strict_types=1);

namespace Bene\DateTime;

use DateInterval;
use DateTime;
use DateTimeInterface;
use InvalidArgumentException;
use Throwable;

use function abs;
use function array_map;
use function array_sum;
use function count;
use function floor;
use function get_debug_type;
use function implode;
use function is_array;
use function is_float;
use function is_int;
use function is_string;
use function number_format;
use function preg_match;
use function preg_replace;
use function round;
use function sprintf;
use function trim;

class Seconds
{
    public const SECOND = 1;
    public const MINUTE = 60;

    public const HOUR = 3600;
    public const DAY = 86400;

    /**
     * - `1 day 1 hour 1 minute 1 second 1 millisecond`
     * - `2 days 2 hours 2 minutes 2 seconds 2 milliseconds`
     * - `1 microsecond`
     * - `2 microseconds`
     */
    public const FORMAT_FULL = 0;

    /**
     * - `1 day 1 hr 1 min 1 sec 1 ms`
     * - `2 days 2 hrs 2 min 2 sec 2 ms`
     * - `1 µs`
     * - `2 µs`
     */
    public const FORMAT_SHORT = 1;

    /**
     * - `1d 1h 1m 1s 1ms`
     * - `2d 2h 2m 2s 2ms`
     * - `1µs`
     * - `2µs`
     */
    public const FORMAT_MINIMAL = 2;

    /**
     * ```
     * seconds == 0    -> PRECISION_SECONDS
     * seconds < 0.001 -> PRECISION_MICROSECONDS
     * seconds < 60    -> PRECISION_MILLISECONDS
     * default         -> PRECISION_SECONDS
     * ```
     */
    public const PRECISION_AUTO = 0;

    /**
     * ```
     * seconds == 0 -> "0 sec"
     * seconds < 1  -> "< 1 sec"
     * default      -> "{round(seconds)} sec"
     * ```
     */
    public const PRECISION_SECONDS = 1;

    /**
     * Append milliseconds if seconds has a decimal fraction or FLAG_FOLLOWING_ZERO_UNITS is used.
     */
    public const PRECISION_MILLISECONDS = 2;

    /**
     * Append microseconds if seconds has a decimal fraction or FLAG_FOLLOWING_ZERO_UNITS is used.
     */
    public const PRECISION_MICROSECONDS = 3;

    /**
     * µs -> us
     */
    public const FLAG_ASCII = 1;

    /**
     * - without flag: 1 hr 2 sec
     * - with flag:    1 hr 0 min 2 sec
     */
    public const FLAG_FOLLOWING_ZERO_UNITS = 2;

    private function __construct()
    {
    }

    public static function fromUnits(
        int $days = 0,
        int $hours = 0,
        int $minutes = 0,
        int|float $seconds = 0,
        int $microseconds = 0,
    ): int|float {
        $sum = $days * self::DAY + $hours * self::HOUR + $minutes * self::MINUTE + $seconds + $microseconds / 1_000_000;

        if (is_int($sum)) {
            return $sum;
        }

        return self::roundFloat($sum);
    }

    private static function roundFloat(float $s): float
    {
        return round($s, 6);
    }

    /**
     * Returns number of seconds.
     * The float value is rounded to microseconds.
     * The return value can be negative.
     *
     * From:
     * - `null` -> 0
     * - `false` -> 0
     * - `int` -> int
     * - `float` -> float
     * - `array` -> sum of values
     * - `DateInterval` -> number of seconds (since the unix epoch)
     * - `DateTimeInterface` -> unix timestamp
     * - empty string: `""` -> 0
     * - number as string: `"-?\d+"` -> -?\d+
     * - DateInterval constructor format: `PT1S` -> 1
     * - ISO 8601: `hh:mm`, `hh:mm:ss`, `hh:mm:ss.sss` (no limit for the decimal fraction)
     * - Allows min format `1d2h3m4s5us`, `1d 2h 3m 4s 5ms`, case-insensitive, negative numbers, with or without spaces
     * - Format as used in the DateTime and DateTimeImmutable constructors
     *   or in the DateInterval::createFromDateString method:
     *   - Only number and unit symbols make sense to use.
     *   - see https://www.php.net/manual/en/datetime.formats.php#datetime.formats.relative
     *   - `1day(s) 2hour(s) 3minute(s)|min(s) 4second(s)|sec(s) 5millisecond(s)|msec(s)|ms` -
     *      useful for console commands (e.g. --lifetime=60min)
     *   - `1 day(s) 2 hour(s) 3 minute(s)|min(s) 4 second(s)|sec(s) 5 millisecond(s)|msec(s)|ms` -
     *      useful for configs (e.g. lifetime: "1 hour 30 min" or "90 minutes")
     *   - `1 Day(s) 2 Hour(s) 3 Minute(s)|Min(s) 4 Second(s)|Sec(s) 5 Millisecond(s)|Msec(s)|Ms` - case-insensitive
     *   - Milliseconds or Microseconds: `5 microsecond(s)|μs|μsec(s)|us|usec`
     *   - `"1day2hour3min4sec5ms"` - no space needed, useful for console commands
     *
     * @throws InvalidArgumentException If the input is invalid.
     */
    public static function from(false|int|float|string|array|DateInterval|DateTimeInterface|null $value): int|float
    {
        if (!is_string($value)) {
            if ($value === null || $value === false) {
                return 0;
            }

            if (is_int($value) || is_float($value)) {
                return $value;
            }

            if (is_array($value)) {
                return array_sum(array_map(static fn(mixed $f) => self::from($f), $value));
            }

            if ($value instanceof DateInterval) {
                return self::intervalToTimestamp($value);
            }

            if ($value instanceof DateTimeInterface) {
                return self::dateToTimestamp($value);
            }

            throw new InvalidArgumentException(
                sprintf('Resolving seconds from value of type "%s" is not implemented.', get_debug_type($value)),
            );
        }

        $value = trim($value);

        if ($value === '') {
            return 0;
        }

        // number as string
        if (preg_match('/^-?\d+(\.\d+)?$/', $value, $matches)) {
            if (isset($matches[1])) {
                return self::roundFloat((float) $value);
            }

            return (int) $value;
        }

        // DateInterval format
        if ($value[0] === 'P') {
            try {
                return self::intervalToTimestamp(new DateInterval($value));
            } catch (Throwable $e) {
                throw new InvalidArgumentException(sprintf('Invalid time "%s".', $value), 0, $e);
            }
        }

        // ISO 8601
        // see: https://en.wikipedia.org/wiki/ISO_8601#Times
        // Implemented: hh:mm, hh:mm:ss, hh:mm:ss.sss
        // The "T" prefix version is not implemented.
        if (preg_match('/^(\d{2}):(\d{2})(?::(\d{2}(\.\d+)?))?$/', $value, $matches)) {
            $s = 3600 * (int) $matches[1];
            $s += 60 * (int) $matches[2];

            if (isset($matches[3])) {
                $s += isset($matches[4]) ? self::roundFloat((float) $matches[3]) : (int) $matches[3];
            }

            return $s;
        }

        // Allow FORMAT_MINIMAL and hours from FORMAT_MINIMAL (hour abbreviation).
        // see: https://en.wikipedia.org/wiki/Hour
        $replacements = [
            ['d', 'day'],
            ['h(?:rs?)?', 'hour'],
            ['m', 'min'],
            ['s', 'sec'],
            ['us', 'usec'], // By default, PHP allows µs, µsec(s) and usec(s)
        ];
        foreach ($replacements as [$part, $replacement]) {
            $value = preg_replace('/(\d+\s*)' . $part . '([^a-z]|$)/i', '$1' . $replacement . '$2', $value);
        }

        // parse
        $interval = @DateInterval::createFromDateString($value);

        if ($interval === false) {
            throw new InvalidArgumentException(sprintf('Invalid time "%s".', $value));
        }

        return self::intervalToTimestamp($interval);
    }

    private static function intervalToTimestamp(DateInterval $interval): int|float
    {
        return self::dateToTimestamp((new DateTime('@0'))->add($interval));
    }

    private static function dateToTimestamp(DateTimeInterface $date): int|float
    {
        if ($date->format('u') === '000000') {
            return $date->getTimestamp();
        }

        return (float) $date->format('U.u');
    }

    /** @throws InvalidArgumentException If the format or precision or max unit is invalid. */
    public static function format(
        int|float $second,
        int $format = self::FORMAT_SHORT,
        int $precision = self::PRECISION_AUTO,
        int $maxUnit = self::DAY,
        int $flags = 0,
    ): string {
        if (
            !isset(
                [
                    self::PRECISION_AUTO => true,
                    self::PRECISION_SECONDS => true,
                    self::PRECISION_MILLISECONDS => true,
                    self::PRECISION_MICROSECONDS => true,
                ][$precision],
            )
        ) {
            throw new InvalidArgumentException(sprintf('Invalid precision "%d".', $maxUnit));
        }

        if (!isset([self::SECOND => true, self::MINUTE => true, self::HOUR => true, self::DAY => true][$maxUnit])) {
            throw new InvalidArgumentException(sprintf('Invalid max unit "%d".', $maxUnit));
        }

        $minus = false;

        if ($second < 0) {
            $minus = true;
            $second = abs($second);
        }

        if ($precision === self::PRECISION_AUTO) {
            if ($second === 0.0) {
                $second = 0;
            }

            $precision = match (true) {
                $second === 0 => self::PRECISION_SECONDS,
                $second < 0.001 => self::PRECISION_MICROSECONDS,
                $second < 60 => self::PRECISION_MILLISECONDS,
                default => self::PRECISION_SECONDS,
            };
        }

        // define format
        $ascii = self::FLAG_ASCII & $flags;
        $followingZeroUnits = self::FLAG_FOLLOWING_ZERO_UNITS & $flags;

        // phpcs:disable Squiz.Arrays.ArrayDeclaration.ValueNoNewline
        [$delimiter, $units, $msecNames, $usecNames] = match ($format) {
            // Units must be sorted form highest to lowest.
            self::FORMAT_FULL => [
                ' ',
                [
                    self::DAY => ['day', 'days'],
                    self::HOUR => ['hour', 'hours'],
                    self::MINUTE => ['minute', 'minutes'],
                    self::SECOND => ['second', 'seconds'],
                ],
                ['millisecond', 'milliseconds'],
                ['microsecond', 'microseconds'],
            ],
            self::FORMAT_SHORT => [
                ' ',
                [
                    self::DAY => ['day', 'days'],
                    self::HOUR => ['hr', 'hrs'],
                    self::MINUTE => ['min', 'min'],
                    self::SECOND => ['sec', 'sec'],
                ],
                ['ms', 'ms'],
                $ascii ? ['us', 'us'] : ['µs', 'µs'],
            ],
            self::FORMAT_MINIMAL => [
                '',
                [
                    self::DAY => ['d', 'd'],
                    self::HOUR => ['h', 'h'],
                    self::MINUTE => ['m', 'm'],
                    self::SECOND => ['s', 's'],
                ],
                ['ms', 'ms'],
                $ascii ? ['us', 'us'] : ['µs', 'µs'],
            ],
            default => throw new InvalidArgumentException(sprintf('Invalid format "%d".', $format)),
        };
        // phpcs:enable Squiz.Arrays.ArrayDeclaration.ValueNoNewline

        [$precisionNames, $allowDecimal] = match ($precision) {
            self::PRECISION_SECONDS => [$units[self::SECOND], false],
            self::PRECISION_MILLISECONDS => [$msecNames, true],
            self::PRECISION_MICROSECONDS => [$usecNames, true],
        };

        $formatter = static function (int $value, string $delimiter, string $unitName): string {
            return number_format($value) . $delimiter . $unitName;
        };

        // find out decimals
        $lessThanNames = null;
        $decimal = 0;

        if (is_float($second)) {
            if ($precision === self::PRECISION_SECONDS) {
                if ($second < 1) {
                    $lessThanNames = $units[self::SECOND];
                } else {
                    $second = (int) round($second);
                }
            } else {
                $exponent = match ($precision) {
                    self::PRECISION_MILLISECONDS => 3,
                    self::PRECISION_MICROSECONDS => 6,
                };
                $originalSec = $second;
                $second = (int) floor($originalSec);
                $decimal = (int) round(($originalSec - $second) * (10 ** $exponent));

                if ($decimal === 0) {
                    $allowDecimal = false; // avoid "< 1 ms 0 ms"
                    $lessThanNames = $precisionNames;
                }
            }
        }

        // build
        $parts = [];

        if ($lessThanNames !== null) {
            $parts[] = '<' . $delimiter . $formatter(1, $delimiter, $lessThanNames[0]);
        } elseif ($second > 0) {
            foreach ($units as $unit => $names) {
                if ($unit > $maxUnit) {
                    continue;
                }

                if ($second >= $unit) {
                    $uniValue = (int) floor($second / $unit);
                    $second -= $uniValue * $unit;
                    $parts[] = $formatter($uniValue, $delimiter, $uniValue === 1 ? $names[0] : $names[1]);

                    continue;
                }

                if (!$followingZeroUnits || count($parts) <= 0) {
                    continue;
                }

                $parts[] = $formatter(0, $delimiter, $names[1]);
            }
        }

        if ($allowDecimal) {
            if ($decimal > 0) {
                $parts[] = $formatter($decimal, $delimiter, $decimal === 1 ? $precisionNames[0] : $precisionNames[1]);
            } elseif ($followingZeroUnits) {
                $parts[] = $formatter(0, $delimiter, $precisionNames[1]);
            }
        }

        if (count($parts) === 0) {
            $parts[] = $formatter(0, $delimiter, $precisionNames[1]);
        }

        // format
        $s = implode(' ', $parts);

        if ($minus) {
            $s = 'minus ' . $s;
        }

        return $s;
    }
}
