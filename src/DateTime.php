<?php

declare(strict_types=1);

namespace Bene\DateTime;

use DateTimeImmutable;

class DateTime extends \DateTime
{
    use DateTimeShared;

    public static function createFromImmutable(DateTimeImmutable $object): static
    {
        return parent::createFromImmutable($object);
    }
}
