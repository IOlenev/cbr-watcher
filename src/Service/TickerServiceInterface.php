<?php

namespace App\Service;

use App\Dto\DateDto;
use App\Dto\TickerDto;

interface TickerServiceInterface
{
    public function withDate(?DateDto $date = null): self;

    public function getTicker(string $charCode): ?TickerDto;

}
