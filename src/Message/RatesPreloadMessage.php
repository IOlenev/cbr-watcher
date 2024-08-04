<?php

namespace App\Message;

use App\Dto\DateDto;
use DateTime;
use DateTimeImmutable;

final class RatesPreloadMessage
{
    private DateDto $previousDate;

    private function __construct(private DateDto $baseDate)
    {
        $this->previousDate = DateDto::create(
            DateTimeImmutable::createFromMutable(
                (new DateTime($this->baseDate->format('Y-m-d')))->modify('-1 day')
            )
        );
    }

    public static function create(?DateDto $baseDate = null): self
    {
        if (is_null($baseDate)) {
            $baseDate = DateDto::create();
        }

        return new self($baseDate);
    }

    public function getBaseDate(): DateDto
    {
        return $this->baseDate;
    }

    public function getPreviousDate(): DateDto
    {
        return $this->previousDate;
    }
}
