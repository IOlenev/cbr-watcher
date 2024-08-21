<?php

namespace App\Tests\Functional;

use App\Domain\Rates\Feature\RatesPreloadFeature;
use App\Domain\Rates\Message\RatesPreloadMessage;
use App\Domain\Storage\Service\TickerStorageInterface;
use App\Domain\Ticker\Dto\TickerDto;
use App\Domain\Ticker\Dto\TickerPayloadDto;
use App\Domain\Ticker\Feature\IndexRurFeature;
use App\Domain\Ticker\Message\IndexRurMessage;
use App\Dto\DateDto;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IndexRurFeatureTest extends KernelTestCase
{
    private const DATE = '-1 day';
    private const CODE = 'USD';

    private TickerStorageInterface $storage;
    private RatesPreloadFeature $preloadFeature;
    private IndexRurFeature $feature;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $container = static::getContainer();
        $this->preloadFeature = $container->get(RatesPreloadFeature::class);
        $this->feature = $container->get(IndexRurFeature::class);
        $this->storage = $container->get(TickerStorageInterface::class);
        $this->storage->withDate(DateDto::create(new DateTime(self::DATE)));
    }

    public function testComputeTicker(): void
    {
        $payload = TickerPayloadDto::create(
            self::CODE,
            DateDto::create(new DateTime(self::DATE))
        );
        $this->storage->removeTicker(TickerDto::create(self::CODE));
        ($this->preloadFeature)(new RatesPreloadMessage($payload));
        ($this->feature)(new IndexRurMessage($payload));
        $ticker = $this->storage->getTicker(self::CODE, TickerDto::DEFAULT_CURRENCY);
        self::assertNotNull($ticker);
        self::assertEquals(self::CODE, $ticker->getCharCode());
        self::assertNotNull($ticker->getDelta());

        //previous
        $previousDate = DateDto::create((new DateTime(self::DATE))->modify('-1 day'));
        $payload = TickerPayloadDto::create(
            self::CODE,
            $previousDate
        );
        $this->storage->withDate($previousDate);
        $this->storage->removeTicker(TickerDto::create(self::CODE));
        ($this->preloadFeature)(new RatesPreloadMessage($payload));
        ($this->feature)(new IndexRurMessage($payload));
        $previousTicker = $this->storage->getTicker(self::CODE, TickerDto::DEFAULT_CURRENCY);
        self::assertNotNull($previousTicker);
        $delta = $ticker->getValue() - $previousTicker->getValue();
        self::assertEquals(
            number_format(
                $delta,
                4
            ),
            number_format(
                $ticker->getDelta(),
                4
            )
        );
    }
}
