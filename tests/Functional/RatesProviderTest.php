<?php

namespace App\tests\Functional;

use App\Dto\DateDto;
use App\Service\RatesProviderInterface;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RatesProviderTest extends KernelTestCase
{
    private RatesProviderInterface $provider;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $container = static::getContainer();
        $this->provider = $container->get(RatesProviderInterface::class);
    }

    public function testGetRatesOutOfRange(): void
    {
        $xml = $this->provider->getRates(DateDto::create(new DateTimeImmutable('-1000 day')));
        self::assertNull($xml);
    }

    /**
     * @depends testGetRatesOutOfRange
     */
    public function testGetRatesSuccessful(): void
    {
        $xml = $this->provider->getRates();
        self::assertNotEmpty($xml);
    }
}
