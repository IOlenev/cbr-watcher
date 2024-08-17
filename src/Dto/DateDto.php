<?php

namespace App\Dto;

use DateTime;

final class DateDto
{
    private const FORMAT = 'Ymd';

    private function __construct(private DateTime $date)
    {
    }

    public static function create(?DateTime $date = null): self
    {
        $date ??= new DateTime();

        return new self($date->setTime(0, 0));
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
