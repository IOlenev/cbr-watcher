<?php

namespace App\Tests\Functional;

use App\Dto\DateDto;
use App\Message\RatesPreloadMessage;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RatesPreloadMessageTest extends KernelTestCase
{
    private const BASE_DATE = '2024-08-02';
    private const PREVIOUS_DATE = '2024-08-01';

    public function testMessageDates(): void
    {
        $message = RatesPreloadMessage::create(
            DateDto::create(
                new DateTime(self::BASE_DATE)
            )
        );

        self::assertEquals(
            self::BASE_DATE,
            $message->getBaseDate()->format('Y-m-d')
        );
        self::assertEquals(
            self::PREVIOUS_DATE,
            $message->getPreviousDate()->format('Y-m-d')
        );
    }

    public function testMessageDefaultDates(): void
    {
        $message = RatesPreloadMessage::create();

        self::assertEquals(
            (new DateTime())->format('Y-m-d'),
            $message->getBaseDate()->format('Y-m-d')
        );
        self::assertEquals(
            (new DateTime())->modify('-1 day')->format('Y-m-d'),
            $message->getPreviousDate()->format('Y-m-d')
        );
    }
}
