<?php

namespace App\Domain\Rates\Service;

use App\Dto\DateDto;

interface RatesProviderInterface
{
    public function getRates(?DateDto $date = null): ?string;
}
