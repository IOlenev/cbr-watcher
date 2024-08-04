<?php

namespace App\Dto;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

final class DateDto
{
    private const FORMAT = 'Ymd';

    private function __construct(private DateTimeInterface $date)
    {
    }

    public static function create(?DateTimeInterface $date = null): self
    {
        if (is_null($date)) {
            $date = DateTimeImmutable::createFromMutable(new DateTime('00:00'));
        }

        return new self($date);
    }

    public function format(string $pattern): string
    {
        return $this->date->format($pattern);
    }

    public function __toString(): string
    {
        return $this->format(self::FORMAT);
    }
}
