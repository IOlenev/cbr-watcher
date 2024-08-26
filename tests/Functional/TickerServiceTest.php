<?php

namespace App\Tests\Functional;

use App\Domain\Storage\Service\TickerServiceInterface;
use App\Domain\Ticker\Dto\TickerDto;
use App\Dto\DateDto;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TickerServiceTest extends KernelTestCase
{
    private const CODE = 'USD';
    private TickerServiceInterface $tickerService;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $container = static::getContainer();
        $this->tickerService = $container->get(TickerServiceInterface::class);
    }

    public function testGetTickerToRur(): void
    {
        $ticker = $this->tickerService
            ->withDate(DateDto::create(new DateTime('-5 day')))
            ->getTicker(self::CODE, TickerDto::DEFAULT_CURRENCY)
        ;
        self::assertNotNull($ticker);
    }

    public function testGetRurTicker(): void
    {
        $date = DateDto::create(new DateTime('-15 day'));
        $ticker = $this->tickerService
            ->withDate($date)
            ->getTicker(TickerDto::DEFAULT_CURRENCY, TickerDto::DEFAULT_CURRENCY)
        ;
        self::assertNotNull($ticker);
        self::assertEquals(TickerDto::DEFAULT_CURRENCY, $ticker->getCharCode());
        self::assertEquals($date, $ticker->getDate());
    }
}
