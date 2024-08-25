<?php

namespace App\Tests\Functional;

use App\Domain\Rates\Feature\RatesPreloadFeature;
use App\Domain\Rates\Message\RatesPreloadMessage;
use App\Domain\Storage\Service\TickerStorageInterface;
use App\Domain\Ticker\Dto\TickerDto;
use App\Domain\Ticker\Dto\TickerPayloadDto;
use App\Domain\Ticker\Feature\IndexBaseFeature;
use App\Domain\Ticker\Feature\IndexRurFeature;
use App\Domain\Ticker\Message\IndexBaseMessage;
use App\Domain\Ticker\Message\IndexRurMessage;
use App\Dto\DateDto;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IndexBaseFeatureTest extends KernelTestCase
{
    private const DATE = '-1 day';
    private const CODE = 'USD';
    private const BASE1 = 'AUD';
    private const BASE2 = 'AMD';

    private TickerStorageInterface $storage;
    private RatesPreloadFeature $preloadFeature;
    private IndexRurFeature $featureRur;
    private IndexBaseFeature $featureBase;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $container = static::getContainer();
        $this->preloadFeature = $container->get(RatesPreloadFeature::class);
        $this->featureRur = $container->get(IndexRurFeature::class);
        $this->featureBase = $container->get(IndexBaseFeature::class);
        $this->storage = $container->get(TickerStorageInterface::class);
        $this->storage->withDate(DateDto::create(new DateTime(self::DATE)));
    }

    public function testComputeTicker(): void
    {
        $this->assertTicker(self::BASE1);
        $this->assertTicker(self::BASE2);
    }

    private function assertTicker(string $baseCurrency): void
    {
        $payload = TickerPayloadDto::create(
            self::CODE,
            DateDto::create(new DateTime(self::DATE)),
            $baseCurrency
        );
        $payloadRur = TickerPayloadDto::create(
            self::CODE,
            DateDto::create(new DateTime(self::DATE))
        );

        $this->storage->removeTicker(
            TickerDto::create(self::CODE, '0', 1, $baseCurrency)
        );
        ($this->preloadFeature)(new RatesPreloadMessage($payload));
        ($this->featureRur)(new IndexRurMessage($payloadRur));
        ($this->featureBase)(new IndexBaseMessage($payload));
        $tickerCodeRur = $this->storage->getTicker(self::CODE, TickerDto::DEFAULT_CURRENCY);
        self::assertNotNull($tickerCodeRur);
        $tickerBaseRur = $this->storage->getTicker($baseCurrency, TickerDto::DEFAULT_CURRENCY);
        self::assertNotNull($tickerBaseRur);
        $ticker = $this->storage->getTicker(self::CODE, $baseCurrency);
        self::assertNotNull($ticker);
        self::assertEquals(self::CODE, $ticker->getCharCode());
        self::assertNotNull($ticker->getDelta());
        self::assertEquals(
            number_format(
                $tickerCodeRur->getValue() * $tickerBaseRur->getKrur(),
                6,
                '.',
                ''
            ),
            number_format(
                $ticker->getValue(),
                6,
                '.',
                ''
            )
        );

        //previous
        $previousDate = DateDto::create((new DateTime(self::DATE))->modify('-1 day'));
        $payload = TickerPayloadDto::create(
            self::CODE,
            $previousDate,
            $baseCurrency
        );
        $payloadRur = TickerPayloadDto::create(
            self::CODE,
            $previousDate
        );
        $this->storage->withDate($previousDate);
        $this->storage->removeTicker(TickerDto::create(self::CODE, '0', 1, $baseCurrency));
        ($this->preloadFeature)(new RatesPreloadMessage($payload));
        ($this->featureRur)(new IndexRurMessage($payloadRur));
        $tickerCodeRur = $this->storage->getTicker(self::CODE, TickerDto::DEFAULT_CURRENCY);
        self::assertNotNull($tickerCodeRur);
        $tickerBaseRur = $this->storage->getTicker($baseCurrency, TickerDto::DEFAULT_CURRENCY);
        self::assertNotNull($tickerBaseRur);
        ($this->featureBase)(new IndexBaseMessage($payload));
        $previousTicker = $this->storage->getTicker(self::CODE, $baseCurrency);
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
