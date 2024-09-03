<?php

namespace App\Domain\Rates\Message;

use App\Dto\DateDto;

readonly class WarmupDateMessage
{
    public function __construct(public DateDto $date, public array $tickers) {}
}
