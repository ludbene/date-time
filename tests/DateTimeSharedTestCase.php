<?php

declare(strict_types=1);

namespace Bene\DateTime\Tests;

use Bene\DateTime\DateTime as Dt;
use Bene\DateTime\DateTimeImmutable as Dti;
use DateTimeZone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

abstract class DateTimeSharedTestCase extends TestCase
{
    /** @return class-string<Dt|Dti> */
    abstract protected function getDateTimeClass(): string;

    /** @return array{string, string, callable} */
    public static function dataModifiers(): array
    {
        return [
            // expected, initial, modifier
            ['2022-05-15 12:30:00.000000', '2022-05-15 12:30:00', static fn(Dt|Dti $d) => $d->addDays(0)],
            ['2022-05-15 12:30:00.000000', '2022-05-15 12:30:00', static fn(Dt|Dti $d) => $d->addHours(0)],
            ['2022-05-15 12:30:00.000000', '2022-05-15 12:30:00', static fn(Dt|Dti $d) => $d->addMinutes(0)],
            ['2022-05-15 12:30:00.000000', '2022-05-15 12:30:00', static fn(Dt|Dti $d) => $d->addSeconds(0)],

            ['2022-05-20 12:30:00.000000', '2022-05-15 12:30:00', static fn(Dt|Dti $d) => $d->addDays(5)],
            ['2022-05-15 17:30:00.000000', '2022-05-15 12:30:00', static fn(Dt|Dti $d) => $d->addHours(5)],
            ['2022-05-15 12:35:00.000000', '2022-05-15 12:30:00', static fn(Dt|Dti $d) => $d->addMinutes(5)],
            ['2022-05-15 12:30:05.000000', '2022-05-15 12:30:00', static fn(Dt|Dti $d) => $d->addSeconds(5)],

            ['2022-05-10 12:30:00.000000', '2022-05-15 12:30:00', static fn(Dt|Dti $d) => $d->addDays(-5)],
            ['2022-05-15 07:30:00.000000', '2022-05-15 12:30:00', static fn(Dt|Dti $d) => $d->addHours(-5)],
            ['2022-05-15 12:25:00.000000', '2022-05-15 12:30:00', static fn(Dt|Dti $d) => $d->addMinutes(-5)],
            ['2022-05-15 12:29:55.000000', '2022-05-15 12:30:00', static fn(Dt|Dti $d) => $d->addSeconds(-5)],

            ['2022-05-15 12:30:00.000000', '2022-05-15 12:30:00', static fn(Dt|Dti $d) => $d->subDays(0)],
            ['2022-05-15 12:30:00.000000', '2022-05-15 12:30:00', static fn(Dt|Dti $d) => $d->subHours(0)],
            ['2022-05-15 12:30:00.000000', '2022-05-15 12:30:00', static fn(Dt|Dti $d) => $d->subMinutes(0)],
            ['2022-05-15 12:30:00.000000', '2022-05-15 12:30:00', static fn(Dt|Dti $d) => $d->subSeconds(0)],

            ['2022-05-10 12:30:00.000000', '2022-05-15 12:30:00', static fn(Dt|Dti $d) => $d->subDays(5)],
            ['2022-05-15 07:30:00.000000', '2022-05-15 12:30:00', static fn(Dt|Dti $d) => $d->subHours(5)],
            ['2022-05-15 12:25:00.000000', '2022-05-15 12:30:00', static fn(Dt|Dti $d) => $d->subMinutes(5)],
            ['2022-05-15 12:29:55.000000', '2022-05-15 12:30:00', static fn(Dt|Dti $d) => $d->subSeconds(5)],

            ['2022-05-20 12:30:00.000000', '2022-05-15 12:30:00', static fn(Dt|Dti $d) => $d->subDays(-5)],
            ['2022-05-15 17:30:00.000000', '2022-05-15 12:30:00', static fn(Dt|Dti $d) => $d->subHours(-5)],
            ['2022-05-15 12:35:00.000000', '2022-05-15 12:30:00', static fn(Dt|Dti $d) => $d->subMinutes(-5)],
            ['2022-05-15 12:30:05.000000', '2022-05-15 12:30:00', static fn(Dt|Dti $d) => $d->subSeconds(-5)],

            [
                '2022-05-15 10:20:00.000000',
                '2022-05-15 01:02:03',
                static fn(Dt|Dti $d) => $d->setTimeFromString('10:20'),
            ],
            [
                '2022-05-15 10:20:30.000000',
                '2022-05-15 01:02:03',
                static fn(Dt|Dti $d) => $d->setTimeFromString('10:20:30'),
            ],
            [
                '2022-05-15 10:20:30.123457',
                '2022-05-15 01:02:03',
                static fn(Dt|Dti $d) => $d->setTimeFromString('10:20:30.123456789'),
            ],
        ];
    }

