<?php

namespace App\Tests\Functional;

use App\Domain\Ticker\Dto\TickerDto;
use App\Domain\Ticker\Dto\TickerPayloadDto;
use App\Dto\DateDto;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TickerPayloadTest extends KernelTestCase
{
    private const CODE = 'BLA';
    private const BASE_DATE = '-1 day';
    private const PREVIOUS_DATE = '-2 day';

    private TickerPayloadDto $message;

    protected function setUp(): void
    {
        parent::setUp();
        $this->message = TickerPayloadDto::create(
            self::CODE,
            DateDto::create(
                new DateTime(self::BASE_DATE)
            )
        );
    }

    public function testMessageDates(): void
    {
        self::assertEquals(
            DateDto::create(new DateTime(self::BASE_DATE))->format('Y-m-d'),
            $this->message->getBaseDate()->format('Y-m-d')
        );
        self::assertEquals(
            DateDto::create(new DateTime(self::PREVIOUS_DATE))->format('Y-m-d'),
            $this->message->getPreviousDate()->format('Y-m-d')
        );
    }

    public function testMessageDefaultDates(): void
    {
        $this->message = TickerPayloadDto::create(
            self::CODE,
            DateDto::create(),
            TickerDto::DEFAULT_CURRENCY
        );

        self::assertEquals(
            (new DateTime())->format('Y-m-d'),
            $this->message->getBaseDate()->format('Y-m-d')
        );
        self::assertEquals(
            (new DateTime())->modify('-1 day')->format('Y-m-d'),
            $this->message->getPreviousDate()->format('Y-m-d')
        );
    }
}
