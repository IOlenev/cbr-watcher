<?php

namespace App\Tests\Functional;

use App\Domain\Storage\Service\TickerStorageInterface;
use App\Domain\Ticker\Dto\TickerDto;
use App\Dto\DateDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Throwable;

class TickerStorageTest extends KernelTestCase
{
    private const CODE = 'BURATINO';
    private const BASE = 'PINOCKIO';

    private TickerStorageInterface $storage;
    private TickerDto $ticker;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $container = static::getContainer();
        $this->storage = $container->get(TickerStorageInterface::class);
        $this->storage->withDate(DateDto::create());
        $this->ticker = TickerDto::create(self::CODE, microtime(), 1, self::BASE);
    }

    public function testSaveSuccess(): void
    {
        $this->storage->putTicker($this->ticker);
        $ticker = $this->storage->getTicker($this->ticker->getCharCode(), $this->ticker->getBaseCurrency());
        self::assertEquals($this->ticker, $ticker);

        $newNominalTicker = TickerDto::create(self::CODE, microtime(), 10, self::BASE);
        self::assertNotEquals($this->ticker->getNominal(), $newNominalTicker->getNominal());
        $this->storage->putTicker($newNominalTicker);
        $ticker = $this->storage->getTicker($newNominalTicker->getCharCode(), $newNominalTicker->getBaseCurrency());
        self::assertEquals($newNominalTicker, $ticker);
        self::assertNotEquals($this->ticker->getNominal(), $ticker->getNominal());
    }

    public function testRemove(): void
    {
        $this->storage->removeTicker($this->ticker);
        $ticker = $this->storage->getTicker(self::CODE, self::BASE);
        self::assertNull($ticker);
    }

    /**
     * @depends testSaveSuccess
     */
    public function testSaveFail(): void
    {
        self::markTestSkipped();
        $this->expectException(Throwable::class);
        $this->storage->getTicker(self::CODE, self::BASE);
    }

    protected function tearDown(): void
    {
        $this->storage->removeTicker($this->ticker);
        parent::tearDown();
    }
}
