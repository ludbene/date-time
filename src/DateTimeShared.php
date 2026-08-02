<?php

declare(strict_types=1);

namespace Bene\DateTime;

use DateInterval;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use InvalidArgumentException;

use function abs;
use function is_int;
use function preg_match;
use function round;
use function sprintf;

/**
 * Methods must work for mutable as well as immutable object.
 */
trait DateTimeShared
{
    public static function createFromFormat(
        string $format,
        string $datetime,
        DateTimeZone|null $timezone = null,
    ): static|false {
        return parent::createFromFormat($format, $datetime, $timezone);
    }

    public static function createFromInterface(DateTimeInterface $object): static
    {
        return parent::createFromInterface($object);
    }

    /** @throws Exception */
    public static function createFromTimestamp(int|float $timestamp): static
    {
        return new static(is_int($timestamp) ? '@' . $timestamp : sprintf('@%.6f', $timestamp));
    }

    public static function createTodayFirstSecond(DateTimeZone|null $timezone = null): static
    {
        return new static('now 00:00:00', $timezone);
    }

    public static function createTodayLastSecond(DateTimeZone|null $timezone = null): static
    {
        return new static('now 23:59:59', $timezone);
    }

    public static function createEndOfWeekLastSecond(DateTimeZone|null $timezone = null): static
    {
        $date = new static('now 23:59:59', $timezone);
        $day = $date->getIsoDayOfWeek();

        if ($day < 7) {
            $date = $date->addDays(7 - $day);
        }

        return $date;
    }

    /**
     * Returns ISO 8601 numeric representation of the day of the week.
     *
     * Values: 1 (for Monday) through 7 (for Sunday)
     */
    public function getIsoDayOfWeek(): int
    {
        return (int) $this->format('N');
    }

    public function addDays(int $value): static
    {
        return match (true) {
            $value === 0 => $this,
            $value < 0 => $this->subDays(abs($value)),
            default => $this->add(new DateInterval('P' . $value . 'D')),
        };
    }

    public function subDays(int $value): static
    {
        return match (true) {
            $value === 0 => $this,
            $value < 0 => $this->addDays(abs($value)),
            default => $this->sub(new DateInterval('P' . $value . 'D')),
        };
    }

    public static function createEndOfMonthLastSecond(DateTimeZone|null $timezone = null): static
    {
        $date = new static('now 23:59:59', $timezone);
        $day = $date->getDay();
        $dayCount = $date->getNumberOfDaysInMonth();

        if ($day < $dayCount) {
            $date = $date->setDate($date->getYear(), $date->getMonth(), $dayCount);
        }

        return $date;
    }

    public function getDay(): int
    {
        return (int) $this->format('j');
    }

    public function getNumberOfDaysInMonth(): int
    {
        return (int) $this->format('t');
    }

    public function getYear(): int
    {
        return (int) $this->format('Y');
    }

    public function getMonth(): int
    {
        return (int) $this->format('n');
    }

    public function addHours(int $value): static
    {
        return match (true) {
            $value === 0 => $this,
            $value < 0 => $this->subHours(abs($value)),
            default => $this->add(new DateInterval('PT' . $value . 'H')),
        };
    }

    public function subHours(int $value): static
    {
        return match (true) {
            $value === 0 => $this,
            $value < 0 => $this->addHours(abs($value)),
            default => $this->sub(new DateInterval('PT' . $value . 'H')),
        };
    }

    public function addMinutes(int $value): static
    {
        return match (true) {
            $value === 0 => $this,
            $value < 0 => $this->subMinutes(abs($value)),
            default => $this->add(new DateInterval('PT' . $value . 'M')),
        };
    }

    public function subMinutes(int $value): static
    {
        return match (true) {
            $value === 0 => $this,
            $value < 0 => $this->addMinutes(abs($value)),
            default => $this->sub(new DateInterval('PT' . $value . 'M')),
        };
    }

    public function addSeconds(int $value): static
    {
        return match (true) {
            $value === 0 => $this,
            $value < 0 => $this->subSeconds(abs($value)),
            default => $this->add(new DateInterval('PT' . $value . 'S')),
        };
    }

    public function subSeconds(int $value): static
    {
        return match (true) {
            $value === 0 => $this,
            $value < 0 => $this->addSeconds(abs($value)),
            default => $this->sub(new DateInterval('PT' . $value . 'S')),
        };
    }

    /**
     * Format: hh:mm|hh:mm:ss|hh:mm:ss.sss (no limit for the decimal fraction)
     */
    public function setTimeFromString(string $value): static
    {
        if (!preg_match('/^([0-1][0-9]|2[0-3]):([0-5][0-9])(?::([0-5][0-9])(\.\d+)?)?$/', $value, $matches)) {
            throw new InvalidArgumentException(sprintf('Invalid time format "%s".', $value));
        }

        return $this->setTime(
            (int) $matches[1],
            (int) ($matches[2] ?? 0),
            (int) ($matches[3] ?? 0),
            isset($matches[4]) ? (int) round(1_000_000 * ('0' . $matches[4])) : 0,
        );
    }

    public function isLeapYear(): bool
    {
        return (bool) $this->format('L');
    }

    /**
     * Numeric representation of the day of the week starting with zero.
     *
     * Values: 0 (for Sunday) through 6 (for Saturday)
     */
    public function getDayOfWeek(): int
    {
        return (int) $this->format('w');
    }

    public function getHour(): int
    {
        return (int) $this->format('G');
    }

    public function getMinute(): int
    {
        return (int) $this->format('i');
    }

    public function getSecond(): int
    {
        return (int) $this->format('s');
    }

    public function getMillisecond(): int
    {
        return (int) $this->format('v');
    }

    public function getMicrosecond(): int
    {
        return (int) $this->format('u');
    }

    public function getMilliTimestamp(): int
    {
        return (int) $this->format('Uv');
    }

    public function getMicroTimestamp(): int
    {
        return (int) $this->format('Uu');
    }
}
