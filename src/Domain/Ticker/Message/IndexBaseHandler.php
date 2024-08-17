<?php

namespace App\Domain\Ticker\Message;

use App\Domain\Ticker\Feature\IndexBaseFeature;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(fromTransport: "index_base")]
final class IndexBaseHandler
{
    public function __construct(private readonly IndexBaseFeature $feature)
    {
    }

    public function __invoke(IndexBaseMessage $message): void
    {
        ($this->feature)($message);
    }
}
