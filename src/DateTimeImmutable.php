<?php

declare(strict_types=1);

namespace Bene\DateTime;

use DateTime;

class DateTimeImmutable extends \DateTimeImmutable
{
    use DateTimeShared;

    public static function createFromMutable(DateTime $object): static
    {
        return parent::createFromMutable($object);
    }
}
