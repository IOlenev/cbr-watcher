<?php

namespace App\Service;

use App\Dto\DateDto;

interface RatesProviderInterface
{
    public function getRates(?DateDto $date = null): ?string;
}