    public function testGetters(): void
    {
        $date = $this->createDateTime('2021-09-08 11:22:33.444555', new DateTimeZone('UTC'));

        $this->assertSame(1631100153, $date->getTimestamp());
        $this->assertSame('444555', $date->format('u'));
        $this->assertSame('444', $date->format('v'));
        $this->assertSame(1631100153444555, $date->getMicroTimestamp());
        $this->assertSame(1631100153444, $date->getMilliTimestamp());
        $this->assertSame(444555, $date->getMicrosecond());
        $this->assertSame(444, $date->getMillisecond());
        $this->assertSame(33, $date->getSecond());
        $this->assertSame(22, $date->getMinute());
        $this->assertSame(11, $date->getHour());
        $this->assertSame(8, $date->getDay());
        $this->assertSame(9, $date->getMonth());
        $this->assertSame(2021, $date->getYear());
    }

    public function testLeapYear(): void
    {
        $date = $this->createDateTime('2021-09-08 11:22:33.444555', new DateTimeZone('UTC'));
        $this->assertFalse($date->isLeapYear());

        $date = $this->createDateTime('2020-09-08 11:22:33.444555', new DateTimeZone('UTC'));
        $this->assertTrue($date->isLeapYear());
    }

    public function testCreateFromTimestamp(): void
    {
        $date = $this->createDateTimeBy('createFromTimestamp', 1631100153);
        $this->assertSame(1631100153, $date->getTimestamp());
        $this->assertSame(1631100153000000, $date->getMicroTimestamp());

        $date = $this->createDateTimeBy('createFromTimestamp', 1631100153.444);
        $this->assertSame(1631100153, $date->getTimestamp());
        $this->assertSame(1631100153444000, $date->getMicroTimestamp());

        $date = $this->createDateTimeBy('createFromTimestamp', 1631100153.444555);
        $this->assertSame(1631100153, $date->getTimestamp());
        $this->assertSame(1631100153444555, $date->getMicroTimestamp());

        $date = $this->createDateTimeBy('createFromTimestamp', 1631100153.4445556);
        $this->assertSame(1631100153, $date->getTimestamp());
        $this->assertSame(1631100153444556, $date->getMicroTimestamp());
    }

    #[DataProvider('dataModifiers')]
    public function testModifiers(string $expected, string $datetime, callable $modifier): void
    {
        $date = $this->createDateTime($datetime, new DateTimeZone('UTC'));
        $date = $modifier($date);
        $this->assertSame($expected, $date->format('Y-m-d H:i:s.u'));
    }

    public function testCreateTodayFirstSecond(): void
    {
        $date = $this->createDateTimeBy('createTodayFirstSecond', new DateTimeZone('UTC'));
        $this->assertSame('00:00:00', $date->format('H:i:s'));
    }

    public function testCreateTodayLastSecond(): void
    {
        $date = $this->createDateTimeBy('createTodayLastSecond', new DateTimeZone('UTC'));
        $this->assertSame('23:59:59', $date->format('H:i:s'));
    }

    public function testCreateEndOfWeekLastSecond(): void
    {
        $date = $this->createDateTimeBy('createEndOfWeekLastSecond', new DateTimeZone('UTC'));
        $this->assertSame('23:59:59', $date->format('H:i:s'));
        $this->assertSame(0, $date->getDayOfWeek());
        $this->assertSame(7, $date->getIsoDayOfWeek());
    }

    public function testCreateEndOfMonthLastSecond(): void
    {
        $date = $this->createDateTimeBy('createEndOfMonthLastSecond', new DateTimeZone('UTC'));

        $dayCounts = [
            1 => 31,
            2 => $date->isLeapYear() ? 29 : 28,
            3 => 31,
            4 => 30,
            5 => 31,
            6 => 30,
            7 => 31,
            8 => 31,
            9 => 30,
            10 => 31,
            11 => 30,
            12 => 31,
        ];

        $this->assertSame('23:59:59', $date->format('H:i:s'));
        $this->assertSame($dayCounts[$date->getMonth()], $date->getDay());
    }

    protected function createDateTime(string $datetime = 'now', DateTimeZone|null $timezone = null): Dt|Dti
    {
        return new ($this->getDateTimeClass())($datetime, $timezone);
    }

    protected function createDateTimeBy(string $method, mixed ...$arguments): Dt|Dti
    {
        return [static::getDateTimeClass(), $method](...$arguments);
    }
}
