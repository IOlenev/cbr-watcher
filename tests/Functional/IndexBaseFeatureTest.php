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
    private const DATE1 = '-1 day';
    private const DATE2 = '-2 day';
    private const CODE1 = 'USD';
    private const CODE2 = 'RUR';
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
        $this->storage->withDate(DateDto::create(new DateTime(self::DATE1)));
    }

    public function testComputeTicker(): void
    {
        $this->assertTicker(self::CODE1, self::DATE1, self::BASE1);
        $this->assertTicker(self::CODE1, self::DATE1, self::BASE2);
        $this->assertTicker(self::CODE2, self::DATE1, self::BASE1);
        $this->assertTicker(self::CODE2, self::DATE1, self::BASE2);
        $this->assertTicker(self::CODE2, self::DATE1, self::CODE1);

        $this->assertTicker(self::CODE1, self::DATE2, self::BASE1);
        $this->assertTicker(self::CODE1, self::DATE2, self::BASE2);
        $this->assertTicker(self::CODE2, self::DATE2, self::BASE1);
        $this->assertTicker(self::CODE2, self::DATE2, self::BASE2);
        $this->assertTicker(self::CODE2, self::DATE2, self::CODE1);
    }

    private function assertTicker(string $ticker, string $date, string $baseCurrency): void
    {
        $payload = TickerPayloadDto::create(
            $ticker,
            DateDto::create(new DateTime($date)),
            $baseCurrency
        );
        $payloadRur = TickerPayloadDto::create(
            $ticker,
            DateDto::create(new DateTime($date))
        );

        $this->storage->removeTicker(
            TickerDto::create($ticker, '0', 1, $baseCurrency)
        );
        ($this->preloadFeature)(new RatesPreloadMessage($payload));
        ($this->featureRur)(new IndexRurMessage($payloadRur));
        ($this->featureBase)(new IndexBaseMessage($payload));
        $tickerCodeRur = $this->storage->getTicker($ticker, TickerDto::DEFAULT_CURRENCY);
        self::assertNotNull($tickerCodeRur);
        $tickerBaseRur = $this->storage->getTicker($baseCurrency, TickerDto::DEFAULT_CURRENCY);
        self::assertNotNull($tickerBaseRur);
        $tickerResult = $this->storage->getTicker($ticker, $baseCurrency);
        self::assertNotNull($tickerResult);
        self::assertEquals($ticker, $tickerResult->getCharCode());
        self::assertNotNull($tickerResult->getDelta());
        self::assertEquals(
            number_format(
                $tickerCodeRur->getValue() * $tickerBaseRur->getKrur(),
                6,
                '.',
                ''
            ),
            number_format(
                $tickerResult->getValue(),
                6,
                '.',
                ''
            )
        );

        //previous
        $previousDate = DateDto::create((new DateTime($date))->modify('-1 day'));
        $payload = TickerPayloadDto::create(
            $ticker,
            $previousDate,
            $baseCurrency
        );
        $payloadRur = TickerPayloadDto::create(
            $ticker,
            $previousDate
        );
        $this->storage->withDate($previousDate);
        $this->storage->removeTicker(TickerDto::create($ticker, '0', 1, $baseCurrency));
        ($this->preloadFeature)(new RatesPreloadMessage($payload));
        ($this->featureRur)(new IndexRurMessage($payloadRur));
        $tickerCodeRur = $this->storage->getTicker($ticker, TickerDto::DEFAULT_CURRENCY);
        self::assertNotNull($tickerCodeRur);
        $tickerBaseRur = $this->storage->getTicker($baseCurrency, TickerDto::DEFAULT_CURRENCY);
        self::assertNotNull($tickerBaseRur);
        ($this->featureBase)(new IndexBaseMessage($payload));
        $previousTicker = $this->storage->getTicker($ticker, $baseCurrency);
        self::assertNotNull($previousTicker);
        $delta = $tickerResult->getValue() - $previousTicker->getValue();
        self::assertEquals(
            number_format(
                $delta,
                4
            ),
            number_format(
                $tickerResult->getDelta(),
                4
            )
        );
    }
}
