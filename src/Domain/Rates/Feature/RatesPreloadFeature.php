<?php

namespace App\Domain\Rates\Feature;

use App\Domain\Rates\Message\RatesPreloadMessage;
use App\Domain\Rates\Service\RatesProviderInterface;

final class RatesPreloadFeature
{
    public function __construct(
        private readonly RatesProviderInterface $provider
    ) {
    }

    public function __invoke(RatesPreloadMessage $message): void
    {
        $this->provider->getRates($message->payload->getBaseDate());
        $this->provider->getRates($message->payload->getPreviousDate());
    }
}
