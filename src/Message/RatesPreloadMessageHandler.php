<?php

namespace App\Message;

use App\Service\RatesProviderInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RatesPreloadMessageHandler
{
    public function __construct(
        private RatesProviderInterface $provider
    ) {
    }

    public function __invoke(RatesPreloadMessage $message): void
    {
        $this->provider->getRates($message->getBaseDate());
        $this->provider->getRates($message->getPreviousDate());
    }
}
