<?php

namespace App\Service;

use DateTimeInterface;

interface RatesProviderInterface
{
    public function getRates(DateTimeInterface $date): ?string;
}
